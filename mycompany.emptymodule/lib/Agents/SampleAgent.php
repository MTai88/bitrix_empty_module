<?php
/**
 * Пример агента.
 *
 * Агент — это статический метод, который Битрикс периодически вызывает
 * через /bitrix/tools/cron_events.php (или хит публичной части, если
 * используется агент-фолбэк).
 *
 * Зарегистрирован в install/index.php:InstallAgents().
 *
 * Конвенции:
 *  - Метод должен быть static.
 *  - Имя метода — run() (Битрикс вызывает Class::run()).
 *  - Возвращаемое имя функции/метода (= строка для повторной регистрации)
 *    определяет, будет ли агент периодическим. Если вернуть пустую строку —
 *    агент выполнится один раз и удалится.
 */

namespace Mycompany\EmptyModule\Agents;

use Bitrix\Main\Diag\Logger;
use Bitrix\Main\Type\DateTime;

class SampleAgent
{
    /**
     * Точка входа. Запускается Битриксом по расписанию.
     *
     * Возвращаем то же самое имя — агент станет периодическим
     * (интервал задаётся в \CAgent::AddAgent 4-м параметром, в секундах).
     */
    public static function run(): string
    {
        $logger = Logger::create('mycompany.emptymodule');

        try {
            $logger->info('SampleAgent::run tick', [
                'time' => (new DateTime())->toString(),
            ]);

            // Здесь делаем полезную работу: чистим логи, синхронизируемся с
            // внешним сервисом, прогреваем кеш и т.п.

        } catch (\Throwable $e) {
            $logger->error('SampleAgent failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return __METHOD__ . '();';
    }
}
