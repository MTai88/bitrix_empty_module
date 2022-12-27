<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Class MycompanyEmptyModule extends CModule
{
    var $MODULE_ID = "mycompany.empty_module";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $errors;

    function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__.'/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("MT_MODULE_INSTALL_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("MT_MODULE_INSTALL_DESCRIPTION");
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
        \Bitrix\Main\ModuleManager::RegisterModule("mycompany.empty_module");
        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        \Bitrix\Main\ModuleManager::UnRegisterModule("mycompany.empty_module");
        return true;
    }

    function InstallDB()
    {
        global $DB;
        $this->errors = false;
        //$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/mycompany.empty_module/install/db/install.sql");
        if (!$this->errors) {

            return true;
        } else
            return $this->errors;
    }

    function UnInstallDB()
    {
        global $DB;
        $this->errors = false;
        //$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/mycompany.empty_module/install/db/uninstall.sql");
        if (!$this->errors) {
            return true;
        } else
            return $this->errors;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }
}