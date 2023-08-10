<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Class Mycompany_EmptyModule extends CModule
{
    var $MODULE_ID = "mycompany.emptymodule";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $errors;

    function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__.'/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("MT_MODULE_INSTALL_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("MT_MODULE_INSTALL_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("MT_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("MT_MODULE_PARTNER_URI");
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
        \Bitrix\Main\ModuleManager::RegisterModule("mycompany.emptymodule");
        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        \Bitrix\Main\ModuleManager::UnRegisterModule("mycompany.emptymodule");
        return true;
    }

    function InstallDB()
    {
        global $DB;
        $this->errors = false;
        //$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/mycompany.emptymodule/install/db/install.sql");
        if (!$this->errors) {

            return true;
        } else
            return $this->errors;
    }

    function UnInstallDB()
    {
        global $DB;
        $this->errors = false;
        //$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/mycompany.emptymodule/install/db/uninstall.sql");
        if (!$this->errors) {
            return true;
        } else
            return $this->errors;
    }

    function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->registerEventHandler(
            'main',
            'OnPageStart',
            'mycompany.emptymodule',
            \Mycompany\EmptyModule\Handlers\HandlerRegister::class,
            'init'
        );

        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'main',
            'OnPageStart',
            'mycompany.emptymodule',
            \Mycompany\EmptyModule\Handlers\HandlerRegister::class,
            'init'
        );

        return true;
    }

    function InstallFiles()
    {
        //CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);

        return true;
    }

    function UnInstallFiles()
    {
        //DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$this->MODULE_ID.'/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

        return true;
    }
}