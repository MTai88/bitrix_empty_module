<?php
/**
 * Локализация страницы настроек модуля (/bitrix/admin/settings.php?mid=...).
 *
 * Ключи MT_OPTIONS_* соответствуют опциям, описанным в options.php.
 */

$MESS['MT_OPTIONS_TAB_MAIN']      = 'Основные';
$MESS['MT_OPTIONS_TAB_MAIN_TITLE'] = 'Базовые параметры модуля';

$MESS['MT_OPTIONS_TAB_DEBUG']      = 'Отладка';
$MESS['MT_OPTIONS_TAB_DEBUG_TITLE'] = 'Логирование и диагностика';

$MESS['MT_OPTIONS_API_URL']     = 'Базовый URL внешнего сервиса';
$MESS['MT_OPTIONS_API_URL_HINT'] = 'Например, https://api.example.com/v1. Используется в HttpClient.';

$MESS['MT_OPTIONS_API_KEY']         = 'API-ключ';
$MESS['MT_OPTIONS_API_KEY_HINT']    = 'Хранится в БД. Чувствительные данные лучше вынести в env.';

$MESS['MT_OPTIONS_MODE']      = 'Режим работы модуля';
$MESS['MT_OPTIONS_MODE_PROD'] = 'Production';
$MESS['MT_OPTIONS_MODE_STAGE'] = 'Staging';
$MESS['MT_OPTIONS_MODE_DEV']  = 'Development';

$MESS['MT_OPTIONS_ENABLE_LOG']         = 'Включить логирование';
$MESS['MT_OPTIONS_ENABLE_LOG_HINT']    = 'Писать в /bitrix/modules/mycompany.emptymodule/logs/module.log';

$MESS['MT_OPTIONS_LOG_LEVEL']      = 'Уровень логирования';
$MESS['MT_OPTIONS_LOG_LEVEL_DEBUG'] = 'DEBUG';
$MESS['MT_OPTIONS_LOG_LEVEL_INFO']  = 'INFO';
$MESS['MT_OPTIONS_LOG_LEVEL_WARN']  = 'WARNING';
$MESS['MT_OPTIONS_LOG_LEVEL_ERROR'] = 'ERROR';
