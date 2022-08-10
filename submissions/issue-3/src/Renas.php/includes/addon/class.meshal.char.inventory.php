<?php
namespace meshal\char;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal角色版本升级的类
################################################
use \meshal\xChar as xChar;
use \meshal\xItem as xItem;

/**
 * 物品容器对象的数据结构
 * 
 * 每个装备槽位都是一个容器，遵循以下格式：
 * $obj->equipmentContainer = array(
 *  'maxSlots' => int, //最多可装备物品的槽位数量
 *  'usedSlots' => int, //已经被使用的装备槽位数量
 *  'items' => array( //装备在这里的物品清单
 *      'itemName1' => int, //key是物品名称，value是数量
 *      'itemName2' => int,
 *      ...
 *  )
 * )
 * 
 * 携带的物品是一个独立的容器
 * $obj->carrying = array(
 *  'itemName1' => int, //key是物品名称，value是数量
 *  'itemName2' => int,
 *  ...
 * )
 */

/**
 * 这是一个物品容器类，用来存储和记录角色携带的物品
 * 
 * 常用方法
 * 
 * 添加携带的物品
 * $obj->acquire(
 *  string $itemName, //添加物品的名称
 *  int $amount, //添加物品的数量
 *  bool $fromOwner //是否从拥有者的仓库划转
 * )
 * 
 * 从携带中移除物品
 * $obj->discard(
 *  string $itemName, //要移除的物品名称
 *  int $amount, //移除物品的数量
 *  bool $toOwner //是否划转到拥有者的仓库
 * )
 * 
 * 装备物品（会自动匹配到对应的装备槽）
 * $obj->equip(
 *  string $itemName, //要装备的物品名称
 *  int $amount, //装备物品的数量
 *  bool $fromCarrying //是否从携带物品中装备
 * )
 * 
 * 卸下装备的物品（会自动从对应的装备槽卸下）
 * $obj->doff(
 *  string $itemName, //要卸下的物品名称
 *  int $amount, //卸下物品的数量
 *  bool $toCarrying //卸下后是否存入携带物品中
 * )
 * 
 * 检查携带的物品数量
 * $obj->checkCarrying(
 *  string $itemName, //检查的物品名称
 *  int $amount //比较数量
 * ): 如果数量不足或不存在，返回false，否则返回true
 * 
 * 查询携带的某个物品的数量（不包括装备中的）
 * $obj->countCarrying(
 *  string $itemName //查询的物品名称
 * ): 返回携带的该物品的数量
 * 
 * 更新装备/携带物品带来的影响
 * $obj->update()
 */
class xInventory {
    function __construct(
        \meshal\xChar &$char
    ) {
        $this->parent = &$char;
        $this->db = &$this->parent->db;

        $this->carrying = array(); //这个数组用于存储携带非装备的物品
        $this->loading = 0.0; //负载

        //根据装备槽位注册表，初始化每个装备槽容器（作为本对象的属性）
        foreach ($GLOBALS['meshal']['equipmentContainer'] as $containerCode => $settings) {
            $this->$containerCode = array(
                'items' => array(), //装备中的物品对象
                'maxSlots' => 0, //总槽位
                'usedSlots' => 0, //被占用的槽位
                'availableSlots' => 0, //剩余可用的槽位
            );
        }
    }

    /**
     * 设置一个容器的最大槽位数
     * 
     * @param string $slotName
     * 槽位名
     * 
     * @param int $amount
     * 设置的值
     */
    public function setSlots (
        string $slotName,
        int $amount
    ) {
        if(!isset($this->$slotName)) {
            \fLog('Error: invalid $slotName: '.$slotName);
            return false;
        }

        $this->{$slotName}['maxSlots'] = $amount;
        return true;
    }

    /**
     * 增减一个装备槽位的数量
     * 
     * @param string $slotName
     * 槽位名
     * 
     * @param int $amount = 1
     * 设置的值
     */
    public function modSlots (
        string $slotName,
        int $amount
    ) {
        if(!isset($this->$slotName)) {
            \fLog('Error: invalid $slotName: '.$slotName);
            return false;
        }

        $this->{$slotName}['maxSlots'] += $amount;
        return true;
    }

    /**
     * 获取某个装备槽中可用槽位的数量
     * 
     * @param string $slotName
     * 槽位名称
     * 
     * @return int
     * 返回槽位数量
     */
    public function getAvailableSlots (
        string $slotName
    ) {
        if(!isset($this->$slotName)) {
            \fLog('Error: invalid $slotName: '.$slotName);
            return 0;
        }

        return $this->{$slotName}['availableSlots'];
    }

