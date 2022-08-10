<?php
################################################
# 初始化开始
################################################

# 常量 _EXPOSURE 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################
$GLOBALS['debug']['log'] = FALSE; //在本脚本中，临时关闭debug日志记录，以避免产生无意义的数据库查询记录。

$html = new \xHtml;
$user = new \xUser;

//只允许特定用户组访问
$user->challengeRole('admin');

$html->loadTpl('maintainance/serverLogs/body.frame.html');

$rowsPerPage = \fGet('rows', 1); //每页显示数量
$con = array();
if(\fGet('uid','') != '') {
    $con[] = "`uid` = '{$_GET['uid']}'";
}

$logCount = $db->getCount(
    'logs',
    $con,
    'id'
);

if($logCount == 0) {
    $html->set('$logList', '');
}

# 根据当前页取log
$rowStart = (\fGet('page', 1) - 1) * $rowsPerPage;

$queryLogs = $db->getArr(
    'logs',
    $con,
    NULL,
    "{$rowStart},{$rowsPerPage}",
    MYSQLI_NUM,
    'id',
    'DESC'
);

# 组装log列表
if($queryLogs !== false) {
    $fill = array();
    foreach ($queryLogs as $l) {
        $fill[] = array(
            '$timestamp' => date('Y-m-d H:i:s', $l['timestamp']),
            '$log' => \fDecode($l['content']),
            '$uid' => $l['uid']
        );
    }
    $html->set(
        '$logList',
        $html->duplicate(
            'maintainance/serverLogs/dup.row.html',
            $fill
        )
    );
}

//组装翻页器
$html->set(
    '$pager',
    $html->pager(
        \fGet('page', 1),
        $logCount,
        $rowsPerPage,
        'serverLogs.php?page={?$page?}&uid='.\fGet('uid')
    )
);

$html->output();
?>