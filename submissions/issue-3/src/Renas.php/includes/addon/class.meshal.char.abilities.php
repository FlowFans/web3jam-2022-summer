<?php
namespace meshal\char;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal能力容器的类
################################################
use \meshal\xAbility as xAbility;

/**
 * 能力容器对象的数据结构
 * 
 * 能力容器注册表
 * $obj->containerRegistry = array(
 *  'containerType1' => array('sustain' => 'attr'), //sustain表示该容器中的能力会占用哪项属性
 *  ...
 * );
 * 
 * 每个能力容器都是以下的数据格式
 * $obj->abilityContainerName = array(
 *  'abilityName1' => array( //能力的名称
 *      'enhanced' => 0, //能力的修行次数
 *      'acquired' => array(
 *          '__INDEPENDENT__' => true, //有这样的记录表示这个能力是独立获得的
 *          'dependFeatureType1' => array( //赋予这个能力的特征类型
 *              'dependFeatureName1' => true, //赋予这个能力的特征名称
 *              'dependFeatureName2' => true,
 *              ...
 *          )
 *      )
 *  ),
 *  ...
 * );
 */

/**
 * 这是一个能力容器类，用来存储和记录角色的能力
 * 
 * 常用方法
 * 
 * 添加一个能力
 * $obj->add(
 *  string $abilityName, //添加的能力名称
 *  bool $cost, //是否有潜能花销（天赋能力不需要花销）
 *  string $dependFeatureType, //赋予这个能力的源特征类型
 *  string $dependFeatureName //赋予这个能力的源特征名称
 * );
 * 
 * 移除一个能力
 * $obj->remove(
 *  string $abilityName, //要移除的能力名称
 *  bool $skipDependenceCheck //是否检查特征依赖关系
 * );
 * 
 * 更新能力容器和能力的效果
 * $obj->update();
 * 
 * 从数据库中获取一个能力的数据
 * $obj->getData(
 *  string $abilityName //要查询的能力名称
 * );
 */
class xAbilities
{
    function __construct(
        \meshal\xChar &$char
    ) {
        $this->parent = &$char;
        $this->db = &$this->parent->db;
        
        /**
         * 能力类型容器，都是用于存储不同类型特征的容器
         * 能力类型容器 => array(
         *  sustain => string //占用什么属性，没有则不占用
         * )
         */
        $this->containerRegistry = array(
            'm' => array('sustain' => 'm'), //强壮能力
            'a' => array('sustain' => 'a'), //活力能力
            's' => array('sustain' => 's'), //精神能力
            'c' => array(), //通用能力
            'g' => array() //天赋能力
        );

        /**
         * 根据能力容器注册表，初始化每个能力容器（作为本对象的属性）
        */
        foreach ($this->containerRegistry as $container => $settings) {
            $this->$container = array();
        }

        $this->update();
    }

