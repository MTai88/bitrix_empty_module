<?php
/**
 * Обёртка над \Bitrix\Main\Config\Configuration с fallback на env.
 *
 * Идея: редактируемые настройки модуля (которые меняются из админки) живут
 * в \COption::GetOptionString. А "статичные" параметры (адреса, секреты из CI,
 * режим dev/prod) — в .env или в /bitrix/.settings.php модуля.
 *
 * Этот класс предоставляет единый API:
 *   Config::get('myproject.api_url')        — из .settings.php
 *   Config::get('myproject.api_url', 'default')  — fallback
 *   Config::env('SENTRY_DSN')               — из getenv() / $_ENV
 *   Config::moduleOption('api_key')         — из b_option текущего модуля
 *
 * В .settings.php модуля структура:
 *   'myproject' => [
 *       'api_url' => 'https://api.example.com',
 *       'timeout' => 30,
 *   ],
 */

namespace Mycompany\EmptyModule\Helpers;

use Bitrix\Main\Config\Configuration;

final class Config
{
    private const MODULE_ID = 'mycompany.emptymodule';

    /**
     * Чтение из /bitrix/.settings.php по dot-нотации.
     *
     * @template T
     * @param string $key  — например, 'myproject.api_url'
     * @param T $default
     * @return mixed|T
     */
    public static function get(string $key, $default = null)
    {
        return Configuration::getValue($key) ?? $default;
    }

    /**
     * Чтение из env-переменной.
     *
     * @template T
     * @param string $name
     * @param T $default
     * @return mixed|T
     */
    public static function env(string $name, $default = null)
    {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }

        if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
            return $_ENV[$name];
        }

        return $default;
    }

    /**
     * Опция модуля из b_option (для редактируемых из админки значений).
     */
    public static function moduleOption(string $name, string $default = ''): string
    {
        return (string) \COption::GetOptionString(self::MODULE_ID, $name, $default);
    }

    /**
     * Удобство: "mode" — 'prod' / 'stage' / 'dev'.
     */
    public static function mode(): string
    {
        return self::moduleOption('mode', 'prod');
    }

    public static function isDev(): bool
    {
        return self::mode() === 'dev';
    }

    public static function isProd(): bool
    {
        return self::mode() === 'prod';
    }
}
