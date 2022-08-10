<?php
namespace meshal\char;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal特征容器的类
################################################
use \meshal\xFeature as xFeature;

/**
 * 特征容器对象的数据结构
 * 
 * 特征容器注册表
 * $obj->containerRegistry = array(
 *  'containerType1' => array('limit' => 1), //limit表示该容器可以放几个此类特征
 *  'containerType2' => array('limit' => null), //limit=null表示该容器不限量
 *  ...
 * );
 * 
 * 特征容器和特征类型的映射表
 * $obj->containerMapping = array( 
 *  'containerName1' => 'featureType1',
 *  ...
 * );
 * 
 * 特征白名单，比如“人类的性别可选择男性或女性”，就需要在白名单中记录
 * $obj->available = array( 
 *  'featureType1' => array( //提供白名单的特征的类型
 *      'featureName1' => array( //提供白名单的特征的名称
 *          'availableType1' => array( //被解锁的特征的类型
 *              'availableName1', //被解锁的特征的名称
 *              'availableName2',
 *              ...
 *          ),
 *          'availableType2' => array(
 *              'availableName3',
 *              'availableName4',
 *              ...
 *          ),
 *      ),
 *      'featureName2' => array(...),
 *      ...
 *  ),
 *  'featureType2' => array(...),
 *  ...
 * );
 * 
 * 每个特征容器都是以下的数据格式
 * $obj->featureContainerName = array(
 *  'featureName1' => array( //特征的名称
 *      'acquired' => array(
 *          '__INDEPENDENT__' => true, //有这样的记录表示这个特征被独立赋予过
 *          'dependFeatureType1' => array( //这里则记录赋予这个特征的源特征类型
 *              'dependFeatureName1' => true, //赋予这个特征的源特征名称
 *              'dependFeatureName2' => true,
 *              ...
 *          ),
 *          ...
 *      )
 *  ),
 *  'featureName2' => array(
 *      '__INDEPENDENT__' => true
 *  ),
 *  ...
 * );
 */

/**
 * 这是一个特征容器类，用来存储和记录角色的特征
 * 
 * 常用方法
 * 
 * 向容器中添加特征
 * $obj->add(
 *  string $featureContainer, //向哪个特征容器添加，容器只接受对应类型的特征
 *  string $featureName, //要添加的特征名
 *  bool $skipAvailabilityCheck //略过白名单检查，直接添加
 * )
 * 
 * 从容器中移除特征
 * $obj->remove(
 *  string $featureContainer, //从哪个特征容器中移除
 *  string $featureName, //要移除的特征名
 *  bool $skipDependenceCheck //是否忽略特征依赖关系检查直接移除
 * )
 * 
 * 更新特征及其带来的影响
 * $obj->update()
 */
class xFeatures
{
    function __construct (\meshal\xChar &$char) {
        $this->parent = &$char;
        $this->db = &$this->parent->db;

        /**
         * 特征类型容器，都是用于存储不同类型特征的容器
         * 特征类型容器 => array(
         *    limit => int|null //可拥有的数量, 设为null表示可以拥有无限个
         * )
         */

        // $this->containerRegistry = $GLOBALS['meshal']['featureContainer'];

        //根据特征类型注册表，初始化每个特征容器（作为本对象的属性）
        foreach ($GLOBALS['meshal']['featureContainer'] as $container => $settings) {
            $this->$container = array();
        }

        /**
         * 初始化特征白名单：
         */
        $this->available = array();
    }