    /**
     * 添加一个能力
     * 
     * @param string $abilityName
     * 添加能力的名称
     * 
     * @param bool $cost
     * 是否要花费潜能来获得这个能力，为true时需要花费潜能，为false时不需要花费潜能
     * 默认为true
     * 
     * @param string $dependFeatureType
     * 如果这个能力是天赋能力，那么这里填赋予该能力的源特征类型
     * 默认为null
     * 
     * @param string $dependFeatureName
     * 如果这个能力是天赋能力，那么这里填赋予该能力的源特征名称
     * 默认为null
     */
    public function add (
        string $abilityName,
        bool $cost = true,
        string $dependFeatureType = null,
        string $dependFeatureName = null
    ) {
        //从数据库取该能力数据
        $data = xAbility::getData($abilityName);

        //先检查是否在库里有能力配置
        if($data === false) {
            \fLog("Ability {$abilityName} doesn't exist in library");
            return false;
        }

        //不允许重复添加能力
        $exists = $this->isAbilityExist($abilityName);
        if(!empty($exists)) {
            $abilityContainers = implode(' & ', $exists);
            \fLog("Ability {$abilityName} already exists in the ability list {$abilityContainers}, should remove it from the list in advance");
            return false;
        }

        if(
            !is_null($dependFeatureType) 
            && !is_null($dependFeatureName)
        ) { //作为天赋能力添加
            //不允许重复添加
            if(isset($exist['g'])) {
                $abilityContainers = implode(' & ', $exists);
                \fLog("Ability {$abilityName} already exists in the ability list {$abilityContainers}, should remove it from the list in advance");
                return false;
            }

            //这个能力通过其他特征添加，是一个天赋能力。
            //因此需要记录它的继承关系，以便以后用$xFeatures->remove()移除源特征时，也可以把这个能力移除
            $this->g[$abilityName]['acquired'][$dependFeatureType][$dependFeatureName] = true;
            if(!isset($this->g[$abilityName]['enhanced'])) {
                $this->g[$abilityName]['enhanced'] = 0;
            }
            
            //仍然要增加实力，但不消耗潜能
            $this->parent->strength->add('ability', $data['level'] * 3);

            \fLog("Ability {$abilityName} was added into ability list g because of feature {$dependFeatureType}.{$dependFeatureName}");
        } 
        else { //作为普通能力添加
            //不允许重复添加
            if(!empty($exists)) {
                $abilityContainers = implode(' & ', $exists);
                \fLog("Ability {$abilityName} already exists in the ability list {$abilityContainers}, should remove it from the list in advance");
                return false;
            }

            //检查属性是否足够能力占用
            if(!is_null($data['attr'])) { //为空则是通用能力
                if( $this->sustain[$data['attr']]
                    + $data['level']
                    < $this->parent->$data['attr']->permanent
                ) {
                    \fLog("Cannot add {$abilityName}, there're insufficient attribute scores({$data['attr']}) for sustaining it");
                    return false;
                }
            }

            //检查潜能是否足够获得此能力
            if(
                $cost === true 
                && $this->parent->strength->pp < $data['level'] * 3
            ) {
                \fLog("Cannot add {$abilityName}, insufficient pp.");
                return false;
            }

            //开始添加能力
            $this->{$data['attr']}[$abilityName] = array(
                'enhanced' => 0,
                'acquired' => array(
                    '__INDEPENDENT__' => true //有这个标记的能力表示：不是通过其他特征自动添加的
                )
            );

            //为添加能力扣除潜能，增加实力
            if($cost === true) {
                $this->parent->strength->cost($data['level'] * 3, 'ability');
            }

            \fLog("Ability {$abilityName} was added into ability list {$data['attr']} independently");
        }

        $this->update();
    }

    /**
     * 移除能力
     * 
     * @param string $abilityName
     * 要移除的能力名称
     * 
     * @param bool $skipDependenceCheck
     * 是否检查特征的依赖关系，关闭的话会忽略依赖关系直接移除。
     * 默认为false。
     * 
     * @return bool
     * 返回是否移除成功的状态
     */
    public function remove (
        string $abilityName,
        bool $skipDependenceCheck = false
    ) {
        $exist = $this->isAbilityExist($abilityName);

        //如果容器中不存在该能力，返回false
        if($exist === false) {
            \fLog("Ability {$abilityName} doesn't exist in any ability lists");
            return false;
        }

        $data = xAbility::getData($abilityName);

        foreach ($exist as $abilityContainer => $ability) {
            if($skipDependenceCheck === false) {
                $dependence = $this->$abilityContainer[$abilityName]['acquired'];
                unset($dependence['__INDEPENDENT__']);
                //如果依赖关系不为空，则不移除
                if(!empty($dependence)) {
                    \fLog("Unable to remove ability {$abilityName} since it's added by other features");
                    return false;
                }
            }

            //如果容器中存在这个能力，则移除
            unset($this->$abilityContainer[$abilityName]);
            //扣除实力
            $this->parent->strength->sub('ability', $data['level'] * 3);

            \fLog("Ability {$abilityName} was removed from {$abilityContainer}");
        }

        $this->update();
        return true;
        
    }

