<?php
/**
 * Обёртка над \Bitrix\Main\Diag\FileLogger.
 *
 * Зачем: у каждого модуля свой логгер с тегом = MODULE_ID. Это упрощает
 * grep в error.log и подсчёт записей.
 *
 * Пример:
 *   \Mycompany\EmptyModule\Helpers\Logger::info('Something happened', ['key' => 'value']);
 *   \Mycompany\EmptyModule\Helpers\Logger::error('Boom!', $context);
 *
 * Поведение:
 *  - Логгеры ленивые: создаются при первом вызове.
 *  - Уровень логирования читается из b_option('mycompany.emptymodule', 'log_level').
 *  - Если в b_option стоит enable_log=N — все вызовы превращаются в no-op
 *    (кроме error — он всегда пишется).
 *  - Файл лога: /bitrix/modules/mycompany.emptymodule/logs/module.log.
 *
 * Совместимость: использует FileLogger (новое API), а не устаревший
 * Diag\Logger::create() (которого нет в свежем Bitrix).
 */

namespace Mycompany\EmptyModule\Helpers;

use Bitrix\Main\Diag\FileLogger;
use Psr\Log\LogLevel;

final class Logger
{
    private const MODULE_ID = 'mycompany.emptymodule';

    private const LEVELS = [
        LogLevel::DEBUG     => 10,
        LogLevel::INFO      => 20,
        LogLevel::WARNING   => 30,
        LogLevel::ERROR     => 40,
        LogLevel::CRITICAL  => 50,
    ];

    private static ?Logger $instance = null;
    private ?FileLogger $logger = null;
    private bool $enabled;
    private string $level;

    private function __construct()
    {
        $this->enabled = \COption::GetOptionString(self::MODULE_ID, 'enable_log', 'N') === 'Y';
        $this->level   = strtoupper(\COption::GetOptionString(self::MODULE_ID, 'log_level', 'INFO'));

        // Создаём FileLogger только если включено логирование.
        if ($this->enabled) {
            $this->logger = self::createLogger();
        }
    }

    private static function createLogger(): FileLogger
    {
        $logFile = self::getLogFilePath();

        // Создаём каталог, если нужно.
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return new FileLogger($logFile, 5 * 1024 * 1024); // 5MB max
    }

    /**
     * Путь к файлу лога модуля. Создаётся внутри /bitrix/modules/<MODULE_ID>/logs/
     * — это стандартное место, оно обычно уже в gitignore, и Bitrix знает, что
     * туда писать. Если /bitrix/modules/ недоступен для записи, fallback в /upload/.
     */
    public static function getLogFilePath(): string
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname($_SERVER['SCRIPT_FILENAME'], 4);

        $preferred = $documentRoot . '/bitrix/modules/' . self::MODULE_ID . '/logs/module.log';

        if (self::isWritable($preferred) || (!file_exists($preferred) && self::isWritable(dirname($preferred)))) {
            return $preferred;
        }

        // Fallback: /upload/ — всегда writable.
        return $documentRoot . '/upload/' . self::MODULE_ID . '/module.log';
    }

    private static function isWritable(string $path): bool
    {
        if (file_exists($path)) {
            return is_writable($path);
        }
        return is_writable(dirname($path));
    }

    private static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Сбросить кеш инстанса (нужно в тестах и при смене опций).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    public static function debug(string $message, array $context = []): void
    {
        self::write(LogLevel::DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write(LogLevel::INFO, $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write(LogLevel::WARNING, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        // error пишется ВСЕГДА, даже если enable_log=N — это требование безопасности.
        $self = self::getInstance();
        if ($self->logger === null) {
            $self->logger = self::createLogger();
        }
        $self->logger->error($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        $self = self::getInstance();
        if ($self->logger === null) {
            $self->logger = self::createLogger();
        }
        $self->logger->critical($message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $self = self::getInstance();

        if (!$self->enabled || $self->logger === null) {
            return;
        }

        if (!self::levelEnabled($self->level, $level)) {
            return;
        }

        $self->logger->{$level}($message, $context);
    }

    private static function levelEnabled(string $configured, string $needed): bool
    {
        $configuredNum = self::LEVELS[$configured] ?? self::LEVELS[LogLevel::INFO];
        $neededNum     = self::LEVELS[$needed] ?? self::LEVELS[LogLevel::INFO];
        return $neededNum >= $configuredNum;
    }
}
