<?php


namespace MsNatali\BitrixDebug;


use CUtil;

class DebugVarHandler
{
    /**
     * Отображение панели
     * @throws \Exception
     */
    public static function showPanel()
    {
        if (!DebugVar::get()->isDebug()) {
            return;
        }

        $bExcel = isset($_REQUEST["mode"]) && $_REQUEST["mode"] === 'excel';
        if (defined("ADMIN_AJAX_MODE") || defined('PUBLIC_AJAX_MODE') || $bExcel) {
            return;
        }

        require_once(__DIR__ . '/include/debug_var_info.php');
    }

    /**
     * Установить куки, исходя из запроса
     * @throws \Exception
     */
    public static function setCookie()
    {
        global $USER;
        $key = DebugVar::get()->getCookieName();

        if (!$USER->IsAdmin() || !isset($_GET[$key])) {
            return;
        }

        if ($_GET[$key] === 'Y') {
            setcookie($key, 'Y', time() + 60 * 60 * 24, '/'); // включаем на сутки
            $_COOKIE[$key] = 'Y';
        } else {
            setcookie($key, "", time() - 3600); // удаляем
            unset($_COOKIE[$key]);
        }
    }

    /**
     * Добавить кнопку работы с фронтендом
     * @throws \Exception
     */
    function addDebugVarButtonToTopPanel()
    {
        global $APPLICATION, $USER;
        if (!$USER->IsAdmin()) {
            return;
        }

        $isDebugVar = DebugVar::get()->isDebug();
        foreach ($APPLICATION->arPanelButtons as $key => $panelButton) {
            if ($panelButton['HK_ID'] == 'top_panel_debug') {
                $APPLICATION->AddPanelButtonMenu($key, [
                    "TEXT" => "Отладка данных",
                    "TITLE" => "",
                    "CHECKED" => $isDebugVar,
                    "ACTION" => "jsUtils.Redirect([], '" . CUtil::addslashes($APPLICATION->GetCurPageParam(DebugVar::get()->getCookieName() . "=" . ($isDebugVar ? "N" : "Y"), [DebugVar::get()->getCookieName()])) . "');",
                    "DEFAULT" => false,
                    "HK_ID" => "top_panel_debug_var",
                ]);
                break;
            }
        }
    }
}