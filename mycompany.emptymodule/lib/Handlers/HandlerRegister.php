<?php

namespace Mycompany\EmptyModule\Handlers;

use Mycompany\EmptyModule\Handlers\Iblock\News;
final class HandlerRegister
{
    static private array $handlers = [
        //['module' => 'iblock', 'event' => 'OnAfterIblockElementAdd', 'toClass' => News::class, 'toMethod' => 'afterAdd'],
        //['module' => 'iblock', 'event' => 'OnAfterIBlockElementUpdate', 'toClass' => News::class, 'toMethod' => 'afterUpdate'],
    ];

    public static function init(): void
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        foreach (self::$handlers as $handler){
            $eventManager->addEventHandler(
                $handler['module'],
                $handler['event'],
                [$handler['toClass'], $handler['toMethod']]
            );
        }
    }
}