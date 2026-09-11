# mycompany.emptymodule

> Скаффолд (заготовка) для кастомных модулей Битрикс24 на D7.
> Готовый набор классов, хелперов и примеров — чтобы за пару часов получить
> рабочий модуль, а не с нуля придумывать структуру.

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb4)](https://www.php.net)
[![Bitrix](https://img.shields.io/badge/Bitrix-21.0%2B-ffd82c)](https://www.1c-bitrix.ru)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

---

## Назначение

Это **шаблон** для партнёрских модулей Битрикс24, которые:

- работают на D7 (PSR-4 namespace `Mycompany\EmptyModule`);
- используют современные подходы — контроллеры `\Bitrix\Main\Engine\Controller`,
  ORM `\Bitrix\Main\ORM\Entity`, агенты, тегированные обработчики;
- разворачиваются в `/local/modules/` (не в `/bitrix/modules/`);
- должны работать в связке с локальным dev-стендом `docker_bitrix24`.

Под капотом — проверенная структура, которая собирается и устанавливается
без ручной правки. Берёте, переименовываете через `install.sh`, дописываете
свою бизнес-логику в `lib/`.

---

## Содержимое

```
mycompany.emptymodule/
├── .settings.php                       # Конфиг модуля (контроллеры, админ-меню)
├── include.php                         # PSR-4 autoload для Mycompany\EmptyModule\
├── options.php                         # Страница настроек в админке
├── install/
│   ├── index.php                       # Класс CModule + DoInstall/DoUninstall
│   ├── step.php                        # Шаг 1 установки (форма подтверждения)
│   ├── unstep.php                      # Шаг 1 удаления
│   ├── version.php                     # Версия + дата
│   └── db/install.sql, uninstall.sql   # SQL-миграции (пустые по умолчанию)
├── lang/
│   └── ru/                             # Локализация (русский)
└── lib/
    ├── Controller/                     # D7-контроллеры (REST)
    │   ├── Base.php                    # Базовый контроллер (логирование)
    │   ├── Health.php                  # /api/v1/health
    │   └── User.php                    # /api/v1/user.getInfo, user.find
    ├── Handlers/                       # Обработчики событий
    │   ├── HandlerRegister.php         # Namespace-точка для динамических хендлеров
    │   ├── Iblock/News.php             # Пример: iblock add/update
    │   └── Main/Epilog.php             # Пример: main:OnEpilog (лог 404)
    ├── Helpers/                        # Хелперы (статика)
    │   ├── Logger.php                  # Обёртка над Diag\FileLogger
    │   └── Config.php                  # Чтение /bitrix/.settings.php + env
    ├── Services/Http/                  # HTTP-клиент
    │   ├── HttpClient.php              # Современная обёртка над \Bitrix\Main\HttpClient
    │   └── Response.php                # DTO ответа
    ├── Agents/
    │   └── SampleAgent.php             # Пример агента (SampleAgent::run())
    ├── Orm/
    │   └── LogTable.php                # Пример ORM-таблицы
    └── Tools/Helper/
        └── Ids.php                     # Кеш ID инфоблоков по символьному коду (legacy)
```

---

## Требования

- PHP 8.1+
- Битрикс 21.0+ (D7)
- Модули Битрикса: `main`, `iblock` (для примера)
- Права на запись в `/bitrix/modules/<MODULE_ID>/logs/`

---

## Установка

### 1. Переименование под проект

Скрипт `install.sh` в корне репозитория заменяет имена модуля, namespace
и legacy-классы во всех файлах, и переименовывает папку.

```bash
cd /path/to/repo
./install.sh
# Enter module ID (vendor.module) [acme.delymodule]: acme.delivery
# Enter module namespace (Vendor_Module) [Acme\Delymodule]: Acme\Delivery
# Продолжить? [y/N]: y
```

После этого в репозитории появится папка `acme.delivery` вместо
`mycompany.emptymodule`.

**Совместимость скрипта:** bash 3+ (Git Bash / WSL / Linux / macOS). Не
работает в чистом `cmd.exe` — там используйте Git Bash.

### 2. Копирование в проект

Скопируйте папку `acme.delivery` (или `mycompany.emptymodule`, если не
переименовали) в `/local/modules/` вашего Битрикса:


### 3. Установка через админку

1. Откройте `/bitrix/admin/partner_modules.php?lang=ru`
2. Найдите `mycompany.emptymodule` (или ваш ID)
3. Нажмите «Установить»
4. Следуйте шагам мастера (шаг 1: подтверждение, шаг 2: фактическая установка)
5. Готово — модуль в `/bitrix/admin/settings.php?mid=mycompany.emptymodule`

### 4. Установка через CLI (для CI / smoke-тестов)

В контейнере PHP:

```bash
docker exec <container> php /path/to/script.php
```

Где `script.php` — кастомный smoke-скрипт, который подключает ядро,
требует `install/index.php` и вызывает `DoInstall()`. 

---

## Использование

### Контроллеры (REST API)

Все контроллеры модуля наследуются от `Base` и доступны по адресу
`/api/v1/<action>`.

| Action                        | Endpoint                          | Описание                            |
| ----------------------------- | --------------------------------- | ----------------------------------- |
| `Health::index`               | `GET /api/v1/health`              | Health-check: версии, модули, время |
| `User::getInfo`               | `GET /api/v1/user.getInfo`        | Данные текущего залогиненного юзера |
| `User::find`                  | `GET /api/v1/user.find?login=X`   | Поиск по логину (только админы)     |

Свой контроллер — наследник `Base`:

```php
namespace Acme\Delivery\Controller;

use Acme\Delivery\Controller\Base;
use Bitrix\Main\Engine\CurrentUser;

class Order extends Base
{
    public function getAction(int $id): ?array
    {
        $user = CurrentUser::get();
        if (!$user->getId()) {
            $this->addError(new \Bitrix\Main\Error('Требуется авторизация', 'AUTH_REQUIRED'));
            return null;
        }

        // ... бизнес-логика
        return ['id' => $id, 'status' => 'ok'];
    }
}
```

### Хелперы

#### `Logger` — модульный логгер

```php
use Mycompany\EmptyModule\Helpers\Logger;

Logger::debug('Что-то происходит', ['order_id' => 42]);
Logger::info('Создан заказ', ['id' => 42, 'sum' => 1000]);
Logger::error('Не удалось отправить в CRM', ['code' => 500]);

// Уровни и on/off управляются из админки (b_option: enable_log, log_level)
// Файл: /bitrix/modules/mycompany.emptymodule/logs/module.log
```

#### `Config` — конфиг из .settings.php + env

```php
use Mycompany\EmptyModule\Helpers\Config;

// Чтение из /bitrix/.settings.php по dot-нотации
$apiUrl = Config::get('mycompany.api_url', 'https://default.example.com');

// Из env (CI / docker .env)
$dsn = Config::env('SENTRY_DSN');

// Опция модуля (редактируется из админки)
$mode = Config::moduleOption('mode', 'prod');

// Helpers
if (Config::isDev()) {
    // ...
}
```

#### `HttpClient` — современный HTTP-клиент

```php
use Mycompany\EmptyModule\Services\Http\HttpClient;

$client = (new HttpClient())
    ->setBaseUrl('https://api.example.com/v1')
    ->setBearer('TOKEN')
    ->setTimeout(15)
    ->setRetries(2, 200);

$response = $client->get('/users/42');

if ($response->isOk()) {
    $data = $response->json();  // mixed
}

if ($response->isServerError() || $response->hasError()) {
    Logger::error('API call failed', [
        'status' => $response->getStatus(),
        'error'  => $response->getError(),
    ]);
}
```

### Агенты

`SampleAgent` — пример, зарегистрирован в `DoInstall()`. Чтобы
запустить **сразу** в CLI:

```php
\Mycompany\EmptyModule\Agents\SampleAgent::run();
```

Свой агент — статический метод + регистрация в `InstallAgents()`:

```php
namespace Acme\Delivery\Agents;

class CleanupAgent
{
    public static function run(): string
    {
        // ... чистим старые записи
        \Mycompany\EmptyModule\Helpers\Logger::info('Cleanup done');
        return __METHOD__ . '();';  // повторять
    }
}
```

В `install/index.php` → `InstallAgents()`:

```php
\CAgent::AddAgent(
    \Acme\Delivery\Agents\CleanupAgent::class . '::run();',
    $this->MODULE_ID,
    'Y',      // периодический
    3600,     // каждый час
    '',       // первая проверка — сейчас
    'Y',      // активен
    '',
    100
);
```

### Обработчики событий

Регистрация — в `install/index.php` → `InstallEvents()` через
`EventManager::registerEventHandler()`. Это **правильный** способ (запись
в `b_module_event`), а не динамическая регистрация на каждом хите.

Пример своего обработчика:

```php
namespace Acme\Delivery\Handlers;

use Mycompany\EmptyModule\Helpers\Logger;

class Order
{
    public static function onCreate(int $orderId): void
    {
        if ($orderId <= 0) {
            return;
        }
        Logger::info('Order created', ['id' => $orderId]);
    }
}
```

Регистрация:

```php
$eventManager->registerEventHandler(
    'sale',
    'OnOrderAdd',
    $this->MODULE_ID,
    \Acme\Delivery\Handlers\Order::class,
    'onCreate'
);
```

---

## Настройка

### `include.php` — автозагрузка

```php
\Bitrix\Main\Loader::registerNamespace(
    'Mycompany\\EmptyModule',
    __DIR__ . '/lib'
);
```

Без этого `Loader` не будет находить классы модуля по namespace.
Обязательно, если модуль лежит в `/local/modules/`.

### `.settings.php` — параметры модуля

```php
return [
    'controllers' => [
        'value'    => ['defaultNamespace' => '\\Mycompany\\EmptyModule\\Controller'],
        'readonly' => true,
    ],
    'adminSection' => [
        'value' => [
            'parent_menu' => 'global_menu_services',
            'section'     => 'mycompany_emptymodule',
            'url'         => '/bitrix/admin/settings.php?lang=ru&mid=mycompany.emptymodule',
        ],
        'readonly' => true,
    ],
];
```

### Опции модуля (b_option)

Редактируются из админки → Настройки → Модули → «mycompany: EmptyModule».

| Ключ         | Дефолт | Описание                                       |
| ------------ | ------ | ---------------------------------------------- |
| `api_url`    | ''     | Базовый URL внешнего сервиса                   |
| `api_key`    | ''     | API-ключ (можно хранить в env, см. Config)     |
| `mode`       | prod   | `prod` / `stage` / `dev`                       |
| `enable_log` | N      | Y/N — писать в `/bitrix/modules/<ID>/logs/`    |
| `log_level`  | INFO   | DEBUG / INFO / WARNING / ERROR                 |

---

## Расширение

### Добавить новый хелпер

1. Создайте `lib/Helpers/<Name>.php` в namespace `Mycompany\EmptyModule\Helpers`
2. Класс `final`, статические методы
3. Добавьте PHPDoc с примерами
4. Добавьте unit-тест в `tests/Unit/Helpers/`

### Добавить новый контроллер

1. Создайте `lib/Controller/<Name>.php`, наследник `Base`
2. Имя класса = `Mycompany\EmptyModule\Controller\<Name>`
3. Действия — публичные методы, заканчивающиеся на `Action`
4. Endpoint: `GET /api/v1/<action>`, где `action` = `<actionName>Action` минус `Action`

### Добавить ORM-таблицу

1. Создайте `lib/Orm/<Name>Table.php`, наследник `DataManager`
2. `getTableName()` → реальное имя в БД
3. `getMap()` → массив `Field`
4. SQL для создания — в `install/db/install.sql`
5. Создание через `\Bitrix\Main\Entity\Base::getInstance(...)->createDbTable();` в `InstallDB()`

---

## Тестирование

### Smoke-тест (PHPUnit / обычный PHP)

В проекте `bitrix24_test` есть скрипт `scripts/module_check.php` —
подключает ядро как cron, ставит модуль, проверяет:

- регистрация в `b_module`
- опции в `b_option`
- обработчики в `b_module_event`
- агент в `b_agent`
- автозагрузка всех классов

Запуск:

```bash
docker exec bitrix-test-php-1 php /tmp/module_check.php
```

JSON-отчёт: `/tmp/module_check.json` и STDERR (`=== MODULE_REPORT_START ===` ... `=== MODULE_REPORT_END ===`).

### Unit-тесты (PHPUnit)

Внутри docker-контейнера:

```bash
docker exec bitrix-test-php-1 composer install
docker exec bitrix-test-php-1 vendor/bin/phpunit
```

### Статанализ (PHPStan)

```bash
docker exec bitrix-test-php-1 composer install
docker exec bitrix-test-php-1 vendor/bin/phpstan analyse
```

### Code style (php-cs-fixer)

```bash
docker exec bitrix-test-php-1 composer cs-check
docker exec bitrix-test-php-1 composer cs-fix
```

---

## Развёртывание

### Из dev → prod

1. **Не коммитим** `.env`, `*.local.php`, `secrets.json` (см. `.gitignore`).
2. **Проверяем** что в `install/version.php` новая версия.
3. **Прогоняем** `composer cs-check && composer phpstan && composer test`.
4. **Помечаем** git-тег: `git tag -a v1.0.0 -m "Release 1.0.0"`.
5. **Копируем** папку `mycompany.emptymodule` в `/local/modules/` на сервере.
6. **Устанавливаем** через `/bitrix/admin/partner_modules.php`.

### Обновление

1. Заменить папку в `/local/modules/`
2. Зайти в `/bitrix/admin/partner_modules.php` → «Переустановить»
3. Если менялся `install/index.php` — Битрикс вызовет `DoInstall()` повторно;
   при правильной идемпотентности (проверки `isModuleInstalled`,
   `CAgent::GetList`) обновление безопасно.


