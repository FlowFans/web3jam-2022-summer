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

use \meshal\xFeature as xFeature;
// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('editor/feature/body.frame.html', 'body');
$html->set('$search', \fGet('search', ''));
$html->set('$type', \fGet('type', ''));

$user->challengeRole('admin', 'editor');


$select = array();

//做关键词筛选
$search = \fGet('search', '');
if($search != '') {
    $select[] = "`name` LIKE '%{$search}%'";
}

if($_GET['type']) {
    $select[] = "`type` = '{$_GET['type']}'";
}

$featureCount = $db->getCount(
    'features',
    $select
);

if($featureCount > 0) {
    # 根据当前页取特征
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['editor']['featuresPerPage'];

    $queryFeatures = $db->getArr(
        'features',
        $select,
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['editor']['featuresPerPage']}",
        null,
        'lastUpdate',
        'DESC'
    );

    #组装特征列表
    if($queryFeatures !== false) {
        //拼装特征数据
        $comp = array();
        foreach ($queryFeatures as $cur) {
            $comp[] = array(
                '--type' => $cur['type'],
                '--name' => $cur['name'],
                '--strength' => $cur['strength'],
                '--probabilityModifier' => $cur['probabilityModifier']
            );
        }

        $html->set(
            '$featureList', 
            $html->duplicate(
                'editor/feature/dup.row.html',
                $comp
            )
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $featureCount,
            $GLOBALS['setting']['pager']['editor']['featuresPerPage'],
            '?page={?$page?}&type='.$_GET['type']
        )
    );
} else {
    $html->set('$featureList', '');
    $html->set('$pager', '');
}

# 组装特征选择器
$typeMenu = array();
foreach ($GLOBALS['meshal']['featureType'] as $typeName => $settings) {
    $typeMenu[] = array(
        // '--type' => $typeName,
        '--url' => $typeName == $_GET['type'] ? '?type=' : "?type={$typeName}",
        '--active' => $typeName == $_GET['type'] ? 'nzSelectionActive' : '',
        '--name' => "{?{$settings['name']}?}"
    );
}
$html->set('$typeMenu', $html->duplicate(
    'editor/feature/dup.filterRow.html',
    $typeMenu
));

$html->output();
\fDie();



?>