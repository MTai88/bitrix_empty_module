<?php
/**
 * Bootstrap для PHPUnit: поднимает ядро Битрикса в окружении тестов.
 *
 * Запускается ТОЛЬКО из CLI (внутри docker compose exec php).
 * Подключает prolog как cron-скрипт, инициализирует модуль.
 *
 * Переменные окружения:
 *   BITRIX_TEST_DOCUMENT_ROOT  — путь к /var/www/html (по умолчанию /var/www/html)
 *   BITRIX_TEST_MODULE_ID      — модуль, который тестируем (default: mycompany.emptymodule)
 *
 * Запуск:
 *   docker compose exec php vendor/bin/phpunit
 */

declare(strict_types=1);

$documentRoot = getenv('BITRIX_TEST_DOCUMENT_ROOT') ?: '/var/www/html';
$moduleId     = getenv('BITRIX_TEST_MODULE_ID')     ?: 'mycompany.emptymodule';

if (!defined('NO_KEEP_STATISTIC')) {
    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);
    define('BX_CRONTAB', true);
}

$_SERVER['DOCUMENT_ROOT'] = $documentRoot;
$_SERVER['HTTP_HOST']     = 'bitrix.local';
$_SERVER['REQUEST_URI']   = '/';
$_SERVER['SCRIPT_NAME']   = '/phpunit';
$_SERVER['SERVER_NAME']   = 'bitrix.local';
$_SERVER['REMOTE_ADDR']   = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
$_SERVER['REQUEST_METHOD']  = 'GET';

require $documentRoot . '/bitrix/modules/main/bx_root.php';
require $documentRoot . '/bitrix/modules/main/include/prolog_before.php';

if (!\Bitrix\Main\Loader::includeModule($moduleId)) {
    fwrite(STDERR, "Cannot load module {$moduleId}\n");
    exit(1);
}
