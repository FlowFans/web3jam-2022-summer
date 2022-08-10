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

use \meshal\xItem as xItem;
// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('editor/item/body.frame.html', 'body');
$html->set('$search', \fGet('search', ''));
// $html->set('$type', \fGet('type', ''));

$user->challengeRole('admin', 'editor');

$where = array();

//做关键词筛选
$search = \fGet('search', '');
if($search != '') {
    $where[] = "`name` LIKE '%{$search}%'";
}

if(\fGet('category', '') != '') {
    $where[] = "`category` = '{$_GET['category']}'";
}

if(\fGet('type', '') != '') {
    $where[] = "`type` = '{$_GET['type']}'";
}

$itemCount = $db->getCount(
    'item_types',
    $where
);

if($itemCount > 0) {
    # 根据当前页取物品
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['editor']['itemsPerPage'];

    $queryItems = $db->getArr(
        'item_types',
        $where,
        '`name`',
        "{$rowStart},{$GLOBALS['setting']['pager']['editor']['itemsPerPage']}",
        null,
        'name',
        'DESC',
        null,
        true
    );

    #组装物品列表
    if($queryItems !== false) {
        //拼装物品数据
        $comp = array();
        foreach ($queryItems as $cur) {
            $curData = xItem::getData($cur['name']);

            $curType = array();
            foreach ($curData['type'] as $categoryName => $types) {
                foreach ($types as $k => $typeName) {
                    $curType[] = "{?itemType.{$categoryName}.{$typeName}?}";
                }
            }

            $comp[] = array(
                '--category' => $cur['category'],
                '--types' => implode('{?common.itemType.separator?}', $curType),
                '--name' => $cur['name'],
                '--icon' => $curData['iconFile'],
                '--equipStrength' => $curData['strength']['equip'],
                '--carryStrength' => $curData['strength']['carry']
            );
        }

        $html->set(
            '$itemList', 
            $html->duplicate(
                'editor/item/dup.row.html',
                $comp
            )
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $itemCount,
            $GLOBALS['setting']['pager']['editor']['itemsPerPage'],
            '?page={?$page?}&type='.$_GET['type']
        )
    );
} else {
    $html->set('$itemList', '');
    $html->set('$pager', '');
}

# 组装大类选择器
$categoryMenu = array();
foreach ($GLOBALS['meshal']['itemType'] as $categoryName => $settings) {
    $categoryMenu[] = array(
        // '--category' => $categoryName,
        '--category' => $categoryName == $_GET['category'] ? '' : $categoryName,
        '--active' => $categoryName == $_GET['category'] ? 'nzSelectionActive' : '',
        '--name' => "{?itemType.{$categoryName}?}"
    );
}

$html->set('$categoryMenu', $html->duplicate(
    'editor/item/dup.filterRow.category.html',
    $categoryMenu
));

#组装类型选择器
$typeMenu = array();
if(\fGet('category', '') !== '') {
    foreach ($GLOBALS['meshal']['itemType'][$_GET['category']] as $typeName => $settings) {
        $typeMenu[] = array(
            '--category' => $_GET['category'],
            '--type' => $typeName == $_GET['type'] ? '' : $typeName,
            '--active' => $typeName == $_GET['type'] ? 'nzSelectionActive' : '',
            '--name' => "{?itemType.{$_GET['category']}.{$typeName}?}"
        );
    }

    $html->set('$typeMenu', $html->duplicate(
        'editor/item/dup.filterRow.type.html',
        $typeMenu
    ));
} else {
    $html->set('$typeMenu', '');
}

$html->set('$category', $_GET['category']);
$html->set('$type', $_GET['type']);


$html->output();
\fDie();



?>