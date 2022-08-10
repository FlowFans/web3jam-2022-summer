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
    if($_GET['confirm'] !== md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}adventure{$_GET['name']}")) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.adventure',
            'redirect.message.editor.adventure.failed'
        );
        \fDie();
    }

    //获取物品数据
    $data = \meshal\xAdventure::getData($_GET['name']);

    $stat = $db->delete( //删除数据库中的物品数据
        'encounters',
        array(
            "`name` = '{$_GET['name']}'"
        ),
        1
    );

    $db->delete( //删除与此遭遇对应的 adventureEntrance 语言数据
        'languages',
        array(
            "`name` = 'adventureEntrance.{$_GET['name']}'"
        )
    );
    
    $db->delete( //删除与此遭遇对应的 encounterApproach 语言数据
        'languages',
        array(
            "`name` = 'encounterApproach.{$_GET['name']}'"
        )
    );

    $db->delete( //删除与此遭遇对应的 encounterProcess 语言数据
        'languages',
        array(
            "`name` = 'encounterProcess.{$_GET['name']}'"
        )
    );

    $db->delete( //删除与此遭遇对应的 encounterSuccess 语言数据
        'languages',
        array(
            "`name` = 'encounterSuccess.{$_GET['name']}'"
        )
    );

    $db->delete( //删除与此遭遇对应的 encounterFailure 语言数据
        'languages',
        array(
            "`name` = 'encounterFailure.{$_GET['name']}'"
        )
    );

    if($stat === false) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.encounter',
            'redirect.message.editor.encounter.failed'
        );
        \fLog("Failed to delete encounter.{$_GET['name']}");
        \fDie();
    } else {
        $html->set('$encounterName', $_GET['name']);
        $html->redirect(
            'index.php',
            'pageTitle.editor.encounter',
            'redirect.message.editor.encounter.deleted'
        );
        \fLog("encounter.{$_GET['name']} was deleted");
        \fDie();
    }
}

elseif($_GET['name']) {
    $html->set('$encounterName', $_GET['name']);
    $html->set('$deleteUrl', "delete.php?&name={$_GET['name']}&confirm=".md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}encounter{$_GET['name']}"));

    $html->loadTpl('editor/encounter/body.delete.html');
    $html->output();
    \fDie();
} 

else {
    $html->redirect(
        'index.php',
        'pageTitle.editor.encounter',
        'redirect.message.editor.encounter.failed'
    );
    \fDie();
}

?>