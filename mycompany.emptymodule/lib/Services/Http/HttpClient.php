<?php
/**
 * Современный HTTP-клиент модуля.
 *
 * Замена lib/Tools/WebService/CurlClient.php (DEPRECATED).
 * Делегирует работу \Bitrix\Main\HttpClient, чтобы не возиться с curl
 * вручную и не ловить TLS/HTTP/2 проблем на разных окружениях.
 *
 * Пример:
 *   $client = (new HttpClient())
 *       ->setBaseUrl('https://api.example.com/v1')
 *       ->setBearer('TOKEN')
 *       ->setTimeout(15);
 *
 *   $response = $client->get('/users/42');
 *   if ($response->isOk()) {
 *       $data = $response->json();
 *   }
 *
 * Поддерживает retry, JSON-тело, query-параметры, заголовки, таймауты.
 */

namespace Mycompany\EmptyModule\Services\Http;

use Bitrix\Main\HttpClient as BitrixHttpClient;
use InvalidArgumentException;

class HttpClient
{
    /** @var string */
    private string $baseUrl = '';

    /** @var array<string, string> */
    private array $headers = [
        'Accept'     => 'application/json',
        'User-Agent' => 'Mycompany-EmptyModule/1.0',
    ];

    /** @var string|null */
    private ?string $bearer = null;

    /** @var int seconds */
    private int $timeout = 30;

    /** @var int */
    private int $connectTimeout = 10;

    /** @var int */
    private int $retries = 0;

    /** @var int ms */
    private int $retryDelayMs = 200;

    /** @var bool */
    private bool $followRedirects = true;

    /** @var bool */
    private bool $verifySsl = true;

    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBearer(?string $token): self
    {
        $this->bearer = $token;
        if ($token !== null && $token !== '') {
            $this->headers['Authorization'] = 'Bearer ' . $token;
        } else {
            unset($this->headers['Authorization']);
        }
        return $this;
    }

    public function setTimeout(int $seconds): self
    {
        if ($seconds < 1) {
            throw new InvalidArgumentException('Timeout must be positive');
        }
        $this->timeout = $seconds;
        return $this;
    }

    public function setConnectTimeout(int $seconds): self
    {
        $this->connectTimeout = $seconds;
        return $this;
    }

    public function setRetries(int $count, int $delayMs = 200): self
    {
        $this->retries = max(0, $count);
        $this->retryDelayMs = max(0, $delayMs);
        return $this;
    }

    public function setFollowRedirects(bool $value): self
    {
        $this->followRedirects = $value;
        return $this;
    }

    public function setVerifySsl(bool $value): self
    {
        $this->verifySsl = $value;
        return $this;
    }

    public function get(string $path, array $query = []): Response
    {
        return $this->request('GET', $path, $query, null);
    }

    public function post(string $path, $body = null, array $query = []): Response
    {
        return $this->request('POST', $path, $query, $body);
    }

    public function put(string $path, $body = null, array $query = []): Response
    {
        return $this->request('PUT', $path, $query, $body);
    }

    public function patch(string $path, $body = null, array $query = []): Response
    {
        return $this->request('PATCH', $path, $query, $body);
    }

    public function delete(string $path, array $query = []): Response
    {
        return $this->request('DELETE', $path, $query, null);
    }

    /**
     * @param mixed $body — array (JSON), string (raw) или null
     */
    private function request(string $method, string $path, array $query, $body): Response
    {
        $url = $this->buildUrl($path, $query);
        $attempt = 0;
        $lastResponse = null;

        while ($attempt <= $this->retries) {
            $http = $this->buildHttpClient();

            if ($body !== null) {
                $this->setBody($http, $body);
            }

            $result = $http->query($method, $url);

            $response = new Response(
                (int) $http->getStatus(),
                (string) $http->getResult(),
                (array)  $http->getHeaders(),
                $result,
                $http->getError()
            );

            $lastResponse = $response;

            // Повторяем на 5xx и сетевых ошибках.
            if (!$response->shouldRetry($this->retries)) {
                return $response;
            }
            if ($attempt === $this->retries) {
                return $response;
            }
            $attempt++;
            if ($this->retryDelayMs > 0) {
                usleep($this->retryDelayMs * 1000);
            }
        }

        return $lastResponse ?? new Response(0, '', [], false, 'unknown error');
    }

    private function buildHttpClient(): BitrixHttpClient
    {
        $http = new BitrixHttpClient();
        $http->setTimeout($this->timeout);
        $http->setStreamTimeout($this->connectTimeout);
        $http->setFollowRedirect($this->followRedirects);
        $http->setSslVerify($this->verifySsl);

        foreach ($this->headers as $name => $value) {
            $http->setHeader($name, $value);
        }

        return $http;
    }

    /**
     * @param mixed $body
     */
    private function setBody(BitrixHttpClient $http, $body): void
    {
        if (is_array($body)) {
            $http->setHeader('Content-Type', 'application/json');
            $http->setBody(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } elseif (is_string($body)) {
            $http->setBody($body);
        }
    }

    private function buildUrl(string $path, array $query): string
    {
        $path = '/' . ltrim($path, '/');
        $url  = $this->baseUrl . $path;

        if (!empty($query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        return $url;
    }
}
