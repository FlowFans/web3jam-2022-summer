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

// var_export($GLOBALS);

################################################
# 初始化结束
################################################

// $db = new \xDatabase;
$html = new \xHtml;
$dice = new meshal\xDice;
$user = new \xUser;
// $char = new meshal\xChar;

$user->challengeRole('admin');

$data = \meshal\xFeature::getData($_GET['type'], $_GET['name']);

$html->loadTpl('body.debug.html');
$html->set('$debugInfo', fDump($data));
$html->output();

fDie();
//test

?>