    /**
     * 增加特征到指定的容器中
     * 
     * @param string $featureContainer
     * 向哪个特征容器添加特征（参考__construct())
     * 
     * @param string $featureName
     * 添加的特征名
     * 
     * @param bool $skipAvailabilityCheck
     * 是否跳过检查特征白名单，强制添加（通常添加species时都应当强制添加）
     * 为false时，如果白名单中没有此特征，则不能添加本特征；为true时，即使白名单中没有此特征也可以加入
     * 默认为false。
     * 
     * @return boolean
     * 返回是否添加成功的状态
     */
    public function add (
        string $featureContainer,
        string $featureName,
        bool $skipAvailabilityCheck = false,
        string $dependType = null,
        string $dependName = null
    ) {
        //先检查是否在库里有特征配置
        $type = $this->getContainerType($featureContainer);
        $data = xFeature::getData($type, $featureName);
        if($data === false) {
            \fLog("Feature {$featureName} doesn't exist in library");
            return false;
        }

        //检查白名单中是否有此特征
        if($skipAvailabilityCheck == false) {
            if($this->isFeatureAvailable($type, $featureName) === false) {
                \fLog("Feature {$featureName} is not available");
                return false;
            }
        }

        //如果是独立添加特征
        if(is_null($dependType) && is_null($dependName)) {
            //检查是否有独立添加的同名特征
            if(isset($this->$featureContainer[$featureName]['acquired']['__INDEPENDENT__'])) {
                \fLog("Feature {$featureName}(__INDEPENDENT__) already exists in {$featureContainer}");
                return false;
            }

            //检查是否还有空位
            if($this->checkContainerSpace($featureContainer) === false) {
                \fLog("Feature container {$featureContainer} is full (limit = {$GLOBALS['meshal']['featureContainer'][$featureContainer]['limit']}), cannnot add {$featureName} into it.");
                return false;
            }
        }

        //如果这个特征是由另一个特征赋予的，则记录它们的关系
        if(!is_null($dependType) && !is_null($dependName)) {
            //这个特征通过其他特征添加，因此需要记录它的继承关系，以便以后用$this->remove()移除源特征时，也可以把这个特征移除
            $this->$featureContainer[$featureName]['acquired'][$dependType][$dependName] = true;
            \fLog("Feature {$featureName} was added into {$featureContainer} because of {$dependType}.{$dependName}");
        } else {
            //有这个标记的特征表示：不是通过其他特征自动添加的
            $this->$featureContainer[$featureName]['acquired']['__INDEPENDENT__'] = true;
            \fLog("Feature {$featureName} was added into {$featureContainer} independently");
        }

        //触发特征赋予能力的操作
        if($data['data']['addAbility']) {
            foreach ($data['data']['addAbility'] as $key => $abilityName) {
                $this->parent->abilities->add(
                    $abilityName,
                    false,
                    $data['type'],
                    $data['name']
                );
            }
        }

        $this->update();
        // 触发特征赋予特征的操作
        $this->recursiveAdd($type, $featureName);
        return true;
    }

    /**
     * 根据给到的源特征中的addFeature配置自动地添加特征
     * 
     * @param string $sourceType
     * 源特征的类型
     * 
     * @param string $sourceName
     * 源特征的名称
     */
    private function recursiveAdd(
        string $sourceType,
        string $sourceName
    ) {
        $data = xFeature::getData($sourceType, $sourceName); //取触发自动添加的源特征数据
        if($data !== false) {
            //遍历加载到的源特征中的addFeature配置
            if($data['data']['addFeature']) {
                foreach ($data['data']['addFeature'] as $addType => $addFeature) {
                    foreach ($addFeature as $k => $addName) {
                        $this->add(
                            $addType, 
                            $addName, 
                            true,
                            $sourceType,
                            $sourceName
                        );
                    }
                }
            }
        }
    }

    /**
     * 移除特征
     * 
     * @param string $featureContainer
     * 从哪个特征容器移除特征（参考__construct())
     * 
     * @param string $featureName
     * 移除特征的名称
     * 
     * @param bool $skipDependenceCheck
     * 是否检查特征的依赖关系，关闭的话会忽略依赖关系直接移除。
     * 默认为false。
     * 
     * @return bool
     * 返回是否移除成功的状态
     */
    public function remove(
        string $featureContainer,
        string $featureName,
        bool $skipDependenceCheck = false
    ) {
        $type = $this->getContainerType($featureContainer);
        $data = xFeature::getData($type, $featureName);

        //如果容器中不存在该特征，返回false
        if(!$this->$featureContainer[$featureName]) {
            \fLog("Feature {$featureName} doesn't exist in the feature list {$featureContainer}");
            return false;
        }

        //检查特征是否是某个源特征赋予的，如果是的话，除非是强制删除，否则不可删除
        if($skipDependenceCheck === false) {
            $dependence = $this->$featureContainer[$featureName]['acquired'];
            unset($dependence['__INDEPENDENT__']);
            //如果依赖关系不为空，则不移除
            if(!empty($dependence)) {
                \fLog("Unable to remove feature {$featureName} since it's added by other features");
                return false;
            }
        }

        //如果容器中存在这个特征，则移除
        unset($this->$featureContainer[$featureName]);
        \fLog("Feature {$featureName} was removed from {$featureContainer}");
        $this->parent->abilities->featureRemove($type, $featureName); //移除这个特征赋予的能力

        //移除由这个特征赋予的其他特征
        $this->recursiveRemove($type, $featureName);
        
        $this->update();
        return true;
    }