    /**
     * 在行囊中存入一个物品
     * 
     * @param string $itemName
     * 物品模板名称
     * 
     * @param int $amount = 1
     * 添加的物品数量
     * 
     * @param bool $fromOwner = false
     * 是否从拥有者的仓库中划转
     * 
     * @return int
     * 存入结果错误码
     * - 0：成功
     * - 1：物品模板在数据库中不存在
     * - 2：物品在用户的仓库中存量不足
     * - 3：角色没有足够的负载
     */
    public function acquire (
        string $itemName,
        int $amount = 1,
        bool $fromOwner = false
    ) {
        $item = xItem::getData($itemName); //读取物品信息
        
        //物品是否存在
        if($item === false) {
            \fLog("Item {$itemName} doesn't exist in the library");
            return 1;
        }

        //检查owner的存货
        if($fromOwner === true) {
            if($this->parent->owner->inventory->checkStock($itemName, $amount) === false) { //数量不足，报错
                \fLog("User({$this->parent->owner->uid}) doesn't have enough {$itemName}");
                return 2;
            } 
        }

        //检查角色的负载
        if(($this->parent->cc->total - $this->loading) < ($item['loads'] * $amount)) {
            \fLog("Character({$this->parent->id}) doesn't have enough carrying capability");
            return 3;
        }

        if($fromOwner === true) {
            $this->parent->owner->inventory->remove($itemName, $amount);
            \fLog("Item {$itemName} x{$amount} is removed from user id={$this->parent->owner->uid}'s inventory");
        }
        
        $this->carrying[$itemName] += $amount;
        \fLog("Item {$itemName} x{$amount} is added to character id={$this->parent->id}'s carrying");

        $this->update();
        return 0;
    }

    /**
     * 从行囊中移除一个物品
     * 
     * @param string $itemName
     * 物品模板名称
     * 
     * @param int $amount = 1
     * 移除的物品数量
     * 
     * @param bool $toOwner = false
     * 是否划转到拥有者的仓库
     * 
     * @return int
     * 返回执行的状态码
     * - 0：移除成功
     * - 1：物品在数据库中不存在
     * - 2：角色行囊中没有此物品或物品数量不足
     */
    public function discard (
        string $itemName,
        int $amount = 1,
        bool $toOwner = false
    ) {
        //检查行囊中是否有该物品记录
        if(!isset($this->carrying[$itemName])) {
            \fLog("There is no {$itemName} in the carrying inventory");
            return 2;
        }

        //检查行囊中的该物品是否有足够的数量
        if($this->carrying[$itemName] < $amount) {
            \fLog("There are only {$this->carrying[$itemName]}x {$itemName}");
            return 2;
        }

        $item = xItem::getData($itemName); //读取物品信息
        
        //物品是否存在
        if($item === false) {
            \fLog("Item template {$itemName} doesn't exist in library");
            return 1;
        }

        //减少物品数量
        $this->carrying[$itemName] -= $amount;
        \fLog("Item {$itemName} x{$amount} is removed from character id={$this->parent->id}'s carrying");

        if(
            $toOwner === true
            && !is_null($this->parent->owner->uid)
        ) {
            $this->parent->owner->inventory->add($itemName, $amount);
            \fLog("Item {$itemName} x{$amount} is added to user id={$this->parent->owner->uid}'s inventory");
        }

        $this->update();
        return 0;
    }

    /**
     * 装备一件物品
     * 
     * @param string $itemName
     * 物品名称
     * 
     * @param int $amount = 1
     * 数量
     * 
     * @param bool $fromCarrying = true
     * 是否从携带的物品中装备
     * 如果这个开关为false，那么物品就会无中生有地装备到角色
     * 
     * @return int 
     * 返回状态码
     * - 0：成功
     * - 1：物品模板不存在
     * - 2：角色没有携带该物品，或没有携带足够的数量
     * - 3：角色没有足够的空余槽位装备该物品
     */
    public function equip (
        string $itemName,
        int $amount = 1,
        bool $fromCarrying = true
    ) {
        $item = xItem::getData($itemName); //读取物品信息

        //物品是否存在
        if($item === false) {
            \fLog("Item {$itemName} doesn't exist in library");
            return 1;
        }

        //检查carrying中是否有足够的物品
        if(
            $fromCarrying === true
            && $this->checkCarrying($itemName, $amount) === false
        ) {
            \fLog("There're not enough item {$itemName} in character's carrying");
            return 2;
        }

        //空余槽位检查
        if(
            $this->{$item['occupancy']['type']}['maxSlots'] - $this->{$item['occupancy']['type']}['usedSlots']
            < $item['occupancy']['slots'] * $amount
        ) {
            \fLog("Insufficient slots for equiping item");
            return 3;
        }

        $this->{$item['occupancy']['type']}['items'][$itemName] += $amount; //增加这个槽位中的物品数量
        \fLog("Item {$itemName} x{$amount} is added to character id={$this->parent->id}'s {$item['occupancy']['type']} equipment slot");

        //从携带物品中移除对应的物品
        if($fromCarrying === true) {
            $this->discard($itemName, $amount, false);
            \fLog("Item {$itemName} x{$amount} is removed from char({$this->parent->id})'s carrying");
        }

        $this->update();

        return 0;
    }

