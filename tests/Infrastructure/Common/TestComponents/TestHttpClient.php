<?php
/** @noinspection PhpUnused */

/** @noinspection PhpMissingDocCommentInspection */

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents;

use Unzer\Core\Infrastructure\Http\DTO\Options;
use Unzer\Core\Infrastructure\Http\Exceptions\HttpCommunicationException;
use Unzer\Core\Infrastructure\Http\HttpClient;
use Unzer\Core\Infrastructure\Http\HttpResponse;

/**
 * Class TestHttpClient.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents
 */
class TestHttpClient extends HttpClient
{
    /** @var int  */
    public const REQUEST_TYPE_SYNCHRONOUS = 1;

    /** @var int  */
    public const REQUEST_TYPE_ASYNCHRONOUS = 2;

    /** @var bool  */
    public bool $calledAsync = false;
    public $additionalOptions;
    public array $setAdditionalOptionsCallHistory = [];

    /**
     * @var array
     */
    private array $responses;

    /**
     * @var array
     */
    private array $history;

    /**
     * @var array
     */
    private array $autoConfigurationCombinations = [];

    /**
     * @inheritdoc
     */
    public function request(string $method, string $url, ?array $headers = [], string $body = ''): HttpResponse
    {
        return $this->sendHttpRequest($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function requestAsync(string $method, string $url, ?array $headers = [], string $body = ''): void
    {
        $this->sendHttpRequestAsync($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function sendHttpRequest(
        string $method,
        string $url,
        ?array $headers = [],
        string $body = ''
    ): HttpResponse
    {
        $this->history[] = [
            'type' => self::REQUEST_TYPE_SYNCHRONOUS,
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body
        ];

        if (empty($this->responses)) {
            throw new HttpCommunicationException('No response');
        }

        return array_shift($this->responses);
    }

    /**
     * @inheritdoc
     */
    public function sendHttpRequestAsync(
        string $method,
        string $url,
        ?array $headers = [],
        string $body = ''
    ): void
    {
        $this->calledAsync = true;

        $this->history[] = [
            'type' => self::REQUEST_TYPE_ASYNCHRONOUS,
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getAutoConfigurationOptionsCombinations(string $method, string $url): array
    {
        if (empty($this->autoConfigurationCombinations)) {
            $this->setAdditionalOptionsCombinations(
                [
                    [new Options(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4)],
                    [new Options(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6)],
                ]
            );
        }

        return $this->autoConfigurationCombinations;
    }

    /**
     * Sets the additional HTTP options combinations.
     *
     * @param array $combinations
     *
     * @return void
     */
    protected function setAdditionalOptionsCombinations(array $combinations): void
    {
        $this->autoConfigurationCombinations = $combinations;
    }

    /**
     * Save additional options for request.
     *
     * @param string|null $domain A domain for which to reset configuration options.
     * @param Options[] $options Additional option to add to HTTP request.
     *
     * @return void
     */
    protected function setAdditionalOptions(?string $domain, array $options): void
    {
        $this->setAdditionalOptionsCallHistory[] = $options;
        $this->additionalOptions = $options;
    }

    /**
     * Reset additional options for request to default value.
     *
     * @param string|null $domain A domain for which to reset configuration options.
     *
     * @return void
     */
    protected function resetAdditionalOptions(?string $domain): void
    {
        $this->additionalOptions = [];
    }

    /**
     * Set all mock responses.
     *
     * @param array $responses
     *
     * @return void
     */
    public function setMockResponses(array $responses): void
    {
        $this->responses = $responses;
    }

    /**
     * Return last request.
     *
     * @return array
     */
    public function getLastRequest(): array
    {
        return end($this->history);
    }

    /**
     * Return call history.
     *
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Resets the history call stack.
     */
    public function resetHistory(): void
    {
        $this->history = null;
    }

    /**
     * Return last request.
     *
     * @return array
     */
    public function getLastRequestHeaders(): array
    {
        $lastRequest = $this->getLastRequest();

        return $lastRequest['headers'];
    }
}
