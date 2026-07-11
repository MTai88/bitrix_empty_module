<?php
/**
 * Health-check контроллер.
 *
 * Эндпоинт: GET /api/v1/health
 *
 * Возвращает состояние модуля, версию PHP, наличие зависимых модулей.
 * Полезен для мониторинга и smoke-тестов.
 *
 * Пример ответа:
 *  {
 *    "status": "ok",
 *    "module": "mycompany.emptymodule",
 *    "module_version": "1.0.0",
 *    "php": "8.3.10",
 *    "bitrix": "24.0.0",
 *    "modules": { "iblock": "loaded", "main": "loaded" },
 *    "time": "2026-07-11T19:30:00+03:00"
 *  }
 */

namespace Mycompany\EmptyModule\Controller;

use Bitrix\Main\Loader;

class Health extends Base
{
    public function getDefaultPreFilters(): array
    {
        // Отключаем csrf и авторизацию — health-check должен быть публичным.
        return [];
    }

    public function indexAction(): array
    {
        $report = [
            'status'  => 'ok',
            'module'  => 'mycompany.emptymodule',
            'php'     => PHP_VERSION,
            'bitrix'  => defined('SM_VERSION') ? SM_VERSION : 'unknown',
            'modules' => $this->checkModules(),
            'time'    => date('c'),
        ];

        // Версия модуля из /bitrix/modules/<MODULE_ID>/install/version.php
        $versionFile = $_SERVER['DOCUMENT_ROOT']
            . '/local/modules/mycompany.emptymodule/install/version.php';
        if (is_file($versionFile)) {
            $arModuleVersion = [];
            include $versionFile;
            if (!empty($arModuleVersion['VERSION'])) {
                $report['module_version'] = $arModuleVersion['VERSION'];
            }
        }

        return $report;
    }

    /**
     * @return array<string, string>  moduleId => 'loaded' | 'missing'
     */
    private function checkModules(): array
    {
        $need = ['main', 'iblock'];
        $out = [];
        foreach ($need as $id) {
            $out[$id] = Loader::includeModule($id) ? 'loaded' : 'missing';
        }
        return $out;
    }
}
