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

$db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('editor/adventure/body.frame.html', 'body');
$html->set('$search', \fGet('search', ''));

$user->challengeRole('admin', 'editor');

$where = array();

//做关键词筛选
$search = \fGet('search', '');
if($search != '') {
    $where[] = "`name` LIKE '%{$search}%'";
}

$adventureCount = $db->getCount(
    'adventures',
    $where
);

if($adventureCount > 0) {
    # 根据当前页取冒险
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['editor']['adventuresPerPage'];

    $queryAdventures = $db->getArr(
        'adventures',
        $where,
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['editor']['adventuresPerPage']}",
        null,
        'name',
        'DESC',
        null,
        true
    );

    #组装冒险列表
    if($queryAdventures !== false) {
        //拼装冒险数据
        $comp = array();
        foreach ($queryAdventures as $cur) {
            $tmp = array();
            $curData = \meshal\xAdventure::getData($cur['name']);

            $comp[] = array(
                '--name' => $cur['name'],
                '--duration' => \fFormatTime($curData['duration'], 'hour'),
                '--strengthMin' => is_null($curData['strengthMin']) ? 0 : $curData['strengthMin'],
                '--strengthMax' => is_null($curData['strengthMax']) ? 0 : $curData['strengthMax'],
                '--teamMin' => is_null($curData['teamMin']) ? 0 : $curData['teamMin'],
                '--teamMax' => is_null($curData['teamMax']) ? 0 : $curData['teamMax'],
                '--apCost' => is_null($curData['apCost']) ? 0 : $curData['apCost'],
                '--loot' => implode("{?common.comma?}", $curData['loot'])
            );
        }

        $html->set(
            '$adventureList', 
            $html->duplicate(
                'editor/adventure/dup.row.html',
                $comp
            )
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $adventureCount,
            $GLOBALS['setting']['pager']['editor']['adventuresPerPage'],
            '?page={?$page?}&type='
        )
    );
} else {
    $html->set('$adventureList', '');
    $html->set('$pager', '');
}

$html->output();
\fDie();



?>