<?php
namespace user;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供用户信息的类
################################################


/**
 * 这是一个用户相关功能和信息的适配器类
 * 
 * $obj->uid：用户的uid
 * $obj->username：用户的username
 * $obj->avatar：用户的头像信息
 * $obj->cp：用户的cp
 */
class xAdapter {
    function __construct() {
        global $db;
        $this->db = $db;
        
        $this->uid = null;
        $this->wallet = array();

        if(!empty($GLOBALS['deploy']['network'])) { //遍历所有network配置，生成对应类的wallet对象
            foreach($GLOBALS['deploy']['network'] as $n => $cfg) {
                $cls = $cfg['class']['wallet'];
                $this->wallet[$n] = new $cls($this);
            }
        }
        
        $this->username = null;
        $this->avatar = null;
        $this->cp = 0;

        $this->inventory = new \user\xInventory($this);
        $this->facility = new \user\xFacility($this);
        $this->efx = new \user\xEfx($this);

        $this->updateWallet();
    }

    /**
     * 加载用户数据
     * @param int $uid
     * 要加载的用户uid
     * 
     * @return bool
     * 失败则返回false
     */
    public function load(
        $uid = null
    ) {
        if(is_null($uid)) return false;

        $this->__construct(); //调用构造方法清空数据

        $info = $this->db->getArr(
            'users',
            array(
                "`uid` = {$uid}"
            ),
            null,
            1
        );

        if($info == false) return false;

        $this->uid = $info[0]['uid'];
        $this->username = \fDecode($info[0]['username']);
        $this->avatar = \fDecode($info[0]['avatar']);
        $this->cp = $info[0]['cp'];

        $this->updateWallet();
        $this->efx->load();
    }

    /**
     * 更新所有Wallet对象的属性
     */
    public function updateWallet() {
        if(!empty($GLOBALS['deploy']['network'])) { //遍历所有network配置，生成对应类的wallet对象
            foreach($GLOBALS['deploy']['network'] as $n => $cfg) {
                $this->wallet[$n]->load();
            }
        }
    }

    /**
     * 修改该用户的cp
     * 
     * @param int|float $amount
     * 修改的数量，负数为扣除，正数为增加
     * 
     * @return bool
     * 修改成功返回true，否则返回false
     */
    function modCP (
        $amount
    ) {
        if(is_null($this->uid)) {
            \fLog('Error: no uid given.');
            return false;
        }
        if(!is_numeric($amount)) {
            \fLog('Error: wrong format of $amount, must be numeric.');
            return false;
        }
        global $db;
        $this->cp = \fAdd($this->cp, $amount);

        $db->update(
            'users',
            array(
                'cp' => $this->cp
            ),
            array(
                "`uid` = '{$this->uid}'"
            ),
            1
        );
        return true;
    }
}