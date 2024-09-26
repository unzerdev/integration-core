<?php

namespace Unzer\Core\Tests\Infrastructure\Http;

use Unzer\Core\Infrastructure\Http\DTO\Options;
use Unzer\Core\Infrastructure\Http\HttpClient;
use Unzer\Core\Infrastructure\Http\HttpResponse;
use Unzer\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Unzer\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class HttpClientTest.
 *
 * @package Unzer\Core\Tests\Infrastructure\Http
 */
class HttpClientTest extends TestCase
{
    /**
     * @var TestHttpClient
     */
    protected TestHttpClient $httpClient;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->httpClient = new TestHttpClient();
        $proxyInstance = $this;
        new TestServiceRegister(
            [
                HttpClient::CLASS_NAME => function () use ($proxyInstance) {
                    return $proxyInstance->httpClient;
                },
            ]
        );
    }

    /**
     * Test auto-configure to be successful with default options
     */
    public function testAutoConfigureSuccessfullyWithDefaultOptions()
    {
        $response = new HttpResponse(200, [], '{}');
        $this->httpClient->setMockResponses([$response]);

        $success = $this->httpClient->autoConfigure('POST', 'test.url.com');

        $this->assertTrue($success, 'Auto-configure must be successful if default configuration request passed.');
        $this->assertCount(
            0,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should not be called'
        );
        $this->assertEmpty($this->httpClient->additionalOptions, 'Additional options should remain empty');
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureSuccessfullyWithSomeCombination()
    {
        $responses = [
            new HttpResponse(400, [], '{}'),
            new HttpResponse(200, [], '{}'),
        ];
        $this->httpClient->setMockResponses($responses);
        $additionalOptionsCombination = [new Options(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4)];

        $success = $this->httpClient->autoConfigure('POST', 'test.url.com');

        $this->assertTrue($success, 'Auto-configure must be successful if request passed with some combination.');
        $this->assertCount(
            1,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should be called once'
        );
        $this->assertEquals(
            $additionalOptionsCombination,
            $this->httpClient->additionalOptions,
            'Additional options should be set to first combination'
        );
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureFailed()
    {
        $responses = [
            new HttpResponse(400, [], '{}'),
            new HttpResponse(400, [], '{}'),
            new HttpResponse(400, [], '{}'),
        ];
        $this->httpClient->setMockResponses($responses);

        $success = $this->httpClient->autoConfigure('POST', 'test.url.com');

        $this->assertFalse($success, 'Auto-configure must failed if no combination resulted with request passed.');
        $this->assertCount(
            2,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should be called twice'
        );
        $this->assertEmpty(
            $this->httpClient->additionalOptions,
            'Reset additional options method should be called and additional options should be empty.'
        );
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureFailedWhenThereAreNoResponses()
    {
        $success = $this->httpClient->autoConfigure('POST', 'test.url.com');

        $this->assertFalse($success, 'Auto-configure must failed if no combination resulted with request passed.');
        $this->assertCount(
            2,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should be called twice'
        );
        $this->assertEmpty(
            $this->httpClient->additionalOptions,
            'Reset additional options method should be called and additional options should be empty.'
        );
    }
}
