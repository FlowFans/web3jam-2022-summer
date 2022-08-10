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

// use \meshal\xFeature as xFeature;
// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

//只允许特定用户组访问
$user->challengeRole('admin');

if($_GET['confirm'] && $_GET['lang'] && $_GET['name']) {//有confirmCode、lang和name，做删除
    //做confirmCode校验是否是合法请求
    if($_GET['confirm'] !== md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}{$_GET['lang']}{$_GET['name']}")) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.language',
            'redirect.message.editor.language.deleteFailed'
        );
        \fDie();
    }

    $stat = $db->delete(
        'languages',
        array(
            "`lang` = '{$_GET['lang']}'",
            "`name` = '{$_GET['name']}'"
        ),
        1
    );

    if($stat === false) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.language',
            'redirect.message.editor.language.deleteFailed'
        );
        \fLog("Failed to delete {$_GET['lang']}.{$_GET['name']}");
        \fDie();
    } else {
        $html->set('$langCode', $_GET['lang']);
        $html->set('$langName', $_GET['name']);
        $html->redirect(
            'index.php',
            'pageTitle.editor.language',
            'redirect.message.editor.language.deleted'
        );
        \fLog("{$_GET['lang']}.{$_GET['name']} was deleted");
        \fDie();
    }
}

elseif($_GET['lang'] && $_GET['name']) {
    $html->set('$langCode', $_GET['lang']);
    $html->set('$langName', $_GET['name']);
    $html->set('$deleteUrl', "delete.php?lang={$_GET['lang']}&name={$_GET['name']}&confirm=".md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}{$_GET['lang']}{$_GET['name']}"));

    $html->loadTpl('editor/language/body.delete.html');
    $html->output();
    \fDie();
} 

else {
    $html->redirect(
        'index.php',
        'pageTitle.editor.language',
        'redirect.message.editor.language.deleteFailed'
    );
    \fDie();
}

?>