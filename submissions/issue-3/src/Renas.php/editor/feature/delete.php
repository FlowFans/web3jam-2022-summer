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

if($_GET['confirm'] && $_GET['type'] && $_GET['name']) {//有confirmCode、type和name，做删除
    //做confirmCode校验是否是合法请求
    if($_GET['confirm'] !== md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}{$_GET['type']}{$_GET['name']}")) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.feature',
            'redirect.message.editor.feature.failed'
        );
        \fDie();
    }

    //获取特征数据
    $data = \meshal\xFeature::getData($_GET['type'], $_GET['name']);

    $stat = $db->delete( //删除数据库中的特征数据
        'features',
        array(
            "`type` = '{$_GET['type']}'",
            "`name` = '{$_GET['name']}'"
        ),
        1
    );

    if($stat !== false) {
        //从feature_index表中更新权重
        $weight = $db->getArr(
            'feature_index',
            array(
                "`name` = '{$_GET['type']}'"
            ),
            null,
            1
        );
        $db->update(
            'feature_index',
            array(
                'strength' => $weight[0]['strength'] - abs($data['strength']),
                'probabilityModifier' => $weight[0]['probabilityModifier'] - $data['probabilityModifier'],
                'count' => $weight[0]['count'] - 1
            ),
            array(
                "`name` = '{$_GET['type']}'"
            ),
            1
        );
    }

    $db->delete( //删除与此特征对应的translation数据
        'languages',
        array(
            "`name` = 'featureName.{$_GET['type']}.{$_GET['name']}'"
        )
    );

    $db->delete( //删除与此特征对应的description数据
        'languages',
        array(
            "`name` = 'featureDesc.{$_GET['type']}.{$_GET['name']}'"
        )
    );

    if($stat === false) {
        $html->redirect(
            'index.php',
            'pageTitle.editor.feature',
            'redirect.message.editor.feature.failed'
        );
        \fLog("Failed to delete {$_GET['type']}.{$_GET['name']}");
        \fDie();
    } else {
        $html->set('$featureType', $_GET['type']);
        $html->set('$featureName', $_GET['name']);
        $html->redirect(
            'index.php',
            'pageTitle.editor.feature',
            'redirect.message.editor.feature.deleted'
        );
        \fLog("{$_GET['type']}.{$_GET['name']} was deleted");
        \fDie();
    }
}

elseif($_GET['type'] && $_GET['name']) {
    $html->set('$featureType', $_GET['type']);
    $html->set('$featureName', $_GET['name']);
    $html->set('$deleteUrl', "delete.php?type={$_GET['type']}&name={$_GET['name']}&confirm=".md5("{$user->uid}delete{$GLOBALS['deploy']['securityKey']}{$_GET['type']}{$_GET['name']}"));

    $html->loadTpl('editor/feature/body.delete.html');
    $html->output();
    \fDie();
} 

else {
    $html->redirect(
        'index.php',
        'pageTitle.editor.feature',
        'redirect.message.editor.feature.failed'
    );
    \fDie();
}

?>