<?php
namespace user;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供用户拥有效果的类
################################################

/**
 * 这是一个用户相关功能和信息的适配器类
 * 
 * 常用方法
 */
class xEfx {
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
        $this->initialise();
    }

    private function initialise() {
        /**
         * 修正器变量集合
         */
        $this->modifier = array(
            'survivorSlots' => 0, //幸存者槽位
            'adventuresRenewal.base' => 0, //每次刷新冒险的数量
            'adventuresRenewal.randomMin' => 0, //每次刷新冒险的随机量上限
            'adventuresRenewal.randomMax' => 0, //每次刷新冒险的随机量上限
        );
    }

    /**
     * 加载所有效果
     */
    public function load() {
        $this->initialise();
        $load = $this->getAllEfx();

        if(!empty($load)) {
            foreach($load as $k => $efx) { //逐个执行效果方法
                $method = 'efx_'.$efx[0];
                if(method_exists($this, $method)) {
                    $param = $efx;
                    unset($param[0]); //把第0个参数（也就是方法名）unset掉，保留余下的作为方法参数
                    $this->$method(...$param);
                }
            }
        }

        // fPrint($this->modifier);
    }

    /**
     * 取所有效果
     * 
     * @return array
     * 返回一个数组，每个成员是一条效果
     */
    public function getAllEfx () {
        $currentTime = time();
        $query = $this->db->getArr(
            'user_efx',
            array(
                "`uid` = '{$this->parent->uid}'",
                // "(`expire` > '{$currentTime}' OR `expire` is null OR `expire` = '')"
                "`expire` is null OR `expire` > '{$currentTime}'"
            )
        );

        $return = array();
        if($query !== false) {
            foreach ($query as $k => $data) {
                $return[] = json_decode($data['data']);
            }
        }

        return $return;
    }

    /**
     * 取某个类型下的所有效果
     * 
     * @param string $type
     * 查询的类型
     * 
     * @return array
     * 返回一个数组，每个成员是一条效果
     */
    public function getEfxByType (
        string $type
    ) {
        $currentTime = time();
        $query = $this->db->getArr(
            'user_efx',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`type` = '{$type}'",
                "`expire` > '{$currentTime}'"
            )
        );

        $return = array();
        if($query !== false) {
            foreach ($query as $k => $data) {
                $return[] = json_decode($data);
            }
        }

        return $return;
    }

    ################################################
    # 以下是效果方法
    ################################################
    
    /**
     * 向修正器某个属性做增加
     * 
     * @param string $modName
     * 属性名
     * 
     * @param string|int|float $number
     * 修改数量
     */
    public function efx_add(
        string $modName,
        $number
    ) {
        $this->modifier[$modName] = \fAdd($this->modifier[$modName], $number);
    }

    /**
     * 向修正器某个属性做减少
     * 
     * @param string $modName
     * 属性名
     * 
     * @param string|int|float $number
     * 修改数量
     */
    public function efx_sub(
        string $modName,
        $number
    ) {
        $this->modifier[$modName] = \fSub($this->modifier[$modName], $number);
    }

    /**
     * 向修正器某个属性做修改（等效于efx_add())
     * 
     * @param string $modName
     * 属性名
     * 
     * @param string|int|float $number
     * 修改数量
     */
    public function efx_mod(
        string $modName,
        $number
    ) {
        $this->modifier[$modName] = \fAdd($this->modifier[$modName], $number);
    }
}