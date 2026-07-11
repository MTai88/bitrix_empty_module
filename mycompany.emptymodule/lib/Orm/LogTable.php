<?php
/**
 * ORM-таблица для лога действий модуля.
 *
 * Зачем: пример того, как в модуле создать свою таблицу. В DoInstall мы
 * НЕ создаём эту таблицу (нужно вручную или через миграцию). Это
 * просто пример, чтобы было от чего отталкиваться.
 *
 * Соответствующая install.sql:
 *   CREATE TABLE mycompany_emptymodule_log (
 *       ID int unsigned NOT NULL AUTO_INCREMENT,
 *       UF_TYPE varchar(50) NOT NULL,
 *       UF_MESSAGE text NOT NULL,
 *       UF_CONTEXT text NULL,
 *       UF_DATE_CREATE datetime NOT NULL,
 *       PRIMARY KEY (ID),
 *       KEY IX_TYPE_DATE (UF_TYPE, UF_DATE_CREATE)
 *   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 *
 * После создания таблицы — зарегистрировать через
 *   \Bitrix\Main\Entity\Base::getInstance('Mycompany\\EmptyModule\\Orm\\LogTable')->createDbTable();
 */

namespace Mycompany\EmptyModule\Orm;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

class LogTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'mycompany_emptymodule_log';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->autocomplete()
                ->primary(),

            (new StringField('TYPE', [
                'required' => true,
                'validation' => [new LengthValidator(null, 50)],
            ])),

            (new TextField('MESSAGE', [
                'required' => true,
            ])),

            (new TextField('CONTEXT', [
                'nullable' => true,
            ])),

            (new DatetimeField('DATE_CREATE', [
                'required' => true,
                'default_value' => static fn () => new DateTime(),
            ])),
        ];
    }

    /**
     * Удобный хелпер: добавить запись в лог.
     */
    public static function addEntry(string $type, string $message, ?array $context = null): \Bitrix\Main\Entity\AddResult
    {
        return static::add([
            'TYPE'        => $type,
            'MESSAGE'     => $message,
            'CONTEXT'     => $context !== null ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
            'DATE_CREATE' => new DateTime(),
        ]);
    }
}
