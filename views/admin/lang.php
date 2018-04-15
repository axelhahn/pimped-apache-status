<?php
if (!isset($adminindex)){
    die("Abort.");
}
$aTmp = $aLangTxt; // backup
$aAllLang = array();
$sTh = "    <th>key</th>\n";
foreach (explode(",", $aCfg['selectLang']) as $s) {
    $aLangTxt=array();
    $sTh.="    <th>$s</th>\n";
    require("./../lang/" . $s . ".php");
    foreach ($aLangTxt as $key => $val) {
        $aAllLang[$key][$s] = $val;
    }
}
$aLangTxt = $aTmp;

$myvar = "aLang";
$sHtml='
        <h3 id="h3' . md5($myvar) . '">' . $aLangTxt["lblDumpsaLang"] . '</h3>
        <div class="subh3">
            <div class="hintbox">' . $aLangTxt["lblHintDumpsaLang"] . '</div>
            <table id="table' . $myvar . '">
            <thead><tr>' . $sTh . '</tr></thead>
            <tbody>';

foreach ($aAllLang as $key => $aLang) {
    $sHtml.="<tr>\n   <td>$key</td>\n";
    foreach (explode(",", $aCfg['selectLang']) as $sLang) {
        if (!array_key_exists($sLang, $aAllLang[$key]) || strlen($aAllLang[$key][$sLang])<1){
            $sTd = $aLangTxt['lblDumpsMiss'];
            $sCssClass = 'miss';
        } else {
            $sTd = htmlentities($aAllLang[$key][$sLang]);
            $sCssClass = 'ok';
        }
        $sHtml.="    <td class=\"$sCssClass\">$sTd</td>\n";
    }
    $sHtml.="</tr>\n";
}

$sHtml.='
            </tbody></table>
        </div>';

print_r($aCfg['datatableOptions']); die();
$sJsOnReady.='$("#table' . $myvar . '").dataTable(' . json_encode($aCfg['datatableOptions']) . ');';

$aTab[$myvar] = array(
    'url' => '#',
    'label' => "$" . $myvar,
    'onclick' => 'return showTab(\'#h3' . md5($myvar) . '\');',
);

echo $sHtml;