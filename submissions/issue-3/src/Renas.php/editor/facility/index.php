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

$html->loadCss('css/meshal.css');
$html->loadTpl('editor/facility/body.frame.html', 'body');
$html->set('$search', \fGet('search', ''));

$user->challengeRole('admin', 'editor');

$where = array();

//做关键词筛选
$search = \fGet('search', '');
if($search != '') {
    $where[] = "`name` LIKE '%{$search}%'";
}

// if(\fGet('category', '') != '') {
//     $where[] = "`category` = '{$_GET['category']}'";
// }

// if(\fGet('type', '') != '') {
//     $where[] = "`type` = '{$_GET['type']}'";
// }

$facilityCount = $db->getCount(
    'facilities',
    $where
);

# 根据当前页取设施
$rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['editor']['facilitiesPerPage'];

$query = $db->getArr(
    'facilities',
    $where,
    null,
    "{$rowStart},{$GLOBALS['setting']['pager']['editor']['facilitiesPerPage']}",
    null,
    'level',
    'DESC',
    null
);
// fPrint($query);
if($query !== false) {
    $comp = array();
    foreach($query as $cur) {
        $comp[] = array(
            '--name' => $cur['name'],
            '--level' => $cur['level']
        );
    }

    $html->set(
        '$facilityList', 
        $html->duplicate(
            'editor/facility/dup.row.html',
            $comp
        )
    );
} else {
    $html->set('$facilityList', '');
    $html->set('$pager', '');
}

//组装翻页器
$html->set(
    '$pager',
    $html->pager(
        \fGet('page', 1),
        $facilityCount,
        $GLOBALS['setting']['pager']['editor']['facilitiesPerPage'],
        '?page={?$page?}&search='.\fGet('search', '')
    )
);

$html->output();
\fDie();



?>