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

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

//只允许特定用户组访问
$user->challengeRole('admin');

if($_GET['confirm'] && $_GET['name']) {//有confirmCode和name，做删除
    //做confirmCode校验是否是合法请求
    if($_GET['confirm'] !== md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}item{$_GET['name']}")) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.item',
            'redirect.message.editor.item.failed'
        );
        \fDie();
    }

    //获取物品数据
    $data = \meshal\xItem::getData($_GET['name']);

    $stat = $db->delete( //删除数据库中的物品数据
        'items',
        array(
            "`name` = '{$_GET['name']}'"
        ),
        1
    );

    $db->delete( //删除与此物品对应的translation数据
        'languages',
        array(
            "`name` = 'itemName.{$_GET['name']}'"
        )
    );

    $db->delete( //删除与此物品对应的description数据
        'languages',
        array(
            "`name` = 'itemDesc.{$_GET['name']}'"
        )
    );

    $db->delete( //删除此物品的type数据
        'item_types',
        array(
            "`name` = '{$_GET['name']}'"
        )
    );

    if($stat === false) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.item',
            'redirect.message.editor.item.failed'
        );
        \fLog("Failed to delete item.{$_GET['name']}");
        \fDie();
    } else {
        $html->set('$itemName', $_GET['name']);
        $html->redirect(
            'index.php',
            'pageTitle.editor.item',
            'redirect.message.editor.item.deleted'
        );
        \fLog("item.{$_GET['name']} was deleted");
        \fDie();
    }
}

elseif($_GET['name']) {
    $html->set('$itemName', $_GET['name']);
    $html->set('$deleteUrl', "delete.php?&name={$_GET['name']}&confirm=".md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}item{$_GET['name']}"));

    $html->loadTpl('editor/item/body.delete.html');
    $html->output();
    \fDie();
} 

else {
    $html->redirect(
        'index.php',
        'pageTitle.editor.item',
        'redirect.message.editor.item.failed'
    );
    \fDie();
}

?>