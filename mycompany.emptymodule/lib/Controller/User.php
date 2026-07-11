<?php
/**
 * Пример контроллера для работы с пользователем.
 *
 * Endpoints:
 *  - GET /api/v1/user.getInfo  — данные о текущем залогиненном пользователе
 *  - GET /api/v1/user.find?login=admin  — поиск по логину (только для админов)
 */

namespace Mycompany\EmptyModule\Controller;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;

class User extends Base
{
    public function getInfoAction(): ?array
    {
        $user = CurrentUser::get();

        if (!$user || !$user->getId()) {
            $this->addError(new Error('Требуется авторизация', 'AUTH_REQUIRED'));
            return null;
        }

        return [
            'id'       => (int) $user->getId(),
            'login'    => $user->getLogin(),
            'name'     => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email'    => $user->getEmail(),
            'isAdmin'  => $user->isAdmin(),
        ];
    }

    public function findAction(string $login = ''): ?array
    {
        if ($login === '') {
            $this->addError(new Error('Не передан login', 'EMPTY_LOGIN'));
            return null;
        }

        global $USER;
        if (!is_object($USER) || !$USER->IsAdmin()) {
            $this->addError(new Error('Недостаточно прав', 'ACCESS_DENIED'));
            return null;
        }

        $userId = \CUser::GetByLogin($login)->Fetch();
        if (!$userId) {
            $this->addError(new Error('Пользователь не найден', 'NOT_FOUND'));
            return null;
        }

        return [
            'id'    => (int) $userId['ID'],
            'login' => $userId['LOGIN'],
            'name'  => trim(($userId['NAME'] ?? '') . ' ' . ($userId['LAST_NAME'] ?? '')),
            'email' => $userId['EMAIL'] ?? '',
        ];
    }
}
