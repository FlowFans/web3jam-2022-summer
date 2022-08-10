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

if($_GET['confirm'] && $_GET['name'] && $_GET['level']) {//有confirmCode和name+level，做删除
    //做confirmCode校验是否是合法请求
    if($_GET['confirm'] !== md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}facility{$_GET['name']}{$_GET['level']}")) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.facility',
            'redirect.message.editor.facility.failed'
        );
        \fDie();
    }

    //获取物品数据
    $data = \meshal\xFacility::getData($_GET['name'], $_GET['level']);

    $stat = $db->delete( //删除数据库中的设施数据
        'facilities',
        array(
            "`name` = '{$_GET['name']}'",
            "`level` = '{$_GET['level']}'"
        ),
        1
    );

    $db->delete( //删除与此物品对应的translation数据
        'languages',
        array(
            "`name` = 'facilityName.{$_GET['name']}'"
        )
    );

    $db->delete( //删除与此物品对应的description数据
        'languages',
        array(
            "`name` = 'facilityDesc.{$_GET['name']}'"
        )
    );

    if($stat === false) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.facility',
            'redirect.message.editor.facility.failed'
        );
        \fLog("Failed to delete facility.{$_GET['name']}({$_GET['level']})");
        \fDie();
    } else {
        $html->set('$facilityName', $_GET['name']);
        $html->set('$facilityLevel', $_GET['level']);
        $html->redirect(
            'index.php',
            'pageTitle.editor.facility',
            'redirect.message.editor.facility.deleted'
        );
        \fLog("facility.{$_GET['name']}({$_GET['level']}) was deleted");
        \fDie();
    }
}

elseif($_GET['name'] && $_GET['level']) {
    $html->set('$facilityName', $_GET['name']);
    $html->set('$facilityLevel', $_GET['level']);
    $html->set('$deleteUrl', "delete.php?&name={$_GET['name']}&level={$_GET['level']}&confirm=".md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}facility{$_GET['name']}{$_GET['level']}"));

    $html->loadTpl('editor/facility/body.delete.html');
    $html->output();
    \fDie();
} 

else {
    $html->redirect(
        'index.php',
        'pageTitle.editor.facility',
        'redirect.message.editor.facility.failed'
    );
    \fDie();
}

?>