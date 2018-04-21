<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use MsNatali\BitrixDebug\DebugVar;

if (!DebugVar::get()->isDebug()) {
    return;
}

$totalVarCount = count(DebugVar::get()->getVars());

echo '<div class="bx-component-debug bx-debug-summary" id="bx-component-debug-var">';
echo 'Отладка данных<br>';
echo '<a title="Посмотреть подробную информацию по данным" href="javascript:BX_DEBUG_VAR_INFO.Show(); BX_DEBUG_VAR_INFO.ShowDetails(\'BX_DEBUG_VAR_INFO_1\');">'.'Всего данных: '."</a> ".intval($totalVarCount)."<br>";
echo '</div><div class="empty"></div>';

//CJSPopup
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
?>
<script type="text/javascript">
    function filterTableVar(input, table_id, column_num)
    {
        var table = BX(table_id);
        for (var i = 0; i < table.rows.length; i++)
        {
            var sql = table.rows[i].cells[column_num].innerHTML;
            if (input.value.length > 0 && sql.indexOf(input.value) == -1)
                table.rows[i].style.display = 'none';
            else
                table.rows[i].style.display = 'block';
        }
    }

    // Ставим суммарную инфу по высоте так, чтобы не накладывалось на останые
    document.addEventListener("DOMContentLoaded", function(){
        var maxTop = 0;
        var elements = document.getElementsByClassName('bx-component-debug');
        for (var i = 0; i < elements.length; i++)
        {
            if (elements[i].style.display!="none" && elements[i].getAttribute('id') != 'bx-component-debug-var') {
                var top = Number(elements[i].style.bottom.replace("px", ""));
                if (maxTop < top) {
                    maxTop = top;
                }
            }
        }

        if (!maxTop) {
            maxTop = 10;
        } else {
            maxTop += 60;
        }
        document.getElementById('bx-component-debug-var').style.bottom = maxTop + 'px';
    });
    BX_DEBUG_VAR_INFO = new BX.CDebugDialog();
</script>
<?
$obJSPopup = new CJSPopupOnPage('', []);
$obJSPopup->jsPopup = 'BX_DEBUG_VAR_INFO';
$obJSPopup->StartDescription('bx-core-debug-var-info');
?>
<p>Всего данных: <?= $totalVarCount ?></p>
<p>Поиск: <input type="text" style="height:16px" onkeydown="filterTableVar(this, 'bx-debug-var', 1)" onpaste="filterTableVar(this, 'bx-debug-var', 1)" oninput="filterTableVar(this, 'bx-debug-var', 1)"></p>
<?$obJSPopup->StartContent(['buffer' => true])?>
<?if($totalVarCount):?>
    <div class="bx-debug-content bx-debug-content-table">
        <table id="bx-debug-var" cellpadding="0" cellspacing="0" border="0">
            <?$j = 1?>
            <?foreach(DebugVar::get()->getVars() as $trace => $var):?>
                <tr>
                    <td class="number" valign="top"><?= $j ?></td>
                    <td><?= $var['name']?> (<?= count($var['vars'])?>) <a href="javascript:BX_DEBUG_VAR_INFO.ShowDetails('BX_DEBUG_VAR_INFO_<?= $j ?>')"><?= $var['trace']?> </a></td>
                </tr>
                <?$j++?>
            <?endforeach;?>
        </table>
    </div>

    #DIVIDER#

    <div class="bx-debug-content bx-debug-content-details">
        <?$j = 1?>
        <?foreach(DebugVar::get()->getVars() as $trace => $var):?>
            <div id="BX_DEBUG_VAR_INFO_<?= $j ?>" style="display:none">
                <b>Данные № <?= $j ?>:</b>
                <?foreach($var['vars'] as $_var):?>
                    <pre><?var_dump($_var)?></pre>
                <?endforeach;?>
            </div>
            <?$j++?>
        <?endforeach;?>
    </div>
<?endif?>
<?
$obJSPopup->StartButtons();
$obJSPopup->ShowStandardButtons(['close']);
?>