    /**
     * 根据给到的源特征，遍历所有容器，并将依赖源特征获得的特征移除。如果一个特征有多个源特征，或是独立获得的，则不会移除。
     * 
     * @param string $sourceType
     * 源特征的类型
     * 
     * @param string $sourceName
     * 源特征的名称
     */
    private function recursiveRemove (
        string $sourceType,
        string $sourceName
    ) {
        foreach ($GLOBALS['meshal']['featureContainer'] as $featureContainer => $settings) {
            foreach ($this->$featureContainer as $featureName => $dependence) {

                if(isset($dependence['acquired'][$sourceType][$sourceName])) {
                    unset($this->$featureContainer[$featureName]['acquired'][$sourceType][$sourceName]); //真实地从特征容器中的对应记录里移除
                    unset($dependence['acquired'][$sourceType][$sourceName]); //从查找用的临时数组中移除

                    if(empty($dependence['acquired'][$sourceType])) { //如果移除以后，上层数组里就没成员了，就应当将上层数组unset
                        unset($dependence['acquired'][$sourceType]); //真实地从特征容器中的对应记录里移除
                        unset($this->$featureContainer[$featureName]['acquired'][$sourceType]); //从查找用的临时数组中移除
                    }

                    if(
                        !isset($dependence['acquired']['__INDEPENDENT__']) //如果没有'__INDEPENDENT__'标签
                        && empty($dependence['acquired']) //也没有其他源特征记录
                    ) {
                        unset($this->$featureContainer[$featureName]); //移除该特征
                        \fLog("Feature {$featureName} was removed from {$featureContainer} since it was added by {$sourceType}.{$sourceName} which was removed.");
                        $featureType = $this->getContainerType($featureContainer);
                        $this->parent->abilities->featureRemove($featureType, $featureName); //移除该特征赋予的天赋能力
                    }
                }
            }
        }
    }

