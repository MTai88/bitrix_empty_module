# Changelog

Все значимые изменения в модуле `mycompany.emptymodule` документируются здесь.
Формат — [Keep a Changelog](https://keepachangelog.com/ru/1.1.0/),
версии — [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-07-11

### Добавлено
- Современный PSR-4-каркас модуля для Битрикс24 (D7).
- `lib/Controller/Base.php` — базовый контроллер с логированием.
- `lib/Controller/Health.php` — `/api/v1/health` для мониторинга.
- `lib/Controller/User.php` — `/api/v1/user.getInfo`, `/api/v1/user.find`.
- `lib/Handlers/Iblock/News.php` — пример обработчика iblock (add/update) с
  кешем и логированием (без `Debug::dump`).
- `lib/Handlers/Main/Epilog.php` — пример для `main:OnEpilog` (лог 404).
- `lib/Handlers/HandlerRegister.php` — namespace-точка для динамических хендлеров.
- `lib/Helpers/Logger.php` — обёртка над `Diag\FileLogger` с настройками
  из `b_option` (enable_log, log_level).
- `lib/Helpers/Config.php` — чтение `/bitrix/.settings.php` + `getenv()` + `b_option`.
- `lib/Services/Http/HttpClient.php` — современный клиент на базе
  `\Bitrix\Main\HttpClient` (с retry, bearer, JSON).
- `lib/Services/Http/Response.php` — DTO ответа (status/body/headers/json/retry).
- `lib/Agents/SampleAgent.php` — пример агента.
- `lib/Orm/LogTable.php` — пример ORM-таблицы.
- `lib/Tools/Helper/Ids.php` — кеш ID инфоблоков по коду (legacy, оставлен).
- `install/index.php` — полноценный инсталлятор: проверка версии Битрикса,
  регистрация событий и агентов, опции, файлы.
- `install/step.php`, `install/unstep.php` — мастер установки/удаления.
- `.settings.php` — настройка `defaultNamespace` + `adminSection`.
- `include.php` — PSR-4 автозагрузка через `Loader::registerNamespace()`.
- `options.php` — страница настроек с 2 табами (основные + отладка).
- `lang/ru/` — локализация (5+ ключей для опций, 4 для инсталлятора).
- `install.sh` — кросс-платформенный скрипт переименования под проект.
- `composer.json` — dev-зависимости (phpstan, psalm, php-cs-fixer, phpunit).
- `phpstan.neon`, `phpunit.xml.dist`, `.php-cs-fixer.dist.php` — конфиги.
- `tests/bootstrap.php`, `tests/Unit/Tools/IdsTest.php` — пример тестов.
- `.gitignore`, `.editorconfig`.
- `CHANGELOG.md` (этот файл).
- `README.md` — подробная документация на русском.

### Изменено
- `install/index.php` — переписан с нуля; убрана двойная регистрация
  событий через `OnPageStart` (анти-паттерн).
- `install/version.php` — обновлён: 1.0.0 / 2026-07-11.
- `lib/Handlers/HandlerRegister.php` — упрощён, динамическая регистрация
  вынесена в `installConditionalHandlers()` (использовать осторожно).
- `lib/Handlers/Iblock/News.php` — `Debug::dump() + exit()` заменены на
  нормальное логирование.
- `lib/Tools/Helper/Ids.php` — опечатка `getFormCache` → `getFromCache`.
- `lang/ru/install/index.php` — обновлены ключи, добавлен `MT_MODULE_PARTNER_*`.
- `lang/ru/options.php` — добавлена локализация для всех опций.

### Удалено
- `lib/Tools/WebService/CurlClient.php` — устаревший PHP-5-style HTTP-клиент.
  Заменён на `lib/Services/Http/HttpClient.php`.

### Безопасность
- Включён `declare(strict_types=1)` во всех новых файлах.
- Опция `enable_log=N` по умолчанию (логи выключены на prod до явного включения).
- `Logger::error()` пишется ВСЕГДА, даже при `enable_log=N` (для отлова критичных ошибок).

## [0.2] — 2023-08-07

### Добавлено
- Первая версия заготовки от Tair Minsafaev (https://github.com/MTai88).
- Базовая структура: `.settings.php`, `include.php`, `options.php`,
  `install/index.php`, `install/version.php`, `lang/ru/`.
- Примеры: `Controller/User`, `Handlers/HandlerRegister`, `Handlers/Iblock/News`,
  `Tools/Helper/Ids`, `Tools/WebService/CurlClient`.
- `install.sh` — скрипт переименования.

### Известные проблемы этой версии
- `OnPageStart → HandlerRegister::init()` — двойная регистрация событий.
- `Debug::dump() + exit()` в `News::afterAdd/afterUpdate` — дебажный код.
- Опечатка `getFormCache` в `Ids`.
- CurlClient в стиле PHP 5 (`__get`, префиксы `_`).
- Отсутствие dev-инструментов (composer, phpstan, phpunit).
- README в 6 строк на английском.
- Нет проверки версии Битрикса в `DoInstall()`.
- Нет регистрации агентов.
