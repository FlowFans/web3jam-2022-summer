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

# 加载依赖
require_once 'function.upgrade.php';

# 加载专属配置
require_once _ROOT.DIR_CFG.'meshal.upgrade.php';

################################################
# 初始化结束
################################################

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;
$dice = new \meshal\xDice;
$char = new \meshal\xChar;

localCheck();

/**
 * 对应不同版本进行升级处理
 */
\fSaveLogToFile();
header("Location: "._ROOT."character/upgrade/{$char->version}/?id={$char->id}&_back=".$_GET['_back']);
\fDie();


?>