    /**
     * 根据给到的源特征，遍历所有容器，并将依赖源特征获得的能力移除。如果一个能力有多个源特征，或是独立获得的，则不会移除。
     * 这个方法由meshal\char\feature::remove()方法中自动调用。
     * 
     * @param string $sourceType
     * 源特征的类型
     * 
     * @param string $sourceName
     * 源特征的名称
     */
    public function featureRemove (
        string $sourceFeatureType,
        string $sourceFeatureName
    ) {
        foreach ($this->containerRegistry as $abilityContainer => $settings) {
            foreach ($this->$abilityContainer as $abilityName => $dependence) {
                if(isset($dependence['acquired'][$sourceFeatureType][$sourceFeatureName])) {
                    unset($this->$abilityContainer[$abilityName]['acquired'][$sourceFeatureType][$sourceFeatureName]); //真实地从能力容器中的对应记录里移除
                    unset($dependence['acquired'][$sourceFeatureType][$sourceFeatureName]); //从查找用的临时数组中移除

                    if(empty($dependence['acquired'][$sourceFeatureType])) { //如果移除以后，上层数组里就没成员了，就应当将上层数组unset
                        unset($dependence['acquired'][$sourceFeatureType]); //真实地从能力容器中的对应记录里移除
                        unset($this->$abilityContainer[$abilityName]['acquired'][$sourceFeatureType]); //从查找用的临时数组中移除
                    }

                    if(
                        !isset($dependence['acquired']['__INDEPENDENT__']) //如果没有'__INDEPENDENT__'标签
                        && empty($dependence['acquired']) //也没有其他源特征记录
                    ) {
                        unset($this->$abilityContainer[$abilityName]);
                        \fLog("Ability {$abilityName} was removed from {$abilityContainer} since it was added by {$sourceFeatureType}.{$sourceFeatureName} which was removed.");
                    }
                }
            }
        }
        $this->update();
    }

    /**
     * 更新整个能力容器对象
     */
    public function update() {
        $modifier = array();
        $this->sustain = array();

        //遍历每个容器中的能力
        foreach ($this->containerRegistry as $container => $settings) {
            $this->sustain[$settings['sustain']] = 0;
            foreach ($this->$container as $abilityName => $record) {
                $data = xAbility::getData($abilityName);
                if($data !== false) {
                    //整理能力的加值修正
                    foreach ($data['data']['modifier'] as $scoreName => $mod) {
                        if(!isset($modifier['modifier'][$scoreName])) {$modifier['modifier'][$scoreName] = 0;}
                        $modifier['modifier'][$scoreName] += $mod;
                    }
                    //整理能力的系数修正
                    foreach ($data['data']['multiplier'] as $scoreName => $multiplier) {
                        if(!isset($modifier['multiplier'][$scoreName])) {$modifier['multiplier'][$scoreName] = 1;}
                        $modifier['multiplier'][$scoreName] *= $multiplier;
                    }
                    //整理能力的属性占用(只检查注册表中有配置过sustain的能力容器)
                    if(isset($settings['sustain'])) {
                        $this->sustain[$data['attr']] += $data['level'];
                    }
                }
            }
        }

        //更新父对象的属性
        if(!empty($modifier['modifier'])) {
            foreach ($modifier['modifier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('ability',$value);
            }
        }
        if(!empty($modifier['multiplier'])) {
            foreach ($modifier['multiplier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('abilityMultiplier',$value);
            }
        }
    }

    /**
     * 将容器中的记录导出
     * 
     * @return array
     * 返回的是一个数组
     */
    public function export() {
        $return = array();
        foreach ($this->containerRegistry as $containerName => $settings) {
            $return[$containerName] = $this->$containerName;
        }
        return $return;
    }

    /**
     * 检查能力是否已经在分类容器中存在。
     * 
     * @param string $abilityName
     * 检查能力的名称
     * 
     * @return array
     * 如果检查到在对应的能力容器中有，则返回一个包含这些能力容器名称的数组
     * 否则，返回一个空数组。
     */
    private function isAbilityExist (
        string $abilityName
    ) {
        $return = array();

        foreach ($this->containerRegistry as $container => $settings) {
            if(isset($this->$container[$abilityName])) {
                $return[$container] = $container;
            }
        }

        return $return;
    }
}
?>