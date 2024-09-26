<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents;

use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Configuration\ConfigurationManager;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Unzer\Core\Infrastructure\Singleton;

/**
 * Class TestShopConfiguration.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents
 */
class TestShopConfiguration extends Configuration
{
    /** @var string */
    private string $callbackUrl = 'https://some-shop.test/callback?a=1&b=abc';

    /** @var ?string */
    private ?string $autoConfigureUrl = 'https://some-shop.test/configure';

    /**
     * Singleton instance of this class.
     *
     * @var ?Singleton
     */
    protected static ?Singleton $instance;

    public function __construct()
    {
        parent::__construct();

        static::$instance = $this;
    }

    /**
     * Retrieves integration name.
     *
     * @return string Integration name.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getIntegrationName(): string
    {
        return $this->getConfigValue('integrationName', 'test-system');
    }

    /**
     * Sets integration name.
     *
     * @param string $name Integration name.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setIntegrationName(string $name)
    {
        $this->saveConfigValue('integrationName', $name);
    }

    /**
     * Determines whether the configuration entry is system specific.
     *
     * @param string $name Configuration entry name.
     *
     * @return bool
     */
    public function isContextSpecific(string $name): bool
    {
        return $name !== 'maxStartedTasksLimit';
    }

    /**
     * @return ConfigurationManager
     */
    public function getConfigurationManager(): ConfigurationManager
    {
        if ($this->configurationManager === null) {
            $this->configurationManager = TestServiceRegister::getService(ConfigurationManager::CLASS_NAME);
        }

        return $this->configurationManager;
    }

    /**
     * Sets auto-configuration controller URL.
     *
     * @param ?string $url Auto-configuration URL.
     */
    public function setAutoConfigurationUrl(?string $url)
    {
        $this->autoConfigureUrl = $url;
    }

    /**
     * @inheritDoc
     */
    public function getAutoConfigurationUrl(): ?string
    {
        return $this->autoConfigureUrl;
    }

    /**
     * Returns async process starter url, always in http.
     *
     * @param string $guid Process identifier.
     *
     * @return string Formatted URL of async process starter endpoint.
     */
    public function getAsyncProcessUrl(string $guid): string
    {
        return str_replace('https://', 'http://', $this->callbackUrl . '&guid=' . $guid);
    }
}
