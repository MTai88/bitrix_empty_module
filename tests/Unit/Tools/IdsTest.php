<?php
/**
 * Пример unit-теста для хелпера Ids.
 *
 * Запуск:
 *   vendor/bin/phpunit tests/Unit
 *
 * Требует подключённого ядра Битрикса (см. tests/bootstrap.php).
 */

declare(strict_types=1);

namespace Mycompany\EmptyModule\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;
use Mycompany\EmptyModule\Tools\Helper\Ids;

class IdsTest extends TestCase
{
    public function testGetIblockIdReturnsStringForKnownCode(): void
    {
        $id = Ids::getIblockId('non_existent_iblock_' . uniqid());
        $this->assertNull($id, 'Для несуществующего кода метод должен вернуть null');
    }

    public function testGetIblockIdCachesResult(): void
    {
        // Первый вызов — populate cache.
        Ids::getIblockId('any_code');

        // Внутреннее состояние уже должно быть заполнено.
        $reflection = new \ReflectionClass(Ids::class);
        $prop = $reflection->getProperty('iblockIds');
        $prop->setAccessible(true);

        $this->assertIsArray($prop->getValue());
    }
}