    /**
     * 卸下装备中的物品
     * 
     * @param string $itemName
     * 物品名称
     * 
     * @param int $amount = 1
     * 数量
     * 
     * @param bool $toCarrying = true
     * 卸下后是否存入carrying
     * 如果这个开关为false，那么物品卸下后就会销毁
     * 
     * @return int 
     * 返回错误码
     * - 0：成功
     * - 1：物品模板不存在
     * - 2：物品没有在角色的装备槽中
     * - 3：角色装备中没有足够数量的物品以供卸下
     */
    public function doff (
        string $itemName,
        int $amount = 1,
        bool $toCarrying = true
    ) {
        $item = xItem::getData($itemName); //读取物品信息

        //物品是否存在
        if($item === false) {
            \fLog("Item {$itemName} doesn't exist in library");
            return 1;
        }

        //检查物品是否在槽位中
        if(!isset($this->{$item['occupancy']['type']}['items'][$itemName])) {
            \fLog("There is no item {$itemName} equipped");
            return 2;
        }
        
        //检查是否有足够的数量可供卸下
        if($this->{$item['occupancy']['type']}['items'][$itemName] < $amount) {
            \fLog("There are not enough item {$itemName} for doffing");
            return 3;
        }

        //卸下对应的物品
        $this->{$item['occupancy']['type']}['items'][$itemName] -= $amount;//减少槽位中物品的数量
        \fLog("Item {$itemName} x{$amount} is removed from character id={$this->parent->id}'s {$item['occupancy']['type']} equipment slot");

        //卸下后放入carrying
        if($toCarrying === true) {
            $this->carrying[$itemName] += $amount;
            \fLog("Item {$itemName} x{$amount} is added to character id={$this->parent->id}'s carrying");
        }

        $this->update();

        return 0;
    }

    /**
     * 统计角色装备+携带的指定物品的总量
     * 
     * @param string $itemName
     * 要查询的物品名称
     * 
     * @return int
     * 返回物品数量，如果物品没有记录则返回0
     */
    public function countItem(
        string $itemName
    ) {
        return $this->countEquipment($itemName) + $this->countCarrying($itemName);
    }

