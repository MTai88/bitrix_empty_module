<?php
/**
 * Реестр хендлеров модуля.
 *
 * Этот класс сохранён как namespace-точка группировки хендлеров.
 * Реальная регистрация обработчиков делается в install/index.php
 * через EventManager::registerEventHandler() — это правильный путь,
 * потому что регистрация "в коде" вызывается на каждом хите,
 * а registerEventHandler пишет в b_module_event и не требует загрузки модуля.
 *
 * Раньше здесь была логика динамической регистрации через OnPageStart
 * с массивом self::$handlers — это было антипаттерном, потому что
 * на каждом хите Битрикс вызывал init() и заново регистрировал
 * уже зарегистрированные в b_module_event хендлеры. Удалено.
 *
 * Если в проекте понадобится динамическая регистрация (например,
 * хендлеры зависят от конфигурации модуля) — используйте:
 *   EventManager::getInstance()->addEventHandlerCompatible(...)
 * в нужном хуке (не OnPageStart!).
 */

namespace Mycompany\EmptyModule\Handlers;

final class HandlerRegister
{
    /**
     * Зарезервировано для динамических хендлеров, зависящих от настроек.
     * Пример использования: в OnAfterInstallModule вызвать installConditionalHandlers().
     */
    public static function installConditionalHandlers(): void
    {
        // Здесь можно добавить хендлеры, которые должны регистрироваться
        // только при определённых опциях модуля. Пока заглушка.
    }

    public static function uninstallConditionalHandlers(): void
    {
        // Обратная операция.
    }
}
