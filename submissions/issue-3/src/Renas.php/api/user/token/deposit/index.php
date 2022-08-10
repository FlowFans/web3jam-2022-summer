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
 * 
 * Raw data
 *  uid=1 //充值用户id
 *  network=flow //此次上账的数据来源网络
 *  amount=1234 //数量
 *  trxId=0xABCD //链上TxId
 */

# 处理用户的token充值
/**
 * API请求格式
 * 
 * @method POST
 * 
 * @param 'uid'
 * 用户id
 * 
 * @param 'network'
 * 传递所在的网络，目前只支持flow
 * 
 * @param 'amount'
 * token的数量
 * 
 * @param 'trxId'
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
 *      'trxId' => string //对应此次充值的TxId
 *  )
 * )
 */

/**
 * 状态码
 *  0：成功存入用户balance。
 *  1：appId错误，不是有效的请求来源
 *  2：该授权已被撤销
 *  3：$_SERVER['REMOTE_ADDR']和记录不一致
 *  4：缺少参数
 *  5：token错误
 * 
 *  100：成功。但钱包地址未和用户绑定，存入Renas的待上账表。
 *  101：无效的合约或Renas不支持请求的网络
 *  102：Renas修改用户中心化balance时出错
 *  103：录入上账记录时出错
 */

// $db = new \xDatabase;

\fLog(\fDump($_SERVER));

\xAPI::checkAuth(); //这个接口需要检查权限
$cmd = \xAPI::listenRaw(); //获取rawdata

if(!isset($GLOBALS['deploy']['network'][$cmd['network']])) { //请求的网络是不支持的网络
    xAPI::respond(
        101,
        false
    );
}

//进行记账
$transactor = new $GLOBALS['deploy']['network'][$cmd['network']]['class']['transactor'];
$check = $transactor->newDeposit(
    $cmd['uid'],
    'cp',
    $cmd['amount'],
    $cmd['trxId']
);

if($check == 1) xAPI::respond(103, false);

//给用户发消息
\fMsg(
    $cmd['uid'],
    'balance',
    'message.balance.depositingCP',
    array(
        '$depositAmount' => $cmd['amount'],
        '$depositNetwork' => $cmd['network'],
        '$depositTxId' => $cmd['trxId'],
        '$depositTime' => \fFormatTime(time())
    )
);

xAPI::respond(0, true, $cmd);
?>