<?php
/**
 * Страница настроек модуля (/bitrix/admin/settings.php?mid=mycompany.emptymodule).
 *
 * Содержит 2 таба:
 *  - Основные: api_url, api_key, mode
 *  - Отладка: enable_log, log_level
 *
 * Опции хранятся в b_option через \COption::Set/GetOptionString.
 */

$module_id = 'mycompany.emptymodule';

$RIGHT_R = $RIGHT_W = $USER->IsAdmin();
if (!$RIGHT_R) {
    return;
}

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

CModule::IncludeModule($module_id);

$arAllOptions = [
    'edit1' => [
        ['api_url', GetMessage('MT_OPTIONS_API_URL'), ['text', '60'], GetMessage('MT_OPTIONS_API_URL_HINT')],
        ['api_key', GetMessage('MT_OPTIONS_API_KEY'), ['text', '40'], GetMessage('MT_OPTIONS_API_KEY_HINT')],
        [
            'mode',
            GetMessage('MT_OPTIONS_MODE'),
            ['selectbox', [
                'prod'  => GetMessage('MT_OPTIONS_MODE_PROD'),
                'stage' => GetMessage('MT_OPTIONS_MODE_STAGE'),
                'dev'   => GetMessage('MT_OPTIONS_MODE_DEV'),
            ]],
            '',
        ],
    ],
    'edit2' => [
        ['enable_log', GetMessage('MT_OPTIONS_ENABLE_LOG'), ['checkbox'], GetMessage('MT_OPTIONS_ENABLE_LOG_HINT')],
        [
            'log_level',
            GetMessage('MT_OPTIONS_LOG_LEVEL'),
            ['selectbox', [
                'DEBUG' => GetMessage('MT_OPTIONS_LOG_LEVEL_DEBUG'),
                'INFO'  => GetMessage('MT_OPTIONS_LOG_LEVEL_INFO'),
                'WARN'  => GetMessage('MT_OPTIONS_LOG_LEVEL_WARN'),
                'ERROR' => GetMessage('MT_OPTIONS_LOG_LEVEL_ERROR'),
            ]],
            '',
        ],
    ],
];

$aTabs = [
    [
        'DIV'   => 'edit1',
        'TAB'   => GetMessage('MT_OPTIONS_TAB_MAIN'),
        'ICON'  => 'biconnector_settings',
        'TITLE' => GetMessage('MT_OPTIONS_TAB_MAIN_TITLE'),
    ],
    [
        'DIV'   => 'edit2',
        'TAB'   => GetMessage('MT_OPTIONS_TAB_DEBUG'),
        'ICON'  => 'biconnector_settings',
        'TITLE' => GetMessage('MT_OPTIONS_TAB_DEBUG_TITLE'),
    ],
];

$tabControl = new CAdminTabControl('tabControl_' . $module_id, $aTabs);

