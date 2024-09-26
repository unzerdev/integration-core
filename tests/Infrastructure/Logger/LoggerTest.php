<?php

namespace Unzer\Core\Tests\Infrastructure\Logger;

use Exception;
use Unzer\Core\Infrastructure\Http\HttpClient;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\Logger\LoggerConfiguration;
use Unzer\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class LoggerTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\Logger
 */
class LoggerTest extends BaseInfrastructureTestWithServices
{

    /**
     * @return void
     *
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->shopConfig->setIntegrationName('Shop1');
        $this->shopConfig->setDefaultLoggerEnabled(true);
    }

    /**
     * Test if error log level is passed to shop logger
     */
    public function testErrorLogLevelIsPassed()
    {
        Logger::logError('Some data');
        $this->assertEquals(0, $this->shopLogger->data->getLogLevel(), 'Log level for error call must be 0.');
    }

    /**
     * Test if warning log level is passed to shop logger
     */
    public function testWarningLogLevelIsPassed()
    {
        Logger::logWarning('Some data');
        $this->assertEquals(1, $this->shopLogger->data->getLogLevel(), 'Log level for warning call must be 1.');
    }

    /**
     * Test if info log level is passed to shop logger
     */
    public function testInfoLogLevelIsPassed()
    {
        Logger::logInfo('Some data');
        $this->assertEquals(2, $this->shopLogger->data->getLogLevel(), 'Log level for info call must be 2.');
    }

    /**
     * Test if debug log level is passed to shop logger
     */
    public function testDebugLogLevelIsPassed()
    {
        Logger::logDebug('Some data');
        $this->assertEquals(3, $this->shopLogger->data->getLogLevel(), 'Log level for debug call must be 3.');
    }

    /**
     * Test if log data is sent to shop logger
     */
    public function testLogMessageIsSent()
    {
        Logger::logInfo('Some data');
        $this->assertEquals('Some data', $this->shopLogger->data->getMessage(), 'Log message must be sent.');
    }

    /**
     * Test if log data is sent to shop logger
     */
    public function testLogComponentIsSent()
    {
        Logger::logInfo('Some data');
        $this->assertEquals('Core', $this->shopLogger->data->getComponent(), 'Log component must be sent');
    }

    /**
     * Test if log data is sent to shop logger
     */
    public function testLogIntegrationIsSent()
    {
        Logger::logInfo('Some data');
        $this->assertEquals('Shop1', $this->shopLogger->data->getIntegration(), 'Log integration must be sent');
    }

    /**
     * Test if message logged to default logger will have timestamp set in milliseconds
     */
    public function testLoggingToDefaultLoggerTimestamp()
    {
        $timeSeconds = time();
        Logger::logInfo('Some data');
        $this->assertGreaterThanOrEqual($timeSeconds * 1000, $this->defaultLogger->data->getTimestamp());
    }
}
