<?php
namespace user\wallet;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供用户Flow钱包地址的类
################################################

class xFlow {
    function __construct(
        object &$parent
    ) {
        //检查接入的对象类型
        if(
            get_class($parent) != 'user\xAdapter'
            && get_class($parent) != 'xUser'
        ) {
            \fLog("Wrong integration of parent object class");
        }

        $this->parent = &$parent;
        $this->db = &$this->parent->db;
        $this->address = false;

        $this->load();
    }

    /**
     * 加载用户的钱包地址
     */
    public function load() {
        $this->address = $this->getAddress();
    }

    /**
     * 保存/更新当前用户的钱包地址
     * 
     * @return int
     * 返回状态码
     * 0：成功更新
     * 1：删除成功
     * 2：更新失败
     */
    public function save() {
        if($this->address === false) { //没有address，删除原有的记录
            $this->db->delete(
                'user_wallet_flow',
                array(
                    "`uid` = '{$this->parent->uid}'"
                ),
                1
            );
            return 1;
        }

        //更新记录
        $check = self::updateMapping($this->parent->uid, $this->address);
        return $check > 2 ? 0 : 2;
    }

    /**
     * 更新用户的钱包地址
     */
    public function update() {
        if($this->parent->uid === false) return;
        \fAsync(
            $GLOBALS['deploy']['siteRoot'].DIR_ASYNC.'updateUserWallet.flow.php?uid='.$this->parent->uid
        );
    }

    /**
     * 获取钱包地址
     * 
     * @return string|bool
     * 返回钱包地址
     * 如果有错误，返回false
     */
    public function fetchAddressByUid() {
        if($this->parent->uid === false) return false; //如果父级对象没有有效的uid，返回false

        $discordId = \user\xDiscord::getDiscordId($this->parent->uid);
        if($discordId === false) {
            \fLog("Error: failed to fetch discordId of user({$this->parent->uid})");
            return false;
        }

        $response = json_decode(
            \fCallAPI(
                'GET',
                $GLOBALS['deploy']['external']['emeraldId'],
                array(
                    'discordId' => $discordId
                )
            ),
            true
        );

        if($response['success'] === false) {
            \fLog("Error: API responded a failure");
            return false;
        }

        if(is_null($response['res'])) {
            \fLog("Warning: user({$this->parent->uid}) who has discordId({$discordId}) hasn't bond emeraldId with wallet address");
            return false;
        }

        return $response['res'];
    }

    /**
     * 获取这个用户的绑定钱包地址
     * 
     * @return string|bool
     * 返回这个用户的钱包地址
     * 没找到则返回false
     */
    public function getAddress() {
        if($this->parent->uid === false || is_null($this->parent->uid)) return false; //如果父级对象没有有效的uid，返回false
        return self::getAddressByUid($this->parent->uid);
    }

    /**
     * 检查这个用户的钱包地址和传递的钱包地址是否一致
     * 
     * @return bool
     * 一致返回true，否则返回false
     */
    public function checkAddress(
        string $address
    ) {
        if($this->address === false) return false; //如果父级对象没有有效的uid，返回false
        if($address != $this->address) return false;
        return true;
    }

    /**
     * 传递钱包地址，反向查找uid
     * 
     * @return int
     * 返回uid
     * 没有找到则返回false
     */
    public static function fetchUidByAddress(
        string $address
    ) {
        $response = json_decode(
            \fCallAPI(
                'GET',
                $GLOBALS['deploy']['external']['emeraldId'],
                array(
                    'address' => $address
                )
            ),
            true
        );

        if($response['success'] === false) {
            \fLog("Error: API responded a failure");
            return false;
        }

        if(is_null($response['res'])) {
            \fLog("Warning: the address({$address}) is not related with any discordId yet");
            return false;
        }

        return \user\xDiscord::getUid($response['res']);
    }

    /**
     * 根据传递的uid查找对应的钱包地址
     * 
     * @param int $uid
     * 用户uid
     * 
     * @return string|bool
     * 返回查找到的钱包地址
     * 如果没有找到，返回false
     */
    public static function getAddressByUid(
        int $uid
    ) {
        global $db;

        $query = $db->getArr(
            'user_wallet_flow',
            array(
                "`uid` = '{$uid}'"
            ),
            null, 1
        );

        if($query === false) {
            \fLog("Warning: user($uid) hasn't mapped with an address");
            return false;
        }

        return $query[0]['address'];
    }

    /**
     * 根据传递的address查找对应的用户uid
     * 
     * @param string $address
     * 钱包地址
     * 
     * @return int|bool
     * 返回查找到的uid
     * 如果没有找到，返回false
     */
    public static function getUidByAddress(
        string $address
    ) {
        global $db;

        $query = $db->getArr(
            'user_wallet_flow',
            array(
                "`address` = '{$address}'"
            ),
            null, 1
        );

        if($query === false) {
            \fLog("Warning: address({$address}) hasn't mapped with an uid");
            return false;
        }

        return $query[0]['uid'];
    }

    /**
     * 更新或新建用户的钱包映射关系
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param string $address
     * 钱包地址
     * 
     * @return int
     * 返回状态码
     * 0：创建了新记录
     * 1：更新了原记录
     * 2：无需更新
     * 3：创建失败
     * 4：更新失败
     */
    public static function updateMapping(
        int $uid,
        string $address
    ) {
        global $db;

        //查询是否有记录
        $check = $db->getArr(
            'user_wallet_flow',
            array(
                "`uid` = '{$uid}'"
            ),
            null, 1
        );

        if($check[0]['address'] == $address) return 2;

        if($check == 0) { //无记录则插入
            $query = $db->insert(
                'user_wallet_flow',
                array(
                    'address' => $address,
                    'uid' => $uid
                )
            );

            if($query === false) {
                \fLog("Error: failed to insert address mapping");
                return 3;
            }
            return 0;
        } else { //有则更新
            $query = $db->update(
                'user_wallet_flow',
                array(
                    'address' => $address
                ),
                array(
                    "`uid` = '{$uid}'"
                ),
                1
            );

            if($query == 0) {
                \fLog("Error: failed to update address mapping");
                return 4;
            }
            return 2;
        }
    }
}

?>