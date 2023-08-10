<?php

namespace Mycompany\EmptyModule\Handlers\Iblock;

use Mycompany\EmptyModule\Tools\Helper\Ids;

class News
{
    public static function afterAdd($fields): bool
    {
        if($fields['IBLOCK_ID'] != Ids::getIblockId('news') || empty($fields['ID'])) {
            return true;
        }

        \Bitrix\Main\Diag\Debug::dump($fields, "added"); exit();

        return true;
    }

    public static function afterUpdate($fields): bool
    {
        if($fields['IBLOCK_ID'] != Ids::getIblockId('news') || empty($fields['ID'])) {
            return true;
        }

        \Bitrix\Main\Diag\Debug::dump($fields, "updated"); exit();

        return true;
    }
}