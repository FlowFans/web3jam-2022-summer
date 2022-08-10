<?php
ignore_user_abort(true);
set_time_limit(0);
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

# 执行这个脚本时不启动automator
define('_NOTAUTOMATOR', true);

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################

/**
 * @method GET
 * 
 * @param int id
 * 记账id
 * 
 * @param string network
 * 交易所在的网络
 */

// $db = new xDatabase;

if(!$_GET['id']) {
    \fLog("Error: no id given");
    xAPI::respond('4', false);
}

if(!$_GET['network']) {
    \fLog("Error: no network given");
    xAPI::respond('4', false);
}

if(!class_exists($GLOBALS['deploy']['network'][$_GET['network']]['class']['transactor'])) {
    \fLog("Error: invalid network");
    xAPI::respond('105', false);
}

\fLog('Creating new $transactor by using class '.$GLOBALS['deploy']['network'][$_GET['network']]['class']['transactor']);
$transactor = new $GLOBALS['deploy']['network'][$_GET['network']]['class']['transactor'];
$localTransaction = $transactor->getLocalTransaction($_GET['id']); //取本地的记账
\fLog(\fDump($localTransaction));

if($localTransaction === false) {
    \fLog("Error: no transaction entry found");
    xAPI::respond('104', false);
}

if(is_null($localTransaction['transactionId'])) {
    \fLog("Error: transaction has no id");
    xAPI::respond('103', false);
}

$response = $transactor->fetchTransactionResult($localTransaction['transactionId']); //取链上的交易数据
\fLog(\fDump($response));


/* 根据返回结果进行处理 */
if($response['status'] == 'Sealed') {
    if($response['execution'] == 'Success') {
        
        if($localTransaction['action'] == 'deposit') { //如果充值成功，给用户加cp
            \fLog("${$localTransaction['amount']} CP is added to user({$localTransaction['uid']})");
            xUser::addCP($localTransaction['uid'], $localTransaction['amount']);
            
            \fMsg(
                $localTransaction['uid'],
                'balance',
                'message.balance.deposit.confirmed',
                array(
                    '$depositAmount' => $localTransaction['amount'],
                    '$networkName' => $_GET['network'],
                    '$txId' => $localTransaction['transactionId']
                )
            );
        }

        $transactor->changeLocalTransactionStatus($_GET['id'], 'success');
        xAPI::respond(
            '0', true,
            array(
                'id' => $_GET['id'],
                'status' => 'success'
            )
        );
    }

    if($response['execution'] == 'Failure') {
        $transactor->changeLocalTransactionStatus($_GET['id'], 'failure');

        if($localTransaction['action'] == 'withdraw') { //如果是提现失败，那么把CP退还给用户
            \fLog("The transaction failed, {$localTransaction['amount']} is refunded to user({$localTransaction['uid']})");
            \xUser::addCP($localTransaction['uid'], $localTransaction['amount']);

            \fMsg( //给用户发退款消息
                $localTransaction['uid'],
                'balance',
                'message.balance.withdraw.refund',
                array(
                    '$refundAmount' => $localTransaction['amount'],
                    '$networkName' => $_GET['network'],
                    '$withdrawAddress' => $localTransaction['address'],
                    '$txId' => $localTransaction['transactionId']
                )
            );
        }
        
        xAPI::respond(
            '2', true,
            array(
                'id' => $_GET['id'],
                'status' => 'failure'
            )
        );
    }
} else {
    $db->update( //更新记录并更新最后检查的时间戳
        'balance_record_'.$_GET['network'],
        array(
            'status' => null,
            'lastCheck' => time()
        ),
        array(
            "`id` = '{$_GET['id']}'"
        ),
        1
    );
    xAPI::respond(
        '1', true,
        array(
            'id' => $_GET['id'],
            'status' => null
        )
    );
}
?>