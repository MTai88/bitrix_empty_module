<?php
/**
 * Шаг 1 установки модуля. Подключается в DoInstall() при step < 2.
 *
 * Минимальный шаблон: выводит сообщение и кнопку "Далее →".
 * Реальный проект здесь может показать лицензионное соглашение,
 * запросить обязательные поля (api_key, secret и т.д.).
 */
?>

<form action="<?= $APPLICATION->GetCurPage() ?>" method="get">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="mycompany.emptymodule">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">

    <p>
        Будет установлен модуль <strong>Mycompany EmptyModule</strong>:
    </p>
    <ul>
        <li>Зарегистрированы обработчики событий (iblock, main);</li>
        <li>Добавлен агент-пример <code>SampleAgent::run()</code>;</li>
        <li>Созданы опции модуля (api_url, api_key, mode, enable_log, log_level);</li>
        <li>Скопированы компоненты/админ-страницы (если есть в install/).</li>
    </ul>

    <input type="submit" value="Установить" class="adm-btn-save">
</form>
