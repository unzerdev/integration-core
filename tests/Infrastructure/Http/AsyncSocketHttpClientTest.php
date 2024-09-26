<?php

namespace Unzer\Core\Tests\Infrastructure\Http;

use Exception;
use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestAsyncSocketHttpClient;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class AsyncSocketHttpClientTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\Http
 */
class AsyncSocketHttpClientTest extends BaseInfrastructureTestWithServices
{
    /**
     * @var TestAsyncSocketHttpClient
     */
    public TestAsyncSocketHttpClient $client;

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new TestAsyncSocketHttpClient();
    }

    /**
     * @return void
     */
    public function testTransferProtocolHttps()
    {
        // arrange
        $url = 'https://google.com';

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals('tls://', $this->client->requestHistory[0]['transferProtocol']);
    }

    /**
     * @return void
     */
    public function testTransferProtocolHttp()
    {
        // arrange
        $url = 'http://google.com';

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals('tcp://', $this->client->requestHistory[0]['transferProtocol']);
    }

    /**
     * @return void
     */
    public function testHost()
    {
        // arrange
        $url = 'http://user:password@google.com/some/path?query=test#fragment';

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals('google.com', $this->client->requestHistory[0]['host']);
    }

    /**
     * @return void
     */
    public function testDefaultHttpsPort()
    {
        // arrange
        $url = 'https://user:password@google.com/some/path?query=test#fragment';

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals(443, $this->client->requestHistory[0]['port']);
    }

    /**
     * @return void
     */
    public function testDefaultHttpPort()
    {
        // arrange
        $url = 'http://user:password@google.com/some/path?query=test#fragment';

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals(80, $this->client->requestHistory[0]['port']);
    }

    /**
     * @return void
     */
    public function testCustomHttpPort()
    {
        // arrange
        $url = 'http://user:password@google.com:1234/some/path?query=test#fragment';

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals(1234, $this->client->requestHistory[0]['port']);
    }

    /**
     * @return void
     */
    public function testDefaultTimeout()
    {
        // arrange
        $url = 'http://user:password@google.com/some/path?query=test#fragment';

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals(5, $this->client->requestHistory[0]['timeout']);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function testCustomTimeout()
    {
        // arrange
        $url = 'http://user:password@google.com/some/path?query=test#fragment';
        /** @var Configuration $configService */
        $configService = TestServiceRegister::getService(Configuration::CLASS_NAME);
        $configService->setAsyncRequestTimeout(10);

        // act
        $this->client->requestAsync('GET', $url);

        // assert
        $this->assertEquals(10, $this->client->requestHistory[0]['timeout']);
    }
}
