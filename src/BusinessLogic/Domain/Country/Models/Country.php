<?php

namespace Unzer\Core\BusinessLogic\Domain\Country\Models;

use Unzer\Core\BusinessLogic\Domain\Country\Exceptions\InvalidCountryArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class Country.
 *
 * @package Unzer\Core\BusinessLogic\Domain\Country\Models
 */
class Country
{
    /**
     * @var string
     */
    private string $code;

    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $code
     * @param string $name
     */
    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array $input
     *
     * @return self[]
     *
     * @throws InvalidCountryArrayException
     */
    public static function fromArrayToBatch(array $input): array
    {
        self::validateTranslatableArray($input);

        return array_map(fn($value) => new self($value['code'], $value['name']), $input);
    }

    /**
     * @param self[] $batch
     *
     * @return array
     */
    public static function fromBatchToArray(array $batch): array
    {
        return array_map(fn($country) => ['code' => $country->getCode(), 'name' => $country->getName()], $batch);
    }

    /**
     * @param array $input
     *
     * @return void
     *
     * @throws InvalidCountryArrayException
     */
    private static function validateTranslatableArray(array $input): void
    {
        foreach ($input as $element) {
            if (!is_array($element) || !isset($element['code']) || !isset($element['name'])) {
                throw new InvalidCountryArrayException(
                    new TranslatableLabel('Country array is invalid',
                        'country.invalidArray')
                );
            }
        }
    }
}
