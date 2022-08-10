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

$GLOBALS['debug']['log'] = FALSE; //在本脚本中，临时关闭debug日志记录，以避免产生无意义的数据库查询记录。

################################################
# 初始化结束
################################################

/** 
 * GET方法传入参数
 * 
 * token
 * 通过这个选择器给什么物品赋予类型，token对应的是item_stage中的数据记录
 * 
 * category
 * 是哪个父类下的物品类型
 * 通常物品类型都有一个父类，比如刀剑和盾牌的父类都属于武器
 * 
 * type
 * 为给物品赋予类型的名字
 * 
 * action
 * 操作参数，须和type参数一同使用
 * - 添加特征则action=add
 * - 移除特征则action=remove
 */


// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/embeded.css');
$html->loadCss('css/meshal.css');
$html->loadTpl('editor/item/dup.typeSelector.html', 'body');

$user->challengeRole('admin', 'editor');

//从item_stage读取记录
//检查是否有token
if(!$_GET['token']) {
    \fLog("Error: no token is given");
//还需要设计一个内容为空的页面
    \fDie();
}

$stage = $db->getArr(
    'item_stage',
    array(
        "`stageToken` = '{$_GET['token']}'"
    ),
    null,
    1
);
if($stage === false) {
    \fLog("Error: no feature stage data fetched");
//还需要设计一个内容为空的页面
    \fDie();
} else {
    $stage = $stage[0];
}

//如果有提交add/remove，做添加/移除操作
if($_GET['action']) {
    switch (true) {
        case (
            $_GET['action']=='add'
            && $_GET['category']
            && $_GET['type']
        ):        
            $stageData = json_decode($stage['type'], true);
            $stageData[$_GET['category']][] = $_GET['type'];

            $db->update(
                'item_stage',
                array(
                    'type' => json_encode($stageData)
                ),
                array(
                    "`stageToken` = '{$_GET['token']}'"
                ),
                1
            );
            break;
        
        case (
            $_GET['action']=='remove'
            && $_GET['category']
            && $_GET['type']
        ):
            $stageData = json_decode($stage['type'], true);

            if(array_search($_GET['type'], $stageData[$_GET['category']]) !== false) {
                unset($stageData[$_GET['category']][array_search($_GET['type'], $stageData[$_GET['category']])]);
                if(empty($stageData[$_GET['category']])) { //清理空的父级容器
                    unset($stageData[$_GET['category']]);
                }
            }
            $db->update(
                'item_stage',
                array(
                    'type' => json_encode($stageData)
                ),
                array(
                    "`stageToken` = '{$_GET['token']}'"
                ),
                1
            );
            break;

        default:
            \fLog("Error: invalid action submitted: action={$_GET['action']}, category={$_GET['category']}, type={$_GET['type']}");
            break;
    }
}

/**
 * 以下代码则是用于显示选择器
 */
//重新获取更新后的数据
$stage = $db->getArr(
    'item_stage',
    array(
        "`stageToken` = '{$_GET['token']}'"
    ),
    null,
    1
);
if($stage === false) {
    \fLog("Error: no item stage data fetched");
//还需要设计一个内容为空的页面
    \fDie();
} else {
    $stage = $stage[0];
}


//拼装物品类型数据
$comp = array();
$stageData = json_decode($stage['type'], true);
foreach ($GLOBALS['meshal']['itemType'] as $categoryCode => $types) {
    foreach ($types as $typeCode => $settings) {
        $tmp = array();
        $tmp['--itemCategory'] = $categoryCode;
        $tmp['--itemType'] = $typeCode;
        $tmp['--typeName'] = "{?itemType.{$categoryCode}.{$typeCode}?}";

        if(!is_null($stageData)) {
            if(
                $stageData[$categoryCode]
                && array_search($typeCode, $stageData[$categoryCode]) !== false) {
                //给已经选中的特征做选中标记
                $tmp['--selected'] = 'nzSelectionActive';
                $tmp['--url'] = "?action=remove&category={$categoryCode}&type={$typeCode}&token={$_GET['token']}";
            } else {
                $tmp['--selected'] = '';
                $tmp['--url'] = "?action=add&category={$categoryCode}&type={$typeCode}&token={$_GET['token']}";
            }
        } else {
            $tmp['--selected'] = '';
            $tmp['--url'] = "?action=add&category={$categoryCode}&type={$typeCode}&token={$_GET['token']}";
        }
        $comp[] = $tmp;
    }
}

$html->set(
    '$itemTypeList', 
    $html->duplicate(
        'editor/item/dup.typeSelector.option.html',
        $comp
    )
);

$html->output('embed');
\fDie();



?>