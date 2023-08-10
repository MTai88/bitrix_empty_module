<?php
namespace Mycompany\EmptyModule\Tools\Helper;

use \Bitrix\Main;

class Ids
{
	private static string $cacheId = 'id_to_cache_';
	private static string $cacheDir = '/id_to_cache';
	private static int $cacheTtl = 2592000;
	private static \CPHPCache $cache;

	private static ?array $iblockIds = [];

	private static function getCacheObject(): \CPHPCache
	{
		if (!isset(self::$cache))
		{
			self::$cache = new \CPHPCache;
		}
		return self::$cache;
	}

	private static function getFormCache(string $id)
	{
		$cacheId = implode('_', [self::$cacheId, $id]);

		if (self::getCacheObject()->InitCache(self::$cacheTtl, $cacheId, self::$cacheDir)
			&& ($tmpVal = self::getCacheObject()->GetVars())
		)
		{
			return $tmpVal;
		}
		return null;
	}

	private static function putIntoCache(string $id, $data): void
	{
		$cacheId = implode('_', [self::$cacheId, $id]);
		if (
			self::getCacheObject()->InitCache(self::$cacheTtl, $cacheId, self::$cacheDir)
			&& self::getCacheObject()->StartDataCache()
		)
		{
			self::getCacheObject()->EndDataCache($data);
		}
	}

	public static function getIblockId(string $iBlockCode, string $siteId = null): ?string
	{
		$siteId = $siteId ?: SITE_ID;

		if (empty(self::$iblockIds))
		{
			$cacheId = 'iblockIds';
			$val = self::getFormCache($cacheId);

			if (!is_array($val) && Main\Loader::IncludeModule('iblock'))
			{
				$val = [];

				$dbRes = \CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N']);
				while ($res = $dbRes->Fetch())
				{
					$val[$res['CODE']] = $res['ID'];
					$val[$res['XML_ID']] = $res['ID'];
				}
				self::putIntoCache($cacheId, $val);
			}
			self::$iblockIds = $val;
		}
		$iBlockCodeOldVariant = $iBlockCode.'_'.$siteId;
		return isset(self::$iblockIds[$iBlockCodeOldVariant])
			? (string) self::$iblockIds[$iBlockCodeOldVariant]
			: (isset(self::$iblockIds[$iBlockCode])
				? (string) self::$iblockIds[$iBlockCode]
				: null
			)
		;
	}
}



