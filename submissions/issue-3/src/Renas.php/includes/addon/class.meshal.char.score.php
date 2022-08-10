<?php
namespace meshal\char;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal属性对象的类
################################################
use \meshal\xChar as xChar;

/**
 * 属性数值的基础类
 * 
 * 常用方法：
 * 
 * $obj->mod(
 *  string $property, 
 *  num $amount
 * );
 * 修改某个部分的属性（比如$property='base'表示修改基础属性）。$amount为负数时表示扣除。
 * 
 * $obj->set(
 *  string $property, 
 *  num $amount
 * );
 * 将某个部分的属性设为特定值。
 * 
 * $obj->add(
 *  string $property, 
 *  num $amount
 * );
 * 增加某个属性部分
 * 
 * $obj->sub(
 *  string $property, 
 *  num $amount
 * );
 * 减少某个属性部分
 * 
 * $obj->restore();
 * 重设当前属性为最大值
 */
class xCommonScore
{
    function __construct () {

        //属性注册表，只有注册过的属性才会被$this->export()方法导出
        $this->registry = array(
            'base' => 0, //受训属性
            'feature' => 0, //特征修正
            'ability' => 0, //被动能力修正

            'equipment' => 0, //装备修正
            'carrying' => 0, //携带物品修正
            'buff' => 0, //临时修正

            'permanent' => 0, //基础属性总值（受训、特征、被动能力）
            'temporary' => 0, //临时属性总值（装备、携带、临时）

            'featureMultiplier' => 1.0, //特征系数修正
            'abilityMultiplier' => 1.0, //被动能力系数修正

            'equipmentMultiplier' => 1.0, //装备系数修正
            'carryingMultiplier' => 1.0, //携带物品系数修正
            'buffMultiplier' => 1.0, //临时系数修正

            'permanentMultiplier' => 1.0, //基础属性系数修正（特征、被动能力）
            'temporaryMultiplier' => 1.0, //临时属性系数修正（装备、携带、临时）

            'total' => 0, //最大属性
            'current' => 0, //当前属性

            'currentDigit' => 0.0, //用于记录恢复时未凑足整数的部分
            'nextRecover' => 0 //用于记录下一个恢复到整数属性的时间戳
        );

        //根据注册表初始化属性
        foreach ($this->registry as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }

        $this->update();
    }

    /**
     * 导出数据为数组
     * 
     * @return array
     * 返回包含每个属性构成的数组
     */
    public function export() {
        $return = array();
        foreach ($this->registry as $propertyName => $settings) {
            $return[$propertyName] = $this->$propertyName;
        }
        return $return;
    }

    /**
     * 设置一个属性组成的值
     * 
     * @param string $propertyName
     * 属性名（见__construct())
     * 
     * @param int|float $amount
     * 设置的值
     */
    public function set (
        string $propertyName,
        $amount
    ) {
        if(
            !is_numeric($amount)
            && !is_bool($amount)
        ) {
            \fLog('Error: $amount must be numeric or boolean');
            return false;
        }

        if(!isset($this->$propertyName)) {
            \fLog('Error: invalid $propertyName: '.$propertyName);
            return false;
        }

        $this->$propertyName = $amount;
        $this->update();
        return true;
    }

    /**
     * 增减一个属性组成的值
     * 
     * @param string $propertyName
     * 属性名（见__construct())
     * 
     * @param int|float $amount
     * 设置的值，如果为负数就是减少，如果为正数就是增加
     */
    public function mod (
        string $propertyName,
        $amount
    ) {
        if(
            !is_numeric($amount)
            && !is_bool($amount)
        ) {
            \fLog('Error: $amount must be numeric or boolean');
            return false;
        }

        if(!isset($this->$propertyName)) {
            \fLog('Error: invalid $propertyName: '.$propertyName);
            return false;
        }

        $this->$propertyName += $amount;
        $this->update();
        return true;
    }

    /**
     * 增加一个属性组成的值
     * 
     * @param string $propertyName
     * 属性名（见__construct())
     * 
     * @param int|float $amount
     * 设置的值，不能为负数，否则会报错
     */
    public function add (
        string $propertyName,
        $amount
    ) {
        if(
            !is_numeric($amount)
            && !is_bool($amount)
        ) {
            \fLog('Error: $amount must be numeric or boolean');
            return false;
        }

        if(!isset($this->$propertyName)) {
            \fLog('Error: invalid $propertyName: '.$propertyName);
            return false;
        }

        if($amount < 0) {
            \fLog('Error: amount is less than 0: '.$amount);
        }

        $this->$propertyName += $amount;
        $this->update();
        return true;
    }

