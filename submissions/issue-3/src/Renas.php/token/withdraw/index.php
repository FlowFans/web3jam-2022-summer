
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

$error = 0;
if($_POST['submit']) { //有提交
    //检查参数是否有填写
    if(!$_POST['withdrawAddress']) {
        \fNotify(
            'notify.withdraw.addressRequired',
            'warn'
        );
        $error ++;
    }

    if(\fPost('withdrawAmount', 0) <= 0) {
        \fNotify(
            'notify.withdraw.amountError',
            'warn'
        );
        $error ++;
    }

    //检查地址合法性
    $validator = new $GLOBALS['deploy']['network'][$_POST['network']]['class']['addressValidator'];
    if($validator->isAddress($_POST['withdrawAddress']) == false) {
        \fNotify(
            'notify.withdraw.addressError',
            'warn'
        );
        $error ++;
    }

    //检查用户是否有足够的cp
    if(bccomp($user->cp, floatval($_POST['withdrawAmount'])) == -1) {
        \fNotify(
            'notify.withdraw.insufficientCP',
            'warn'
        );
        $error ++;
    }

    # 如果所有检查都通过，那么开始处理提现请求
    if($error == 0) {
        //进行记账
        $transactor = new $GLOBALS['deploy']['network'][$_POST['network']]['class']['transactor'];
        $check = $transactor->newWithdrawal(
            $user->uid,
            $_POST['withdrawAddress'],
            'cp',
            $_POST['withdrawAmount']
        );

        if($check != 0) { //有错误，不扣除且不上账
            $html->redirect(
                '',
                'pageTitle.token.withdraw',
                'redirect.message.token.withdrawError'
            );
            \fDie();
        } else { //没有错误，扣除用户cp
            $user::subCP($user->uid, $_POST['withdrawAmount']);
            $html->set('$networkName', $_POST['network']);
            $html->redirect(
                _ROOT.'token/withdraw/',
                'pageTitle.token.withdraw',
                'redirect.message.token.withdrawSuccess'
            );
            \fDie();
        }
    }
}

# 渲染页面
$html->loadTpl('token/withdraw/body.frame.html');

$html->set('$withdrawMax', $user->cp);
if(!$_GET['network']) {
    $_GET['network'] = $GLOBALS['deploy']['defaultNetwork'];
}
$html->set('$walletAddress', $user->wallet[$_GET['network']]->address);

$comp = array();
foreach ($GLOBALS['deploy']['network'] as $networkName => $cfg) {
    $comp[] = array(
        '--networkName' => $networkName,
        '--checked' => $GLOBALS['deploy']['defaultNetwork'] == $networkName ? 'checked' : ''
    );
}

$html->set(
    '$networks',
    $html->duplicate(
        'token/withdraw/dup.network.html',
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
        'token/withdraw/dup.filterNetwork.html',
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
        "`action` = 'withdraw'"
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
            "`action` = 'withdraw'"
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
                    $status = '{?common.withdraw.success?}';
                    $statusCss = 'transaction-success';
                    break;

                case 'failure':
                    $status = '{?common.withdraw.failure?}';
                    $statusCss = 'transaction-error';
                    break;
                
                default:
                    $status = '{?common.withdraw.notSealed?}';
                    $statusCss = 'transaction-notSealed';
                    break;
            }

            $comp[] = array(
                '--time' => \fFormatTime($tx['timestamp']),
                '--txId' => is_null($tx['transactionId']) ? '{?common.withdraw.toBeConfirmed?}' : $tx['transactionId'],
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
                'token/withdraw/dup.tx.html',
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