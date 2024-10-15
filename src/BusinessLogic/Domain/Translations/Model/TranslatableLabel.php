<?php

namespace Unzer\Core\BusinessLogic\Domain\Translations\Model;

use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;

/**
 * Class TranslatableLabel
 *
 * @package Unzer\Core\BusinessLogic\Domain\Translations\Model
 */
class TranslatableLabel
{
    /**
     * @var string
     */
    protected string $message;

    /**
     * @var string
     */
    protected string $code;

    /**
     * @var string[]
     */
    protected array $params;

    /**
     * @param string $message
     * @param string $code
     * @param string[] $params
     */
    public function __construct(string $message, string $code, array $params = [])
    {
        $this->message = $message;
        $this->code = $code;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $input
     *
     * @return self[]
     *
     * @throws InvalidTranslatableArrayException
     */
    public static function fromArrayToBatch(array $input): array
    {
        self::validateTranslatableArray($input);

        return array_map(fn($value) => new self($value['value'], $value['locale']), $input);
    }

    /**
     * @param array $input
     *
     * @return void
     *
     * @throws InvalidTranslatableArrayException
     */
    private static function validateTranslatableArray(array $input): void
    {
        foreach ($input as $element) {
            if (!is_array($element) || !isset($element['locale']) || !isset($element['value'])) {
                throw new InvalidTranslatableArrayException(
                    new TranslatableLabel('Translatable array is invalid',
                        'translatableLabel.invalidArray')
                );
            }
        }
    }
}
