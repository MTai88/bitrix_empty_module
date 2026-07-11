<?php
/**
 * Инсталлятор модуля Mycompany EmptyModule.
 *
 * Содержит сценарии установки/удаления: БД, события, агенты, файлы, опции.
 * Класс CModule — legacy, но именно его ожидает партнёрский маркетплейс
 * и стандартный установщик из /bitrix/admin/partner_modules.php.
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class mycompany_emptymodule extends CModule
{
    /** @var string */
    public $MODULE_ID = 'mycompany.emptymodule';

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $PARTNER_NAME;

    /** @var string */
    public $PARTNER_URI;

    /** @var string */
    public $MODULE_GROUP_RIGHTS = 'R';

    /** @var bool|array */
    public $errors;

    public function __construct()
    {
        $arModuleVersion = [];

        include __DIR__ . '/version.php';

        $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME        = Loc::getMessage('MT_MODULE_INSTALL_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MT_MODULE_INSTALL_DESCRIPTION');
        $this->PARTNER_NAME       = Loc::getMessage('MT_MODULE_PARTNER_NAME');
        $this->PARTNER_URI        = Loc::getMessage('MT_MODULE_PARTNER_URI');
    }

    /**
     * Точка входа установки.
     *
     * Возвращает bool|string: true — успех; string — текст ошибки для админки.
     */
    public function DoInstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if (!$this->checkRequirements()) {
            $APPLICATION->ThrowException(Loc::getMessage('MT_MODULE_INSTALL_REQUIRE_KERNEL'));
            return false;
        }

        $step = (int) $request->get('step');

        if ($step < 2) {
            // Шаг 1: вывод формы подтверждения. Подключаем шаблон.
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('MT_MODULE_INSTALL_TITLE'),
                __DIR__ . '/step.php'
            );
            return true;
        }

        // Шаг 2: фактическая установка.
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallAgents();
        $this->InstallFiles();
        $this->InstallOptions();

        ModuleManager::RegisterModule($this->MODULE_ID);

        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        $step = (int) $request->get('step');

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('MT_MODULE_UNINSTALL_TITLE'),
                __DIR__ . '/unstep.php'
            );
            return true;
        }

        $saved = $request->get('saved');
        $tables = $request->get('tables') === 'Y';

        $this->UnInstallOptions();
        $this->UnInstallFiles();
        $this->UnInstallAgents();
        $this->UnInstallEvents();

        if ($tables) {
            $this->UnInstallDB();
        }

        // 'saved' оставляет данные в b_option (для повторной установки).
        if (!$saved) {
            ModuleManager::UnRegisterModule($this->MODULE_ID);
        }

        return true;
    }

    /**
     * Проверка совместимости с ядром Битрикса.
     */
    private function checkRequirements(): bool
    {
        // SM_VERSION задан в /bitrix/modules/main/version.php
        if (defined('SM_VERSION') && version_compare(SM_VERSION, '21.0.0', '<')) {
            return false;
        }
        return true;
    }

    // =============================================================
    //  БД
    // =============================================================

    public function InstallDB()
    {
        $this->errors = false;

        // $this->errors = $this->runSql('install/db/install.sql');

        return $this->errors ?: true;
    }

    public function UnInstallDB()
    {
        $this->errors = false;

        // $this->errors = $this->runSql('install/db/uninstall.sql');

        return $this->errors ?: true;
    }

    /**
     * Прогон SQL-файла через CDatabase::RunSQLBatch.
     */
    private function runSql(string $relativePath)
    {
        global $DB;
        $path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/' . ltrim($relativePath, '/');
        if (!is_file($path)) {
            return false;
        }
        return $DB->RunSQLBatch($path);
    }

    // =============================================================
    //  События
    // =============================================================

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        // Регистрация хендлеров "в коде" — современный путь.
        // ВАЖНО: это и есть единственная точка регистрации. Не дублируйте
        // через OnPageStart + addEventHandler внутри модуля — будет двойной
        // вызов (см. lib/Handlers/HandlerRegister.php для истории).

        $eventManager->registerEventHandler(
            'iblock',
            'OnAfterIblockElementAdd',
            $this->MODULE_ID,
            \Mycompany\EmptyModule\Handlers\Iblock\News::class,
            'afterAdd'
        );

        $eventManager->registerEventHandler(
            'iblock',
            'OnAfterIblockElementUpdate',
            $this->MODULE_ID,
            \Mycompany\EmptyModule\Handlers\Iblock\News::class,
            'afterUpdate'
        );

        $eventManager->registerEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            \Mycompany\EmptyModule\Handlers\Main\Epilog::class,
            'onEpilog'
        );

        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnAfterIblockElementAdd',
            $this->MODULE_ID,
            \Mycompany\EmptyModule\Handlers\Iblock\News::class,
            'afterAdd'
        );

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnAfterIblockElementUpdate',
            $this->MODULE_ID,
            \Mycompany\EmptyModule\Handlers\Iblock\News::class,
            'afterUpdate'
        );

        $eventManager->unRegisterEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            \Mycompany\EmptyModule\Handlers\Main\Epilog::class,
            'onEpilog'
        );

        return true;
    }

    // =============================================================
    //  Агенты
    // =============================================================

    public function InstallAgents()
    {
        $agentClass = \Mycompany\EmptyModule\Agents\SampleAgent::class;
        $agentName  = $agentClass . '::run();';

        // Добавляем агент, если его ещё нет. Идемпотентно.
        // Фильтр по NAME (полное имя функции/метода) — стандарт для CAgent.
        $exists = false;
        $agentRes = \CAgent::GetList([], ['NAME' => $agentName]);
        if ($agentRes->Fetch()) {
            $exists = true;
        }

        if (!$exists) {
            \CAgent::AddAgent(
                $agentName,
                $this->MODULE_ID,
                'N',                      // is_period: не периодический — выполняется один раз
                86400,                    // interval, сек (24ч) — используется, если периодический
                '',                       // date_first_check
                'Y',                      // active
                '',                       // date_next_exec
                100                       // sort
            );
        }

        return true;
    }

    public function UnInstallAgents()
    {
        $agentClass = \Mycompany\EmptyModule\Agents\SampleAgent::class;

        \CAgent::RemoveModuleAgents($this->MODULE_ID);

        return true;
    }

    // =============================================================
    //  Файлы
    // =============================================================

    public function InstallFiles()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];

        // Компоненты: /bitrix/components/mycompany/emptymodule.*
        // CopyDirFiles(
        //     $root . '/bitrix/modules/' . $this->MODULE_ID . '/install/components',
        //     $root . '/bitrix/components/mycompany',
        //     true, true
        // );

        // Админские страницы: /bitrix/admin/mycompany_*.php
        // CopyDirFiles(
        //     $root . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin',
        //     $root . '/bitrix/admin',
        //     true, true
        // );

        return true;
    }

    public function UnInstallFiles()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];

        // DeleteDirFiles(...) — обратная операция.

        return true;
    }

    // =============================================================
    //  Опции (редактируемые из админки)
    // =============================================================

    public function InstallOptions()
    {
        // Дефолты — безопасные. Дальше админ правит через options.php.
        \COption::SetOptionString($this->MODULE_ID, 'api_url', '');
        \COption::SetOptionString($this->MODULE_ID, 'api_key', '');
        \COption::SetOptionString($this->MODULE_ID, 'mode', 'prod');
        \COption::SetOptionString($this->MODULE_ID, 'enable_log', 'N');
        \COption::SetOptionString($this->MODULE_ID, 'log_level', 'INFO');

        return true;
    }

    public function UnInstallOptions()
    {
        \COption::RemoveOption($this->MODULE_ID);
        return true;
    }
}