    /**
     * 更新整个特征容器对象
     */
    public function update () {
        $modifier = array();
        $this->available = array(); //重设特征白名单
        foreach ($GLOBALS['meshal']['featureContainer'] as $featureContainer => $settings) { //遍历注册表中的每个特征容器名
            $featureType = $this->getContainerType($featureContainer); //获取每个容器映射的特征类型
            foreach ($this->$featureContainer as $featureName => $dependence) { //遍历每个特征容器中的特征
                //如果特征在较弱级容器中，那么须检查是否在较强级容器中有相同特征，如果有则略过处理
                if(!is_null($GLOBALS['meshal']['featureContainer'][$settings['major']])) {
                    if($this->isFeatureExist(
                        $settings['major'],
                        $featureName
                    ) === true)
                    break;
                }

                $data = xFeature::getData($featureType, $featureName); //取特征数据
                if($data !== false) {
                    //整理特征的加值修正
                    foreach ($data['data']['modifier'] as $scoreName => $mod) {
                        if(!isset($modifier['modifier'][$scoreName])) {$modifier['modifier'][$scoreName] = 0;}
                        $modifier['modifier'][$scoreName] += (is_null($mod) || $mod == '') ? 0 : $mod;
                    }
                    //整理特征的系数修正
                    foreach ($data['data']['multiplier'] as $scoreName => $multiplier) {
                        if(!isset($modifier['multiplier'][$scoreName])) {$modifier['multiplier'][$scoreName] = 1;}
                        $modifier['multiplier'][$scoreName] *= (is_null($multiplier) || $multiplier == '') ? 1 : $multiplier;
                    }
                    //添加特征的白名单
                    if($data['data']['availableFeature']) {
                        $this->available[$data['type']][$data['name']] = $data['data']['availableFeature'];
                    }
                    //整理特征的实力修正
                    $modifier['strength'] += $data['strength'];
                    //整理特征的装备槽
                    if(!empty($data['data']['equipmentSlots'])) {
                        foreach ($data['data']['equipmentSlots'] as $containerCode => $slots) {
                            if(!isset($modifier['equipmentSlots'][$containerCode])) {$modifier['equipmentSlots'][$containerCode] == 0;}
                            $modifier['equipmentSlots'][$containerCode] += (is_null($slots) || $slots == '') ? 0 : $slots;
                        }
                    }
                }
            }
        }

        //更新父对象的属性
        if(!empty($modifier['modifier'])) {
            foreach ($modifier['modifier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('feature', $value);
            }
        }
        if(!empty($modifier['multiplier'])) {
            foreach ($modifier['multiplier'] as $scoreName => $value) {
                $this->parent->$scoreName->set('featureMultiplier', $value);
            }
        }
        if($modifier['strength']) {
            $this->parent->strength->set('feature', $modifier['strength']);
        }
        if(!empty($modifier['equipmentSlots'])) {
            foreach ($modifier['equipmentSlots'] as $containerCode => $value) {
                $this->parent->inventory->modSlots($containerCode, $value);
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
        foreach ($GLOBALS['meshal']['featureContainer'] as $containerName => $settings) {
            $return[$containerName] = $this->$containerName;
        }
        return $return;
    }

    /**
     * 根据输入的$containerType，获取实际的类型映射。
     * 比如$this->add('perceptionPoor', 'someFeature')时，应当从数据库中取type='perception'的特征，此时就需要用这个方法来获取映射的type。
     * 
     * @param string $containerType
     * 输入一个特征容器名。
     * 
     * @param bool $reverse
     * 是否反向获取（比如已知 'perception', 检查映射到它的 'perceptionPoor'）。为false时不做反向获取；为true时做反向获取。
     * 默认为 false
     * 
     * @return string
     * 返回映射的特征容器名。
     */
    private function getContainerType (
        string $containerType
    ) {
        return $GLOBALS['meshal']['featureContainer'][$containerType]['type'];
    }

    /**
     * 根据给定的特征容器名，查找这个特征的较弱特征容器（比如给定perception, 会找到perceptionPoor）
     * 
     * @param string $containerType
     * 要查找的特征容器名
     * 
     * @return string|false
     * 如果这个特征没有较弱特征容器，返回false
     * 否则返回较弱特征容器名
     */
    public static function getPoorContainer(
        string $containerType
    ) {
        if(!is_null($GLOBALS['meshal']['featureContainer'][$containerType]['poor'])) {
            return $GLOBALS['meshal']['featureContainer'][$containerType]['poor'];
        } else {
            return false;
        }
    }

    /**
     * 检查一个给定的特征容器中是否有空位
     * 
     * @param string $featureContainer
     * 特征容器的名称
     * 
     * @return bool
     * 有空位返回true，反之返回false
     */
    public function checkContainerSpace(
        string $featureContainer
    ) {
        //检查特征容器中是否有空位
        if(
            $GLOBALS['meshal']['featureContainer'][$featureContainer]['limit'] !== null 
            && count($this->$featureContainer) >= $GLOBALS['meshal']['featureContainer'][$featureContainer]['limit']
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查特征是否已经在分类容器中存在。
     * 同时也会检查分类映射的容器中是否存在该特征。比如：$this->isFeatureExist('perceptionPoor') 同时也该检查 perception 分类容器
     * 
     * @param string $featureType
     * 检查是否在某个type的特征容器中
     * 
     * @param string $featureName
     * 检查特征的名称
     * 
     * @return bool
     * 如果该特征存在，返回true；反之返回false
     */
    public function isFeatureExist (
        $containerName,
        string $featureName
    ) {
        if($this->$containerName[$featureName]) {
            \fLog("{$featureName} exists in container {$containerName}");
            return true;
        } else {
            \fLog("{$featureName} doesn't exist in container {$containerName}");
            return false;
        }
    }

    /**
     * 检查特征是否在白名单中
     * 
     * @param string $featureType
     * 被检查特征的类型
     * 
     * @param string $featureName
     * 被检查特征的名称
     * 
     * @return bool
     * 如果特征在白名单中，返回true；否则返回false。
     */
    private function isFeatureAvailable (
        string $featureType,
        string $featureName
    ) {
        $type = $this->getContainerType($featureType);
        
        //遍历白名单，如果找到特征，则返回true
        foreach ($this->available as $sourceType) {
            if(
                !is_null($sourceType) 
                && !empty($sourceType)
            ) {
                foreach ($sourceType as $sourceName) {
                    if(
                        $sourceName[$type]
                        && !is_null($sourceName[$type])
                        && !empty($sourceName[$type])
                    ) {
                        if(array_search($featureName, $sourceName[$type]) !== false) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }


    /**
     * 查询白名单，找到所有在白名单中的某个类型的特征
     * 
     * @param string $featureType
     * 要查询的特征类型
     * 
     * @return array
     * 如果在白名单中找到了此类特征，那么返回一个数组，数组中的键名是位于白名单中的特征
     * 如果没有找到，则返回空数组。
     */
    public function availability(
        string $featureType
    ) {
        //没有注册过传递的特征类型，则返回false
        if(!isset($GLOBALS['meshal']['featureType'][$featureType])) {
            \fLog('The featureType is not registered');
            return array();
        }

        //遍历available，找到符合类型的特征并且汇总返回（键名是白名单特征）
        $result = array();
        foreach ($this->available as $sourceType => $sourceTypeData) {
            foreach ($sourceTypeData as $feature => $featureUnlocks) {
                if(!is_null($featureUnlocks[$featureType])) 
                $result = array_merge($result, array_flip($featureUnlocks[$featureType]));
            }
        }
        return $result;
    }
}

?>