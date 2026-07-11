<?php
/**
 * Хелпер для получения ID инфоблоков по символьному коду.
 *
 * Кеширует результат в managed_cache на 30 дней.
 * Безопасен к вызову до инициализации SITE_ID (если передать явно).
 *
 * DEPRECATED: рекомендуется мигрировать на
 *   Mycompany\EmptyModule\Helpers\IblockHelper::getIdByCode()
 * Класс сохранён для обратной совместимости — публичный API тот же.
 */

namespace Mycompany\EmptyModule\Tools\Helper;

use Bitrix\Main;

class Ids
{
    private const CACHE_ID_PREFIX = 'mt_emptymodule_id_to_cache_';
    private const CACHE_DIR       = '/mt_emptymodule_id_to_cache';
    private const CACHE_TTL       = 2592000; // 30 дней

    /** @var \CPHPCache|null */
    private static $cache;

    /** @var array<string, string>|null */
    private static $iblockIds;

    private static function getCacheObject(): \CPHPCache
    {
        if (!self::$cache) {
            self::$cache = new \CPHPCache();
        }
        return self::$cache;
    }

    private static function getFromCache(string $id)
    {
        $cacheId = self::CACHE_ID_PREFIX . $id;

        if (
            self::getCacheObject()->InitCache(self::CACHE_TTL, $cacheId, self::CACHE_DIR)
            && ($tmpVal = self::getCacheObject()->GetVars())
        ) {
            return $tmpVal;
        }
        return null;
    }

    private static function putIntoCache(string $id, $data): void
    {
        $cacheId = self::CACHE_ID_PREFIX . $id;
        if (
            self::getCacheObject()->InitCache(self::CACHE_TTL, $cacheId, self::CACHE_DIR)
            && self::getCacheObject()->StartDataCache()
        ) {
            self::getCacheObject()->EndDataCache($data);
        }
    }

    public static function getIblockId(string $iBlockCode, ?string $siteId = null): ?string
    {
        $siteId = $siteId ?: (defined('SITE_ID') ? SITE_ID : null);

        if (self::$iblockIds === null) {
            $val = self::getFromCache('iblockIds');

            if (!is_array($val) && Main\Loader::IncludeModule('iblock')) {
                $val = [];

                $dbRes = \CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N']);
                while ($res = $dbRes->Fetch()) {
                    $val[$res['CODE']]       = $res['ID'];
                    $val[$res['XML_ID']]     = $res['ID'];
                    $val[$res['CODE'] . '_' . ($siteId ?? '')] = $res['ID'];
                }
                self::putIntoCache('iblockIds', $val);
            }

            self::$iblockIds = is_array($val) ? $val : [];
        }

        if (isset(self::$iblockIds[$iBlockCode])) {
            return (string) self::$iblockIds[$iBlockCode];
        }

        if ($siteId !== null && isset(self::$iblockIds[$iBlockCode . '_' . $siteId])) {
            return (string) self::$iblockIds[$iBlockCode . '_' . $siteId];
        }

        return null;
    }
}
