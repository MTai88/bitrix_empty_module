<?php
/**
 * Настройки модуля.
 *
 * Конфиг хранится в /bitrix/.settings.php Битрикса в секции modules
 * с ключом 'mycompany.emptymodule'. Используется для runtime-параметров,
 * которые не должны меняться из админки (адреса сервисов, секреты из env,
 * feature flags, не редактируемые напрямую).
 *
 * Что сюда НЕ класть:
 *  - редактируемые из админки значения — для них есть options.php / .settings.php override;
 *  - секреты, которые нельзя светить в файловой системе — они идут из env.
 *
 * @var array
 */
return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Mycompany\\EmptyModule\\Controller',
        ],
        'readonly' => true,
    ],

    'adminSection' => [
        'value' => [
            // Подменю в "Настройки" → "Настройки продукта" → "Пользовательские модули".
            // Если null — модуль появится только в Marketplace → Установленные решения.
            'parent_menu' => 'global_menu_services',
            'section'     => 'mycompany_emptymodule',
            'sort'        => 1000,
            'text'        => 'Mycompany EmptyModule',
            'title'       => 'Mycompany EmptyModule — настройки модуля',
            'url'         => '/bitrix/admin/settings.php?lang=ru&mid=mycompany.emptymodule',
        ],
        'readonly' => true,
    ],
];