    /**
     * 检查是否有装备指定数量的指定物品
     * 
     * @param string $itemName
     * 要检查的物品名称
     * 
     * @param int $amount = 1
     * 要检查的物品数量
     * 
     * @return bool
     * 检查通过返回true，否则返回false
     */
    public function checkEquipment(
        string $itemName,
        int $amount = 1
    ) {
        if($this->countEquipment($itemName) < $amount) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 查询装备中的物品中，某件指定物品的数量
     * 
     * @param string $itemName
     * 要查询的物品名称
     * 
     * @return int
     * 返回物品数量，如果物品没有记录则返回0
     */

    public function countEquipment(
        string $itemName
    ) {
        $item = xItem::getData($itemName); //读取物品数据

        //物品是否存在
        if($item === false) {
            \fLog("Item {$itemName} doesn't exist in library");
            return 0;
        }

        if(
            !isset($this->{$item['occupancy']['type']}['items'][$itemName])
            || is_null($this->{$item['occupancy']['type']}['items'][$itemName])
        ) {
            return 0;
        } else {
            return $this->{$item['occupancy']['type']}['items'][$itemName];
        }
    }

    /**
     * 检查指定数量的指定物品是否在携带物品中
     * 
     * @param string $itemName
     * 要检查的物品名称
     * 
     * @param int $amount = 1
     * 要检查的物品数量
     */
    public function checkCarrying(
        string $itemName,
        int $amount = 1
    ) {
        if($this->countCarrying($itemName) < $amount) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 查询携带物品中某件物品的数量
     * 
     * @param string $itemName
     * 要查询的物品名称
     * 
     * @return int
     * 返回物品数量，如果物品没有记录则返回0
     */
    public function countCarrying(
        string $itemName
    ) {
        if(
            !isset($this->carrying[$itemName])
            || is_null($this->carrying[$itemName])
        ) {
            return 0;
        } 
        
        else {
            return $this->carrying[$itemName];
        }
    }

    /**
     * 更新数据
     */
    public function update() {
        $this->loading = 0.0;
        $equipEfx = array('strength' => 0);
        foreach ($GLOBALS['meshal']['equipmentContainer'] as $containerCode => $settings) { //遍历注册表中每个装备容器名
            $this->{$containerCode}['usedSlots'] = 0;
            foreach ($this->{$containerCode}['items'] as $itemName => $amount) { //遍历每个装备容器中的物品
                //取物品数据
                $item = xItem::getData($itemName);

                //整理物品的加数修正
                foreach ($item['data']['equip']['modifier'] as $scoreName => $mod) {
                    if(!isset($equipEfx['modifier'][$scoreName])) {$equipEfx['modifier'][$scoreName] = 0;}
                    $equipEfx['modifier'][$scoreName] += $mod * $amount;
                }

                //整理物品的系数修正
                foreach ($item['data']['equip']['multiplier'] as $scoreName => $mod) {
                    if(!isset($equipEfx['multiplier'][$scoreName])) {$equipEfx['multiplier'][$scoreName] = 1;}
                    $equipEfx['multiplier'][$scoreName] *= pow($mod, $amount); //这里数量对倍数的作用是指数级的
                }

                //整理物品的实力修正
                $equipEfx['strength'] += $item['strength']['equip'] * $amount;

                //累加负载
                $this->loading += $item['loads'] * $amount;

                //累加使用槽位
                $this->{$containerCode}['usedSlots'] += $item['occupancy']['slots'] * $amount;
                
                //移除数量为0的物品
                if($amount == 0 || is_null($amount)) {
                    unset($this->{$containerCode}['items'][$itemName]);
                }
            }
            $this->{$containerCode}['availableSlots'] = $this->{$containerCode}['maxSlots'] - $this->{$containerCode}['usedSlots'];
        }

        $carryEfx = array('strength' => 0);
        foreach ($this->carrying as $itemName => $amount) { //遍历carrying容器中的物品
            //取物品数据
            $item = xItem::getData($itemName);

            //整理物品的加数修正
            foreach ($item['data']['carry']['modifier'] as $scoreName => $mod) {
                if(!isset($carryEfx['modifier'][$scoreName])) {$carryEfx['modifier'][$scoreName] = 0;}
                $carryEfx['modifier'][$scoreName] += $mod * $amount;
            }

            //整理物品的系数修正
            foreach ($item['data']['carry']['multiplier'] as $scoreName => $mod) {
                if(!isset($carryEfx['multiplier'][$scoreName])) {$carryEfx['multiplier'][$scoreName] = 1;}
                $carryEfx['multiplier'][$scoreName] *= pow($mod, $amount); //这里数量对倍数的作用是指数级的
            }

            //整理物品的实力修正
            $carryEfx['strength'] += $item['strength']['carry'] * $amount;

            //累加负载
            $this->loading += $item['loads'] * $amount;

            //移除数量为0的物品
            if($amount == 0 || is_null($amount)) {
                unset($this->carrying[$itemName]);
            }
        }

        //更新父对象的属性（装备）
        if(!empty($equipEfx['modifier'])) {
            foreach ($equipEfx['modifier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('equipment', $value);
            }
        }
        if(!empty($equipEfx['multiplier'])) {
            foreach ($equipEfx['multiplier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('equipmentMultiplier', $value);
            }
        }

        //更新父对象的实力（装备）
        $this->parent->strength->set('equipment', $equipEfx['strength']);

        //更新父对象的属性（携带）
        if(!empty($carryEfx['modifier'])) {
            foreach ($carryEfx['modifier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('carrying', $value);
            }
        }
        if(!empty($carryEfx['multiplier'])) {
            foreach ($carryEfx['multiplier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('carryingMultiplier', $value);
            }
        }

        //更新父对象的实力（携带）
        $this->parent->strength->set('carrying', $carryEfx['strength']);

        //更新父对象的负载
        $this->parent->cc->set('current', $this->loading);
    }

    /**
     * 将容器中的记录导出
     * 
     * @return array
     * 返回的是一个数组
     */
    public function export() {
        $return = array();
        foreach ($GLOBALS['meshal']['equipmentContainer'] as $containerCode => $settings) {
            $return['equipment'][$containerCode] = $this->{$containerCode};
        }
        $return['carrying'] = $this->carrying;

        return $return;
    }

    /**
     * 根据给到的数组导入数据
     * 
     * @param array $importData
     * 导入的数组，格式必须为 array(
     *  'equipment' => array(
     *      'containerCode' => array(
     *          'maxSlots' = int
     *          'usedSlots' = int
     *          'items' = array(
     *              'item1' => amount, 
     *              'item2' => amount, 
     *              ...
     *          )
     *      ),
     *      ...
     *  ),
     *  'carrying' => array(
     *      'item1' => amount,
     *      'item2' => amount,
     *  ...
     * )
     */
    public function import(
        array $importData
    ) {
        // $this->__construct($this->parent); //调用构造函数清空数据

        //加载每个装备槽容器的数据
        if(!empty($importData['equipment'])) {
            foreach ($importData['equipment'] as $containerName => $data) {
                $this->{$containerName}['items'] = $data['items'];
            }
        }

        //加载携带的数据
        $this->carrying = $importData['carrying'];

        $this->update();
    }
}

?>