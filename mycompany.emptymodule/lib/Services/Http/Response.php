<?php
/**
 * DTO ответа HTTP-клиента.
 *
 * Не зависит от Bitrix\Main\HttpClient, чтобы тестировать и
 * формировать вручную в юнит-тестах.
 */

namespace Mycompany\EmptyModule\Services\Http;

use Bitrix\Main\Web\Json;

class Response
{
    public const TRANSIENT_STATUSES = [408, 425, 429, 500, 502, 503, 504, 0];

    /** @var int */
    private int $status;

    /** @var string */
    private string $body;

    /** @var array<string, string|string[]> */
    private array $headers;

    /** @var bool */
    private bool $success;

    /** @var string|null */
    private ?string $error;

    /**
     * @param array<string, string|string[]> $headers
     */
    public function __construct(
        int $status,
        string $body,
        array $headers = [],
        bool $success = true,
        ?string $error = null
    ) {
        $this->status  = $status;
        $this->body    = $body;
        $this->headers = $headers;
        $this->success = $success;
        $this->error   = $error;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name, ?string $default = null): ?string
    {
        $name = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $name) {
                if (is_array($value)) {
                    return implode(', ', $value);
                }
                return (string) $value;
            }
        }
        return $default;
    }

    public function isOk(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function isClientError(): bool
    {
        return $this->status >= 400 && $this->status < 500;
    }

    public function isServerError(): bool
    {
        return $this->status >= 500;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return !$this->success || $this->error !== null;
    }

    /**
     * Распарсить тело как JSON. Возвращает null при невалидном JSON.
     *
     * @return mixed
     */
    public function json()
    {
        if ($this->body === '') {
            return null;
        }
        try {
            return Json::decode($this->body);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Нужно ли повторять запрос (5xx, 408, 429, 0).
     */
    public function shouldRetry(int $maxRetries = 0): bool
    {
        if ($maxRetries <= 0) {
            return false;
        }
        if (in_array($this->status, self::TRANSIENT_STATUSES, true)) {
            return true;
        }
        return !$this->success; // сетевая ошибка без статус-кода
    }
}
