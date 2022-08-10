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
$adventure = new \meshal\xAdventure;

$user->challengeRole('admin');

$rName = $_GET['name']; //templateName
$tpl = $adventure->getData($rName);

$arr = array();
$def = array();
$arr[] = "adventureName.{$rName}";
$arr[] = "adventureDesc.{$rName}";
$arr[] = "adventureProlog.{$rName}";
foreach($tpl['data']['scenes'] as $sceneId => $data) {
    $arr[] = "adventureEntrance.{$rName}.{$data['encounter']}";
    $arr[] = "encounterApproach.{$rName}.{$data['encounter']}";
    $arr[] = "encounterProcess.{$rName}.{$data['encounter']}";
    
    $def["adventureEntrance.{$data['encounter']}"] = true;
    $def["encounterApproach.{$data['encounter']}"] = true;
    $def["encounterProcess.{$data['encounter']}"] = true;

    $encounter = \meshal\adventure\xEncounter::getData($data['encounter']);
    if(
        !empty($encounter['data']['checkAny']) || !empty($encounter['data']['checkAll'])
    ) {
        $arr[] = "encounterSuccess.{$rName}.{$encounter['name']}";
        $def["encounterSuccess.{$encounter['name']}"] = true;
        $arr[] = "encounterFailure.{$rName}.{$encounter['name']}";
        $def["encounterFailure.{$encounter['name']}"] = true;
    }
}
$arr[] = "adventureEpilog.{$rName}";


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

\fEcho('<hr><h3>Default desc</h3>');
foreach ($def as $langCode => $v) {
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
$html->set('$debugInfo', fDump($tpl));
$html->output();

fDie();
//test

?>