if (
    $REQUEST_METHOD === 'POST'
    && ($Update . $Apply . $RestoreDefaults) !== ''
    && $RIGHT_W
    && check_bitrix_sessid()
) {
    if ($RestoreDefaults !== '') {
        COption::RemoveOption($module_id);
    } else {
        foreach ($arAllOptions as $tabOptions) {
            foreach ($tabOptions as $arOption) {
                $name = $arOption[0];
                $val  = trim((string) $_REQUEST[$name], " \t\n\r");

                if ($arOption[2][0] === 'checkbox' && $val !== 'Y') {
                    $val = 'N';
                }

                COption::SetOptionString($module_id, $name, $val, $arOption[1]);
            }
        }
    }

    if ($_REQUEST['back_url_settings'] !== '') {
        if (($Apply !== '') || ($RestoreDefaults !== '')) {
            LocalRedirect(
                $APPLICATION->GetCurPage()
                . '?mid=' . urlencode($module_id)
                . '&lang=' . urlencode(LANGUAGE_ID)
                . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings'])
                . '&' . $tabControl->ActiveTabParam()
            );
        } else {
            LocalRedirect($_REQUEST['back_url_settings']);
        }
    } else {
        LocalRedirect(
            $APPLICATION->GetCurPage()
            . '?mid=' . urlencode($module_id)
            . '&lang=' . urlencode(LANGUAGE_ID)
            . '&' . $tabControl->ActiveTabParam()
        );
    }
}
?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id) ?>&amp;lang=<?= LANGUAGE_ID ?>">
    <?php
    $tabControl->Begin();

    foreach ($arAllOptions as $tabDiv => $tabOptions) {
        $tabControl->BeginNextTab();

        foreach ($tabOptions as $arOption) {
            $name = $arOption[0];
            $val  = COption::GetOptionString($module_id, $name);
            $type = $arOption[2];
            $hint = $arOption[3] ?? '';
            ?>
            <tr>
                <td width="40%" nowrap <?= ($type[0] === 'textarea') ? 'class="adm-detail-valign-top"' : '' ?>>
                    <label for="<?= htmlspecialcharsbx($name) ?>">
                        <?= $arOption[1] ?>:
                    </label>
                </td>
                <td width="60%">
                    <?php if ($type[0] === 'checkbox'): ?>
                        <input type="checkbox" name="<?= htmlspecialcharsbx($name) ?>"
                               id="<?= htmlspecialcharsbx($name) ?>" value="Y"
                            <?= ($val === 'Y') ? ' checked' : '' ?>>
                    <?php elseif ($type[0] === 'text'): ?>
                        <input type="text" size="<?= (int) $type[1] ?>" maxlength="255"
                               value="<?= htmlspecialcharsbx($val) ?>"
                               name="<?= htmlspecialcharsbx($name) ?>"
                               id="<?= htmlspecialcharsbx($name) ?>">
                    <?php elseif ($type[0] === 'textarea'): ?>
                        <textarea rows="<?= (int) $type[1] ?>" cols="<?= (int) $type[2] ?>"
                                  name="<?= htmlspecialcharsbx($name) ?>"
                                  id="<?= htmlspecialcharsbx($name) ?>"><?= htmlspecialcharsbx($val) ?></textarea>
                    <?php elseif ($type[0] === 'selectbox'): ?>
                        <select name="<?= htmlspecialcharsbx($name) ?>" id="<?= htmlspecialcharsbx($name) ?>">
                            <?php foreach ($type[1] as $key => $value): ?>
                                <option value="<?= htmlspecialcharsbx($key) ?>"
                                    <?= ((string) $val === (string) $key) ? ' selected' : '' ?>>
                                    <?= htmlspecialcharsbx($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <?php if ($hint !== ''): ?>
                        <div class="adm-info-message-wrap" style="margin-top: 6px;">
                            <div class="adm-info-message"><?= $hint ?></div>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } // foreach $tabOptions ?>
    <?php } // foreach $arAllOptions ?>

    <?php
    $tabControl->Buttons();
    ?>
    <input <?= (!$RIGHT_W) ? 'disabled' : '' ?> type="submit" name="Update" value="<?= GetMessage('MAIN_SAVE') ?>"
           title="<?= GetMessage('MAIN_OPT_SAVE_TITLE') ?>" class="adm-btn-save">
    <input <?= (!$RIGHT_W) ? 'disabled' : '' ?> type="submit" name="Apply" value="<?= GetMessage('MAIN_OPT_APPLY') ?>"
           title="<?= GetMessage('MAIN_OPT_APPLY_TITLE') ?>">
    <?php if ($_REQUEST['back_url_settings'] !== ''): ?>
        <input <?= (!$RIGHT_W) ? 'disabled' : '' ?> type="button" name="Cancel"
               value="<?= GetMessage('MAIN_OPT_CANCEL') ?>"
               title="<?= GetMessage('MAIN_OPT_CANCEL_TITLE') ?>"
               onclick="window.location='<?= htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings'])) ?>'">
        <input type="hidden" name="back_url_settings" value="<?= htmlspecialcharsbx($_REQUEST['back_url_settings']) ?>">
    <?php endif; ?>
    <input <?= (!$RIGHT_W) ? 'disabled' : '' ?> type="submit" name="RestoreDefaults"
           title="<?= GetMessage('MAIN_HINT_RESTORE_DEFAULTS') ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING')) ?>')"
           value="<?= GetMessage('MAIN_RESTORE_DEFAULTS') ?>">
    <?= bitrix_sessid_post(); ?>
    <?php $tabControl->End(); ?>
</form>
