<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################
$html = new \xHtml;
$user = new \xUser;
// $adventure = new \meshal\xAdventure;

$user->challengeRole('admin');

$rName = $_GET['name']; //templateName
$data = \meshal\adventure\xEncounter::getData($_GET['name'])['data'];

$arr = array();
$arr[] = "adventureEntrance.{$rName}";
$arr[] = "encounterApproach.{$rName}";
$arr[] = "encounterProcess.{$rName}";

if(
    !empty($data['checkAny']) || !empty($data['checkAll'])
) {
    $arr[] = "encounterSuccess.{$rName}";
    $arr[] = "encounterFailure.{$rName}";       
}

foreach ($arr as $k => $langCode) {
    $lang = $html->dbLang($langCode);


    \fEcho('<div class="nzContainer widthFill">');
    \fEcho('<input type="text" class="widthFill bgWhite3 fontsizel" readonly value="'.$langCode.'">');
    if(is_null($lang)) {
        \fEcho('<input type="text" class="widthFill bgYellow1" readonly value="not defined">');
    } else {
        \fEcho("<textarea class=\"widthFill\" style=\"height: 128px;\">{$lang}</textarea>");
    }

    \fEcho('</div>');
}


$html->loadTpl('body.debug.html');
$html->set('$debugInfo', fDump($data));
$html->output();

fDie();
//test

?>
