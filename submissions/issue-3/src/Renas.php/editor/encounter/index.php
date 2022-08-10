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
$html->loadTpl('editor/encounter/body.frame.html', 'body');
$html->set('$search', \fGet('search', ''));

$user->challengeRole('admin', 'editor');

$where = array();

//做关键词筛选
$search = \fGet('search', '');
if($search != '') {
    $where[] = "`name` LIKE '%{$search}%'";
}

$encounterCount = $db->getCount(
    'encounters',
    $where
);

if($encounterCount > 0) {
    # 根据当前页取遭遇
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['editor']['encountersPerPage'];

    $queryEncounters = $db->getArr(
        'encounters',
        $where,
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['editor']['encountersPerPage']}",
        null,
        'name',
        'DESC',
        null,
        true
    );

    #组装遭遇列表
    if($queryEncounters !== false) {
        //拼装物品数据
        $comp = array();
        foreach ($queryEncounters as $cur) {
            $tmp = array();
            $curData = \meshal\adventure\xEncounter::getData($cur['name']);

            $comp[] = array(
                '--name' => $cur['name'],
                '--duration' => \fFormatTime($curData['duration'], 'hour'),
                '--intensity' => $curData['intensity'],
                '--loot' => implode("{?common.comma?}", $curData['loot'])
            );
        }

        $html->set(
            '$encounterList', 
            $html->duplicate(
                'editor/encounter/dup.row.html',
                $comp
            )
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $encounterCount,
            $GLOBALS['setting']['pager']['editor']['encountersPerPage'],
            '?page={?$page?}&type='
        )
    );
} else {
    $html->set('$encounterList', '');
    $html->set('$pager', '');
}

$html->output();
\fDie();



?>