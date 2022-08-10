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
 * api/user/token/balance?
 *  appId=1
 *  &token=b66cb67283c8d6e86d74a2fb88138223 
 *  &network=flow //此次上账的数据来源网络
 *  &address=0x123 //充值来源的钱包地址
 */

# 查询用户在renas中的token余额
/**
 * API请求格式
 * 根据给出的network和address查找用户，返回的是该用户的CP余额和该地址在该链中的未入账金额
 * 
 * @method GET
 * 
 * @param 'network'
 * 传递所在的网络，目前只支持flow
 * 
 * @param 'address'
 * 传递的钱包地址
 * 
 * @return string (json_encoded)
 * array(
 *  'success' => bool //成功或失败
 *  'code' => int //状态码
 *  'data' => array( //code==0 或 100 时返回详情，否则为空数组
 *      'uid' => int|null //如果该地址未绑定过Renas的用户id，返回null，否则返回用户id
 *      'address' => string //返回该钱包地址
 *      'amount' => int //在用户账号内的数量
 *      'uncredited' => int //在该地址记录下，但未划转到用户名下的balance
 *      'unsealed' => int //在Renas中，对应该地址的链上未确认数量
 *  )
 * )
 */

/**
 * 状态码
 *  0：查询成功。
 *  1：appId或token错误，不是有效的请求来源
 *  2：该授权已被撤销
 *  3：$_SERVER['REMOTE_ADDR']和记录不一致
 *  4：缺少参数
 * 
 *  100：成功。但钱包地址未和用户绑定
 *  101：Renas不支持请求的网络
 */

// $db = new \xDatabase;
$cmd = \xAPI::listenGet();
if(!isset($GLOBALS['deploy']['network'][$cmd['network']])) { //请求的网络是不支持的网络
    xAPI::respond(
        101,
        false
    );
}

//查无归属的总额
$uncreditedDeposited = $db->getSum(
    "balance_record_{$cmd['network']}",
    'amount',
    array(
        "`address` = '{$cmd['address']}'",
        "`action` = 'deposit'",
        "`uid` is null"
    )
);
$uncreditedWithdrawal = $db->getSum(
    "balance_record_{$cmd['network']}",
    'amount',
    array(
        "`address` = '{$cmd['address']}'",
        "`action` = 'withdraw'",
        "`uid` is null"
    )
);
$uncredited = $uncreditedDeposited - $uncreditedWithdrawal;

//查链上未确认的总额
$unsealedDepoisted = $db->getSum(
    "balance_record_{$cmd['network']}",
    'amount',
    array(
        "`address` = '{$cmd['address']}'",
        "`action` = 'deposit'",
        "`sealed` = '0'"
    )
);
$unsealedWithdrawal = $db->getSum(
    "balance_record_{$cmd['network']}",
    'amount',
    array(
        "`address` = '{$cmd['address']}'",
        "`action` = 'withdraw'",
        "`sealed` = '0'"
    )
);
$unsealed = $unsealedDepoisted - $unsealedWithdrawal;


$u = new \user\xAdapter;
$uid = false;
$return = array(
    'address' => $cmd['address'],
    'uncredited' => $uncredited,
    'unsealed' => $unsealed
);

switch ($cmd['network']) { //识别网络，并根据该网络中的address查找用户的uid
    case 'flow':
        $uid = \user\wallet\xFlow::getUidByAddress($cmd['address']);        
        break;
    default:
        break;
}

if($uid === false) { //该地址没有对应的用户uid
    $return['uid'] = null;
    $return['amount'] = 0;

    //成功，但该地址未绑定Renas中的用户，所以存入待上账
    xAPI::respond(
        100,
        true,
        $return
    );
} else {
    $u = new \user\xAdapter;
    $u->load($uid);
    $return['uid'] = $uid;
    $return['amount'] = $u->cp;

    //返回成功
    xAPI::respond(
        0, 
        true,
        $return
    );
}
?>