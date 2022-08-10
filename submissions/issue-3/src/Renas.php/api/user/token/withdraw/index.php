<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

# 接受外部请求
\fCors();

################################################
# 初始化结束
################################################

/**
 * 请求格式
 * api/user/token/deposit?
 *  appId=1
 *  &token=b66cb67283c8d6e86d74a2fb88138223 
 *  &network=flow //此次上账的数据来源网络
 *  &contract=A.0x1234.CP //合约地址
 *  &address=0x123 //充值来源的钱包地址
 *  &amount=1234 //数量
 *  &tx=0xABCD //链上TxId
 */

# 处理用户的token充值
/**
 * API请求格式
 * 
 * @method GET
 * 
 * @param 'network'
 * 传递所在的网络，目前只支持flow
 * 
 * @param 'address'
 * 用户的钱包地址
 * 
 * @param 'contract'
 * 资产的合约地址
 * 
 * @param 'amount'
 * token的数量
 * 
 * @param 'tx'
 * 链上交易的transactionId
 * 
 * @return string (json_encoded)
 * array(
 *  'success' => bool //成功或失败
 *  'code' => int //状态码
 *  'data' => array( //code==0 或 100 时返回详情，否则为空数组
 *      'uid' => int|null //如果该地址未绑定过Renas的用户id，返回null，否则返回用户id
 *      'address' => string //用户的钱包地址
 *      'amount' => int //此次充值的数量
 *      'tx' => string //对应此次充值的TxId
 *  )
 * )
 */

/**
 * 状态码
 *  0：成功存入用户balance。
 *  1：appId或token错误，不是有效的请求来源
 *  2：该授权已被撤销
 *  3：$_SERVER['REMOTE_ADDR']和记录不一致
 *  4：缺少参数
 * 
 *  100：成功。但钱包地址未和用户绑定，存入Renas的待上账表。
 *  101：无效的合约或Renas不支持请求的网络
 *  102：Renas修改用户中心化balance时出错
 *  103：录入上账记录时出错
 */

// $db = new \xDatabase;
$cmd = \xAPI::listenGet('network', 'address', 'contract', 'amount', 'tx');

if(!isset($GLOBALS['deploy']['network'][$cmd['network']])) { //请求的网络是不支持的网络
    xAPI::respond(
        101,
        false
    );
}

//检查是否是有效的资产合约
if(
    $GLOBALS['deploy']['network'][$cmd['network']]['asset']['cp'] != $cmd['contract']) {
    \fLog("Error: invalid contract({$cmd['contract']})");
    xAPI::respond(101, false); //不是有效的合约资产
}

$u = new \user\xAdapter;
$uid = false;

switch ($cmd['network']) { //识别网络，并根据该网络中的address查找用户的uid
    case 'flow':
        $uid = \user\wallet\xFlow::getUidByAddress($cmd['address']);        
        break;
    default:
        break;
}

if($uid === false) { //该地址没有对应的用户uid，将交易存为无主账
    $entry = $db->insert(
        "balance_record_{$cmd['network']}",
        array(
            'uid' => null,
            'address' => $cmd['address'],
            'action' => 'deposit',
            'amount' => $cmd['amount'],
            'type' => $cmd['contract'],
            'sealed' => 1,
            'transactionId' => $cmd['tx'],
            'timestamp' => time()
        )
    );

    if($entry === false) { //上账失败
        \fLog("Error: failed to create new balance entry");
        xAPI::respond(103, false);
    }

    //成功，但该地址未绑定Renas中的用户，所以存入无主账
    xAPI::respond(
        100,
        true,
        array(
            'uid' => null,
            'address' => $cmd['address'],
            'amount' => $cmd['amount'],
            'tx' => $cmd['tx']
        )
    );
} else {
    $u->load($uid); //加载用户到适配器

    //添加一条有主的sealed上账记录
    $entry = $db->insert(
        "balance_record_{$cmd['network']}",
        array(
            'uid' => $uid,
            'address' => $cmd['address'],
            'action' => 'deposit',
            'amount' => $cmd['amount'],
            'type' => $cmd['contract'],
            'transactionId' => $cmd['tx'],
            'sealed' => 1,
            'timestamp' => time()
        )
    );
    
    if($entry === false) { //上账失败
        \fLog("Error: failed to create new balance entry");
        xAPI::respond(103, false);
    }

    //修改用户的balance
    $check = $u->modCP($cmd['amount']);
    if($check === false) { //更新用户balance时出错
        \fLog("Error: failed to modify user({$uid})'s balance");

        //把这条entry的uid修改为null，等于未上账
        $db->update(
            "balance_record_{$cmd['network']}",
            array(
                'uid' => null
            ),
            array(
                'id' => $entry
            ),
            1
        );

        xAPI::respond(102, false);
    }

    //给用户发消息
    \fMsg(
        $uid,
        'balance',
        'message.balance.depositCP',
        array(
            '$depositAmount' => $cmd['amount'],
            '$depositNetwork' => $cmd['network'],
            '$depositTxId' => $cmd['tx'],
            '$depositTime' => \fFormatTime(time())
        )
    );
        
    //返回成功
    xAPI::respond(
        0, 
        true,
        array(
            'uid' => $uid,
            'address' => $cmd['address'],
            'amount' => $cmd['amount'],
            'tx' => $cmd['tx']
        )
    );
}
?>