    /**
     * 减少一个属性组成的值
     * 
     * @param string $propertyName
     * 属性名（见__construct())
     * 
     * @param int|float $amount
     * 设置的值，不能为负数，否则会报错
     */
    public function sub (
        string $propertyName,
        $amount
    ) {
        if(
            !is_numeric($amount)
            && !is_bool($amount)
        ) {
            \fLog('Error: $amount must be numeric or boolean');
            return false;
        }

        if(!isset($this->$propertyName)) {
            \fLog('Error: invalid $propertyName: '.$propertyName);
            return false;
        }

        if($amount < 0) {
            \fLog('Error: amount is less than 0: '.$amount);
        }

        $this->$propertyName -= $amount;
        $this->update();
        return true;
    }

    /**
     * 将当前属性值恢复至最大属性值
     */
    public function restore () {
        $this->update();
        $this->current = $this->total;
        return $this->current;
    }

    /**
     * 校准属性：进行一系列的属性检查，并校正属性值
     */
    public function update () {
        //一些特殊准备（比如负重要依赖强壮计算）
        $this->prepare();

        //计算基础属性
        $this->permanent = (
            $this->base
            + $this->feature
            + $this->ability
        );

        //计算临时属性
        $this->temporary = (
            $this->equipment
            + $this->carrying
            + $this->buff
        );

        //计算基础属性系数
        $this->permanentMultiplier = (
            $this->featureMultiplier
            * $this->abilityMultiplier
        );

        //计算临时属性系数
        $this->temporaryMultiplier = (
            $this->equipmentMultiplier
            * $this->carryingMultiplier
            * $this->buffMultiplier
        );

        //计算总属性
        $this->total = (
            $this->permanent
            + $this->temporary
        ) * (
            $this->permanentMultiplier
            * $this->temporaryMultiplier
        );

        //额外的矫正（预留）在这个基础类里，$this->calibrate是一个空方法，子类中可以自定义。用于对属性做一些特殊处理（比如不可小于0）
        $this->calibrate();

        //检查当前属性
        $this->checkCurrentScore();

        //对计算结果做舍尾处理
        $this->intScore();
    }

    /**
     * 检查当前属性是否超过了最大属性或小于0，如果超过则设为最大，如果小于0则设为0
     */
    protected function checkCurrentScore () {
        //当前属性值不能大于最大总属性值
        if($this->current > $this->total) {
            $this->current = $this->total;
        }

        //当前属性值不能小于0
        if($this->current < 0) {
            $this->current = 0;
        }

        //当前属性等于最大总属性时，恢复用的小数部分应当被重置为0
        if($this->current >= $this->total) {
            $this->currentDigit = 0.0;
        }
    }

    /**
     * 对属性做无条件舍尾处理
     */
    protected function intScore () {
        $this->total = \intval($this->total);
        $this->current = \intval($this->current);
    }

    /**
     * 属性矫正方法，由其他子类中自行定义
     * 默认这个方法是空的。
     */
    protected function prepare() {}

    /**
     * 属性矫正方法，由其他子类中自行定义
     * 默认这个方法是空的。
     */
    protected function calibrate() {}
}

/**
 * 基础属性
 * 这类属性最低不会小于1
 */
class xAttrScore extends xCommonScore
{
    function __construct () {
        parent::__construct();
        $this->update();
    }

    /**
     * calibrate()方法被重写，因为总属性不会降至0以下
     */
    public function calibrate () {
        //总属性不会降至1以下
        if($this->total < 1) {
            $this->total = 1;
        }
    }
}

/**
 * 正数属性
 * 机动/距离/速度相关的属性，这类属性最低不会小于0
 */
class xPositiveScore extends xCommonScore
{
    function __construct () {
        parent::__construct();
        $this->update();
    }

    /**
     * calibrate()方法被重写，因为总属性不会降至0以下
     */
    public function calibrate () {
        //总属性不会降至0以下
        if($this->total < 0) {
            $this->total = 0;
        }
    }
}

class xCarryingCapability extends xCommonScore
{
    function __construct(
        \meshal\xChar &$char
    ) {
        parent::__construct();
        $this->parent = $char;
        $this->update();
    }

    /**
     * calibrate()方法被重写，因为总属性不会降至0以下
     */
    public function calibrate () {
        //总属性不会降至0以下
        if($this->total < 0) {
            $this->total = 0;
        }
    }

