<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents;

use Unzer\Core\Infrastructure\Configuration\ConfigurationManager;
use Unzer\Core\Infrastructure\Singleton;

/**
 * Class TestConfigurationManager.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents
 */
class TestConfigurationManager extends ConfigurationManager
{
    protected string $context = 'test';

    /**
     * Singleton instance of this class.
     *
     * @var ?Singleton
     */
    protected static ?Singleton $instance = null;

    public function __construct()
    {
        parent::__construct();

        static::$instance = $this;
    }
}
