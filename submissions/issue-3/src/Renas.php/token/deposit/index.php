
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

# 渲染页面
$html->loadTpl('token/deposit/body.frame.html');

$html->set('$uid', $user->uid);
if(!$_GET['network']) {
    $_GET['network'] = $GLOBALS['deploy']['defaultNetwork'];
}
$html->set('$walletAddress', $user->wallet[$_GET['network']]->address);
$html->set('$uid', $user->uid);
$comp = array();
foreach ($GLOBALS['deploy']['network'] as $networkName => $cfg) {
    $comp[] = array(
        '--networkName' => $networkName,
        '--url' => $cfg['url']['deposit']
    );
}

$html->set(
    '$networks',
    $html->duplicate(
        'token/deposit/dup.network.html',
        $comp
    )
);

/**
 * 拼装记录筛选器
 */
$comp = array();
foreach ($GLOBALS['deploy']['network'] as $networkName => $cfg) {
    $active = ($_GET['network'] == $networkName) ? 'nzSelectionActive' : '';

    $comp[] = array(
        '--url' => '?network={?$networkName?}',
        '--networkName' => $networkName,
        '--active' => $active
    );
}

$html->set(
    '$networkMenu',
    $html->duplicate(
        'token/deposit/dup.filterNetwork.html',
        $comp
    )
);

/**
 * 整理历史记录
 */
$comp = array();

$txCount = $db->getCount(
    "balance_record_{$_GET['network']}",
    array(
        "`uid` = {$user->uid}",
        "`action` = 'deposit'"
    )
);

if($txCount == 0) {
    $html->set('$nonHistory', '{?common.none?}');
    $html->set('$history', '');
    $html->set('$displayHistory', 'hidden');
    $html->set('$pager', '');
} else {
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['default']['normal'];
    
    $queryTx = $db->getArr(
        "balance_record_{$_GET['network']}",
        array(
            "`uid` = {$user->uid}",
            "`action` = 'deposit'"
        ),
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['default']['normal']}",
        MYSQLI_NUM,
        'timestamp',
        'DESC'
    );

    #组装交易列表
    if($queryTx !== false) {
        $comp = array();
        foreach ($queryTx as $tx) {
            switch ($tx['status']) {
                case 'success':
                    $status = '{?common.deposit.success?}';
                    $statusCss = 'transaction-success';
                    break;

                case 'failure':
                    $status = '{?common.deposit.failure?}';
                    $statusCss = 'transaction-error';
                    break;
                
                default:
                    $status = '{?common.deposit.notSealed?}';
                    $statusCss = 'transaction-notSealed';
                    break;
            }

            $comp[] = array(
                '--time' => \fFormatTime($tx['timestamp']),
                '--txId' => is_null($tx['transactionId']) ? '{?common.deposit.toBeConfirmed?}' : $tx['transactionId'],
                '--amount' => $tx['amount'],
                '--networkName' => $_GET['network'],
                '--txUrl' => $GLOBALS['deploy']['network'][$_GET['network']]['browser']['transaction'],
                '--status' => $status,
                '--statusCss' => $statusCss
            );
        }

        $html->set(
            '$history',
            $html->duplicate(
                'token/deposit/dup.tx.html',
                $comp
            )
        );
        
        $html->set('$nonHistory', '');
        $html->set('$displayHistory', '');

        $html->set('$networkName', $_GET['network']);
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $txCount,
            $GLOBALS['setting']['pager']['default']['normal'],
            '?page={?$page?}&network={?$networkName?}&_back='.\fGet('_back')
        )
    );
}

$html->output();
fDie();
?>