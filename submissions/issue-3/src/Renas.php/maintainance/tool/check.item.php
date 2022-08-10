<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问

use meshal\xChar;

define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

$GLOBALS['debug']['debugMode'] = false;
$GLOBALS['debug']['log'] = false;

################################################
# 初始化结束
################################################
$db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

// $user->challengeRole('admin');

// 检查参数
if(!$_GET['discord']) {
    \fDie('discordId is required');
}

if(!$_GET['item']) {
    \fDie('item name is required');
}

$stock = 0;

//获取用户id

$query = $db->getArr(
    'user_discord',
    array(
        "`discordId` = '{$_GET['discord']}'"
    ),
    null,1
);

if($query === false) {
    \fDie('not registered');
}

$uid = $query[0]['uid'];

$u = new \user\xAdapter;
$u->load($uid);

$stock = $u->inventory->getStock($_GET['item']);

//检查角色
$query = $db->getArr(
    'characters',
    array(
        "`ownerId` = '{$uid}'"
    ),null
);

if($query != false) {
    $char = new \meshal\xChar;
    foreach($query as $k => $data) {
        $char->load($data['id']);

        $stock += $char->inventory->countEquipment(
            $_GET['item']
        );

        $stock += $char->inventory->countCarrying(
            $_GET['item']
        );
    }
}

\fDie("User(Discord {$_GET['discord']}) has items({$_GET['item']})<h1>{$stock}</h3>(warehouse stock & all character inventory included)");
?>
