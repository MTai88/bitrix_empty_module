<?php
/**
 * Обработчик событий инфоблока (пример).
 *
 * Зарегистрирован в install/index.php для событий
 *   - iblock:OnAfterIblockElementAdd
 *   - iblock:OnAfterIblockElementUpdate
 *
 * Использует Helpers\Logger и Helpers\Ids (для проверки, что событие
 * относится к одному из "наших" инфоблоков).
 */

namespace Mycompany\EmptyModule\Handlers\Iblock;

use Mycompany\EmptyModule\Helpers\Ids;
use Mycompany\EmptyModule\Helpers\Logger;

class News
{
    /**
     * Символьные коды инфоблоков, на которые мы реагируем. Под проект — поменять.
     */
    private const WATCHED_IBLOCK_CODES = ['news', 'articles'];

    public static function afterAdd(array $fields): bool
    {
        if (!self::isOurIblock((int) ($fields['IBLOCK_ID'] ?? 0))) {
            return true;
        }
        if (empty($fields['ID'])) {
            return true;
        }

        Logger::info('iblock element added', [
            'id'       => (int) $fields['ID'],
            'iblock'   => (int) $fields['IBLOCK_ID'],
            'section'  => (int) ($fields['IBLOCK_SECTION_ID'] ?? 0),
            'modified_by' => (int) ($fields['MODIFIED_BY'] ?? 0),
        ]);

        return true;
    }

    public static function afterUpdate(array $fields): bool
    {
        if (!self::isOurIblock((int) ($fields['IBLOCK_ID'] ?? 0))) {
            return true;
        }
        if (empty($fields['ID'])) {
            return true;
        }

        Logger::info('iblock element updated', [
            'id'       => (int) $fields['ID'],
            'iblock'   => (int) $fields['IBLOCK_ID'],
            'active'   => ($fields['ACTIVE'] ?? 'Y') === 'Y',
        ]);

        return true;
    }

    private static function isOurIblock(int $iblockId): bool
    {
        if ($iblockId <= 0) {
            return false;
        }

        foreach (self::WATCHED_IBLOCK_CODES as $code) {
            if ((int) Ids::getIblockId($code) === $iblockId) {
                return true;
            }
        }
        return false;
    }
}
