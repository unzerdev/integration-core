<?php

namespace Unzer\Core\BusinessLogic\DataAccess\Connection\Entities;

use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\Mode;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;
use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings as DomainConnectionSettings;

/**
 * Class ConnectionSettings.
 *
 * @package Unzer\Core\BusinessLogic\DataAccess\Connection\Entities
 */
class ConnectionSettings extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var DomainConnectionSettings
     */
    protected DomainConnectionSettings $connectionSettings;

    /**
     * @var string
     */
    protected string $storeId;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'ConnectionSettings');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidModeException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];

        $connectionSettings = $data['connectionSettings'] ?? [];
        $this->connectionSettings = new DomainConnectionSettings(
            Mode::parse($connectionSettings['mode']),
            !empty($connectionSettings['liveData']) ?
                new ConnectionData(
                    $connectionSettings['liveData']['publicKey'] ?? null,
                    $connectionSettings['liveData']['privateKey'] ?? null,
                ) : null,
            !empty($connectionSettings['sandboxData']) ?
                new ConnectionData(
                    $connectionSettings['sandboxData']['publicKey'] ?? null,
                    $connectionSettings['sandboxData']['privateKey'] ?? null
                ) : null
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['connectionSettings'] = [
            'mode' => $this->connectionSettings->getMode() ? $this->connectionSettings->getMode()->getMode() : 'live',
            'liveData' => $this->connectionSettings->getLiveConnectionData() ? [
                'publicKey' => $this->connectionSettings->getLiveConnectionData()->getPublicKey(),
                'privateKey' => $this->connectionSettings->getLiveConnectionData()->getPrivateKey()
            ] : [],
            'sandboxData' => $this->connectionSettings->getSandboxConnectionData() ? [
                'publicKey' => $this->connectionSettings->getSandboxConnectionData()->getPublicKey(),
                'privateKey' => $this->connectionSettings->getSandboxConnectionData()->getPrivateKey()
            ] : [],
        ];

        return $data;
    }

    /**
     * @return DomainConnectionSettings
     */
    public function getConnectionSettings(): DomainConnectionSettings
    {
        return $this->connectionSettings;
    }

    /**
     * @param DomainConnectionSettings $connectionSettings
     *
     * @return void
     */
    public function setConnectionSettings(DomainConnectionSettings $connectionSettings): void
    {
        $this->connectionSettings = $connectionSettings;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }
}
