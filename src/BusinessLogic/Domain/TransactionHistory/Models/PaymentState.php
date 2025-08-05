<?php

namespace Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models;

/**
 * Class PaymentState.
 *
 * @package Unzer\Core\BusinessLogic\Domain\TransactionHistory\Models
 */
class PaymentState
{
    /**
     * @var string $id
     */
    private string $id;

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['name']);
    }
}
