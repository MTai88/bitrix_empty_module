<?php
/**
 * Обработчик main:OnEpilog (пример).
 *
 * Срабатывает после формирования HTML, до отправки клиенту.
 * Используется для:
 *   - подмены/добавления мета-тегов;
 *   - добавления JS/CSS;
 *   - модификации $APPLICATION->GetPageProperty().
 *
 * Зарегистрирован в install/index.php.
 */

namespace Mycompany\EmptyModule\Handlers\Main;

use Bitrix\Main\Diag\Logger;

class Epilog
{
    public static function onEpilog(): void
    {
        // Пример: логируем 404, чтобы видеть битые ссылки.
        if (defined('ERROR_404') && ERROR_404 === 'Y') {
            $logger = Logger::create('mycompany.emptymodule');
            $logger->warning('404 hit', [
                'url'      => $_SERVER['REQUEST_URI'] ?? '',
                'referer'  => $_SERVER['HTTP_REFERER'] ?? '',
            ]);
        }
    }
}
