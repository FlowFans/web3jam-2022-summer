<?php
namespace transactor;

use xDatabase;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#Transaction support: Flow
################################################

class flow
{

    function __construct()
    {
        $this->network = 'flow';
        
        global $db;
        $this->db = $db;
        
        $this->config = $GLOBALS['deploy']['network'][$this->network];
    }

    /**
     * 创建一笔新的充值
     * @param int $uid
     * 接受充值的用户uid
     * 
     * @param string $asset
     * 资产代号
     * 
     * @param string|int|float $amount
     * 充值的金额
     * 
     * @param string $txId
     * 链上交易Id
     * 
     * @return int
     * 返回状态码
     * 0：正确执行
     * 1：写入数据库时发生错误
     * 2：txId为空
     */
    public function newDeposit(
        int $uid,
        string $asset,
        $amount,
        string $txId
    ) {
        if (is_null($txId)) {
            \fLog("Error: the given txId is null");
            return 2;
        }
        //插入一条未结账的记录
        $newId = $this->db->insert(
            'balance_record_'.$this->network,
            array(
                'uid' => $uid,
                'address' => null,
                'action' => 'deposit',
                'amount' => $amount,
                'type' => $this->config['asset'][$asset],
                'timestamp' => time(),
                'transactionId' => $txId,
                'status' => null
            )
        );

        if($newId === false) return 1;
        return 0;
    }

    /**
     * 创建一笔新的提现
     * @param int $uid
     * 发起提现的用户uid
     * 
     * @param string $toAddress
     * 接收提现的钱包地址
     * 
     * @param string $asset
     * 资产代号
     * 
     * @param string|int|float $amount
     * 提现的金额
     * 
     * @return int
     * 返回状态码
     * 0：正确执行
     * 1：远程接口返回错误
     */
    public function newWithdrawal(
        int $uid,
        string $toAddress,
        string $asset,
        $amount
    ) {
        $timestamp = time();
        //插入一条未结账的记录
        $newId = $this->db->insert(
            'balance_record_'.$this->network,
            array(
                'uid' => $uid,
                'address' => $toAddress,
                'action' => 'withdraw',
                'amount' => $amount,
                'type' => $this->config['asset'][$asset],
                'timestamp' => $timestamp,
                'status' => null
            )
        );

        //调用远程接口
        $json = json_encode(array(
            'timestamp' => $timestamp,
            'identifier' => $this->config['asset']['cp.identifier'], //资产vault
            'recieverPath' => $this->config['asset']['cp.recieverPath'], //接收者资产vault
            'id' => $newId, //记账id
            'amount' => floatval($amount), //提现数量
            'reciever' => $toAddress //提现目标地址
        ));

        $verify = md5($json.$this->config['serviceSalt']); //将json字串和盐值加密作为校验码

        \fLog("The params for calling remote API:");
        \fLog(\fDump($json), 1);

        $encoded = json_encode(array( //请求内容用json格式传raw data
            'data' => base64_encode($json),
            'verify' => $verify
        ));

        \fLog("The encoded raw data:");
        \fLog(\fDump($encoded), 1);

        $response = json_decode(\fCallAPI( //请求远程接口
            'POST',
            $this->config['API']['withdraw'],
            $encoded
        ), true);

        \fLog("Recieved response:");
        \fLog(\fDump($response), 1);

        if(
            $response['error'] //如果response有错误
            || empty($response) //如果response结果为空
            || is_null($response['trxId']) //response返回的txId为空
        ) { 
            \fLog("Error: remote server returns an error.");
            $this->db->delete( //将之前创建的记账数据删除
                'balance_record_'.$this->network,
                array(
                    "`id` = '{$newId}'"
                ),
                1
            );
            return 1;
        };

        //将返回的txId更新到记账信息
        $this->db->update(
            'balance_record_'.$this->network,
            array(
                "transactionId" => $response['trxId']
            ),
            array(
                "`id` = '{$newId}'"
            ),
            1
        );

        return 0;
    }

    /**
     * 调取远程接口获取交易结果
     * 
     * @param string $transactionId
     * 交易hash
     * 
     * @return array
     */
    public function fetchTransactionResult (
        string $transactionId
    ) {
        return json_decode(\fCallAPI( //请求远程接口
            'GET',
            $this->config['API']['transactionResult'].$transactionId
        ), true);
    }

    /**
     * 获取本地的记账
     * 
     * @param int $id
     * 本地记录的记账id
     * 
     * @return array|bool
     * 没有获取到记账则返回false
     */
    public function getLocalTransaction (
        int $id
    ) {
        $query = $this->db->getArr(
            'balance_record_'.$this->network,
            array(
                "`id` = '{$id}'"
            ),
            null,
            1
        );

        if($query === false) return false;
        return $query[0];
    }

    /**
     * 修改本地记账的状态
     * 
     * @param int $id
     * 记账id
     * 
     * @param string $status
     * 状态
     */
    public function changeLocalTransactionStatus (
        int $id,
        string $status
    ) {
        $this->db->update(
            'balance_record_'.$this->network,
            array(
                'status' => $status
            ),
            array(
                "`id` = '{$id}'"
            ),
            1
        );
    }
}
?>