<?php
/**
 * Autoload модуля.
 *
 * Регистрирует PSR-4-style маппинг namespace Mycompany\EmptyModule\
 * на каталог /lib/ относительно корня модуля.
 *
 * Вызывается автоматически при Loader::includeModule('<MODULE_ID>').
 * Без этой строки Битрикс не будет находить наши классы по namespace
 * (Loader по умолчанию не сканирует /lib/ модуля).
 */

if (!class_exists('Bitrix\\Main\\Loader')) {
    return;
}

\Bitrix\Main\Loader::registerNamespace(
    'Mycompany\\EmptyModule',
    __DIR__ . '/lib'
);