    /**
     * 对属性做无条件舍尾处理
     */
    protected function intScore () {
        $this->total = \intval($this->total);
    }

    /**
     * 当前负载会超过最大负载
     */
    protected function checkCurrentScore () {
        //当前属性值不能小于0
        if($this->current < 0) {
            $this->current = 0;
        }
    }

    /**
     * 负重需要依赖强壮进行计算
     */
    public function prepare() {
        $this->base = $this->parent->m->total * 6;
    }
}

/**
 * 一个防护属性的类
 * 防护属性的$this->current实际上是没有意义的，它始终等于$this->total（由重写的$this->calibrate()更新）
 */
class xProtectionScore extends xCommonScore
{
    function __construct () {
        parent::__construct();
        $this->update();
    }

    /**
     * 保持$this->current 与 $this->total相同
     */
    protected function checkCurrentScore()
    {
        $this->current = $this->total;
    }
}

/**
 * 一个速度与范围的类
 * 速度与范围属性的$this->current实际上是没有意义的，它始终等于$this->total（由重写的$this->calibrate()更新）
 */
class xDistanceScore extends xCommonScore
{
    function __construct () {
        parent::__construct();
        $this->update();
    }

    /**
     * 保持$this->current 与 $this->total相同
     */
    protected function calibrate () {
        //总属性不会降至0以下
        if($this->total < 0) {
            $this->total = 0;
        }
        $this->current = $this->total;
    }
}

/**
 * 一个伤害免疫属性的类
 * 免疫属性不同的地方在于，将所有修正累加在一起后，只要结果大于1就是免疫。所以用boolean $this->immune来代表，由重写的calibrate()来更新
 * - restore()方法没有实用意义
 */
class xImmunityScore extends xCommonScore
{
    function __construct () {
        parent::__construct();
        $this->registry['immune'] = false;
        $this->immune = false; //是否免疫的最终结果
        $this->update();
    }

    /**
     * 根据最终的结果来更新 $this->immune
     */
    protected function calibrate () {
        //设置最终的$this->immune属性
        $this->immune = $this->total > 0 ? true : false;
    }
}

/**
 * 潜能和实力的类。单独定义了属性
 */
class xStrengthScore extends xCommonScore
{
    function __construct () {
        $this->registry = array(
            'base' => 0, //基础实力
            'feature' => 0, //特征修正实力，移除特征时从这里扣除
            'ability' => 0, //能力修正实力，移除能力时从这里扣除
            'equipment' => 0, //装备修正实力，移除装备时从这里扣除
            'carrying' => 0, //携带物品修正实力，从行囊中移除物品时从这里扣除

            'pp' => 0,
            'st' => 0
        );

        //根据注册表初始化属性
        foreach ($this->registry as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
        $this->update();
    }

    /**
     * 消耗潜能并增加实力
     * 这是一个常见的meshal角色成长的方法，当潜能被消耗时，就会增加等量的实力
     * 
     * @param int $amount
     * 要消耗的潜能数量
     * 
     * @param string $type
     * 增加哪部分的实力，可选项包括：
     * - 'base' //基础实力：与属性相关的实力增减。这是默认值
     * - 'feature' //特征实力：获得或移除特征导致的实力增减
     * - 'ability' //能力实力：获得或移除能力导致的实力增减
     * - 'equipment' //装备实力
     * - 'carrying' //携带实力
     * 
     * @return int
     * 返回的错误码
     * - 0：成功
     * - 1：潜能不足以支付
     */
    public function cost(
        int $amount,
        string $type = 'base'
    ) {
        if($this->pp < $amount) { //不允许超支
            \fLog("Insufficient pp for the cost({$amount})");
            return 1;
        }

        $this->sub('pp', $amount);
        $this->add($type, $amount);
        return 0;
    }

    //这个类中不需要restore方法
    public function restore() {}
    
    /**
     * 重写update()，只需要检查数据不小于0，且是整数即可。
     */
    public function update() {
        $this->st =
            $this->base
            + $this->feature
            + $this->ability
            + $this->equipment
            + $this->carrying
        ;

        if($this->pp < 0 ) {
            $this->pp = 0;
        }

        if($this->st < 0) {
            $this->st = 0;
        }

        $this->intScore();
    }

    /**
     * 对属性做无条件舍尾处理
     */
    protected function intScore () {
        $this->pp = \intval($this->pp);
        $this->st = \intval($this->st);
    }
}

?>