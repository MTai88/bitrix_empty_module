<?php
/**
 * Шаг 1 удаления модуля. Подключается в DoUninstall() при step < 2.
 *
 * Спрашиваем, что делать с данными: сохранить ли опции и удалить ли таблицы.
 */
?>

<form action="<?= $APPLICATION->GetCurPage() ?>" method="get">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="mycompany.emptymodule">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">

    <p>
        Удаление модуля <strong>Mycompany EmptyModule</strong>.
    </p>

    <p>
        <label>
            <input type="checkbox" name="saved" value="Y" checked>
            Сохранить настройки модуля (опции) — пригодится при переустановке
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="tables" value="Y">
            Удалить таблицы модуля из БД (если создавали)
        </label>
    </p>

    <input type="submit" value="Удалить" class="adm-btn-delete">
</form>
