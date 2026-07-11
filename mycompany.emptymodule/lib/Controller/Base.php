<?php
/**
 * Базовый контроллер модуля.
 *
 * Все остальные контроллеры модуля наследуются от Base. Здесь:
 *  - дефолтный префильтр логирования (каждый action логирует старт/финиш);
 *  - общий префильтр проверки CSRF / прав (опционально, настраивается в потомках).
 *
 * Контроллеры Битрикса (D7, namespace \Bitrix\Main\Engine\Controller) уже
 * дают JSON-ответ, маршрутизацию, работу с action-ами. Зера мы добавляем
 * только то, что нужно именно нашему модулю.
 */

namespace Mycompany\EmptyModule\Controller;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Diag\Logger;
use Bitrix\Main\Error;

class Base extends Controller
{
    /**
     * Префильтр, выполняемый ДО каждого action.
     *
     * Если вернуть null — пропускаем, иначе ответ.
     */
    public function prepareAction()
    {
        $this->logRequest();

        return null;
    }

    /**
     * Постфильтр, выполняемый ПОСЛЕ action.
     *
     * Сигнатура совпадает с родительским классом Controller.
     *
     * @param Action $action
     * @param mixed $result
     */
    public function processAfterAction(Action $action, $result): void
    {
        $this->logResponse($action, $result);
    }

    /**
     * Лог: какой action какого контроллера вызван, с какими параметрами.
     */
    protected function logRequest(): void
    {
        $logger = Logger::create('mycompany.emptymodule');
        $logger->debug(sprintf(
            '[%s::%s] request: %s',
            static::class,
            $this->getAction() ?? '?',
            json_encode($this->getSourceParametersList(), JSON_UNESCAPED_UNICODE)
        ));
    }

    /**
     * Лог: результат (без полного тела, чтобы не раздувать).
     */
    protected function logResponse(Action $action, $result): void
    {
        $logger = Logger::create('mycompany.emptymodule');
        $errors = method_exists($this, 'getErrors') ? $this->getErrors() : [];

        $logger->debug(sprintf(
            '[%s::%s] response: %s, errors: %d',
            static::class,
            $this->getAction() ?? '?',
            is_scalar($result) ? (string) $result : gettype($result),
            count($errors)
        ));
    }

    /**
     * Удобный хелпер: вернуть стандартный JSON-ответ с ошибкой.
     *
     * @param string $message
     * @param string $code
     * @return array
     */
    protected function errorResponse(string $message, string $code = 'ERR'): array
    {
        $this->addError(new Error($message, $code));
        return ['success' => false, 'code' => $code];
    }
}
