<?php
namespace user;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供用户物资的类
################################################

/**
 * 这是一个用户相关功能和信息的适配器类
 * 
 * 常用方法
 * 
 * 添加物品
 * $obj->add(
 *  string $itemName,
 *  int $amount
 * )
 * 
 * 移除物品
 * $obj->remove(
 *  string $itemName,
 *  int $amount
 * )
 * 
 * 查询指定物品的数量
 * $obj->getStock(
 *  string $itemName
 * )
 * 
 * 检查用户是否有指定数量的指定物品
 * $obj->checkStock(
 *  string $itemName,
 *  int $amount
 * )
 */
class xInventory {
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
    }

    /**
     * 获取用户拥有某件物品的数量
     * 
     * @param string $itemName
     * 物品的名称
     * 
     * @return int
     * 返回物品的数量
     */
    public function getStock(
        string $itemName
    ) {
        $query = $this->db->getArr(
            'user_items',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '{$itemName}'"
            ),
            null,
            1
        );

        if($query === false) {
            return 0;
        } else {
            return $query[0]['amount'];
        }
    }

    /**
     * 检查用户是否拥有指定数量的指定物品
     * 
     * @param string $itemName
     * 物品名称
     * 
     * @param int $amount
     * 物品数量
     * 
     * @return bool
     * 如果有足量的物品返回true，不足返回false
     */
    public function checkStock(
        string $itemName,
        int $amount
    ) {
        if($this->getStock($itemName) >= $amount) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 增加物品
     * 
     * @param string $itemName
     * 增加物品的名字
     * 
     * @param int $amount
     * 增加物品的数量
     * 
     * @return int
     * 返回错误码
     * - 0：成功
     * - 1：物品在数据库中不存在
     * - 2：更新数量时出错
     */
    public function add(
        string $itemName,
        int $amount = 1
    ) {
        //取物品数据
        $item = \meshal\xItem::getData($itemName);

        if($item === false) { //物品不存在，报错
            \fLog("Item {$itemName} doesn't exist in library");
            return 1;
        }

        //取用户现有的物品数量
        $query = $this->db->getArr(
            'user_items',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '{$itemName}'"
            ),
            null,
            1
        );

        if($query === false) { //没有该物品的信息，插入一条新的
            $check = $this->db->insert(
                'user_items',
                array(
                    'uid' => $this->parent->uid,
                    'name' => $itemName,
                    'amount' => $amount,
                    'lastUpdate' => time()
                )
            );
            \fLog("Item {$itemName} x{$amount} is added to user id={$this->parent->uid}'s inventory");
        } else { //有数据，更改数量
            $check = $this->db->update(
                'user_items',
                array(
                    'amount' => $query[0]['amount'] + $amount,
                    'lastUpdate' => time()
                ),
                array(
                    "`uid` = '{$this->parent->uid}'",
                    "`name` = '{$itemName}'"
                ),
                1
            );
            \fLog("Item {$itemName} x{$amount} is added to user id={$this->parent->uid}'s inventory");
        }

        if($check === false) {
            \fLog("Error while updating item amount");
            return 2;
        }

        return 0;
    }

    /**
     * 移除物品
     * 
     * @param string $itemName
     * 移除物品的名字
     * 
     * @param int $amount
     * 移除物品的数量
     * 
     * @return int
     * 返回的错误码
     * - 0：操作成功
     * - 1：物品在数据库中不存在
     * - 2：用户没有足够的此类物品
     * - 3：更新物品数量失败
     */
    public function remove(
        string $itemName,
        int $amount
    ) {
        //取物品数据
        $item = \meshal\xItem::getData($itemName);

        if($item === false) { //物品不存在，报错
            \fLog("Item {$itemName} doesn't exist in library");
            return 1;
        }

        //取用户现有的物品数量
        $query = $this->db->getArr(
            'user_items',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '{$itemName}'"
            ),
            null,
            1
        );

        //检查是否有物品记录
        if($query === false) {
            \fLog("There's no {$itemName} in user's inventory");
            return 2;
        }

        //检查是否有足够的数量
        if($query[0]['amount'] < $amount) {
            \fLog("There're not enough {$itemName} in user's inventory");
            return 2;
        }

        $check = $this->db->update(
            'user_items',
            array(
                'amount' => $query[0]['amount'] - $amount,
                'lastUpdate' => time()
            ),
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '$itemName'"
            ),
            1
        );

        if($check === false) {
            \fLog("Error while updating item amount");
            return 3;
        }

        \fLog("Item {$itemName} x{$amount} is removed from user id={$this->parent->uid}'s inventory");

        return 0;
    }
}