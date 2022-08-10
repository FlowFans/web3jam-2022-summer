<?php
namespace meshal\adventure;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal的Encounter类
################################################

// interface xEncounterInterface {
//     public function execute();
// }

class xEncounter
{
    function __construct(
        \meshal\xAdventure &$parent
    ) {
        $this->parent = $parent;
        // $this->logger = $this->parent->logger;
        $this->name = null; //遭遇名称
        // $this->type = null; //遭遇类型
        $this->duration = 0; //遭遇花费的时间
        $this->intensity = 0; //遭遇的挑战难度
        $this->probability = null; //随机概率修正
        $this->lastUpdate = null; //最后更新时间
        $this->creator = new \user\xAdapter; //创作者信息
        $this->data = array(); //遭遇数据
        $this->loot = array(); //遭遇产出（仅用于信息查询）

        $this->db = $parent->db; //数据库对象
        $this->dice = $parent->dice; //掷骰器
        $this->team = $parent->team; //从父对象处继承这场遭遇的队伍对象
        $this->checker = $parent->checker; //引用父级对象的检查器
        $this->executor = $parent->executor; //引用冒险结果执行器
    }

    /**
     * 加载遭遇数据
     * 
     * @param string $encounterName
     * 要加载的遭遇名
     */
    public function load(
        string $encounterName
    ) {
        $arr = self::getData($encounterName);
        if($arr === false) return false;

        $this->name = $arr['name'];
        $this->data = $arr['data'];
        $this->loot = $arr['loot'];
        $this->intensity = $arr['intensity'];
        $this->duration = $arr['duration'];
        $this->probability = $arr['probability'];
        ### 加载数据待补完
        return true;
    }

    /**
     * 从数据库中获取遭遇的资料
     * 
     * @param string $encounterName
     * 查询的遭遇名称
     * 
     * @return mixed
     * 如果没有查到，返回false；
     * 如果查到了记录，以数组返回包含该遭遇的数据
     */
    public static function getData (
        string $encounterName
    ) {
        global $db;
        $arr = $db->getArr(
            'encounters',
            array(
                "`name` = '{$encounterName}'"
            ),
            NULL,
            1
        );
        if($arr === false) {
            \fLog("{$encounterName} doesn't exist in library");
            return false;
        }

        $return = array(
            'fullname' => "meshal.encounter.{$arr[0]['name']}",
            'name' => $encounterName,
            'duration' => $arr[0]['duration'],
            'intensity' => $arr[0]['intensity'],
            'data' => json_decode($arr[0]['data'], true),
            'loot' => json_decode($arr[0]['loot'], true),
            'probability' => $arr[0]['probabilityModifier']
        );

        return $return;
    }

    /**
     * 向parent冒险对象添加冒险日志
     * 
     * @param string $event
     * 事件代码
     * 
     * @param array $detail
     * 事件详情的数据
     */
    public function log(
        string $event,
        string $desc,
        array $detail
    ) {
        $this->parent->log(
            $event, //检查的方法名
            $desc, //描述
            $detail //检查的细节
        );
    }

    /**
     * 执行一个遭遇
     * 这是一个通用执行方法，当配置发生变动时，此处的代码也要进行迭代
     */
    public function execute() {
        // $this->parent->logger->addScene($this->name);
        $logBuff = array();
        //检查触发前提 ###技术债

        //进行前提检查(All部分)
        $checkAll = 1;
        if(!empty($this->data['checkAll'])) {
            foreach($this->data['checkAll'] as $k => $params) {
                $method = array_shift($params);
                if(!is_string($method)) {
                    \fLog("Error: {$this->name} \$method is not a string");
                    \fLog(\fDump($method));
                    // fPrint($this->name);
                    // fPrint($method);
                }

                if(method_exists($this->checker, $method)) {
                    $check = $this->checker->$method(...$params); //运行检查器
                    $checkAll *= $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAll
                    // $this->log("encounter.checkAll.{$method}", $this->name, $check['log']);
                    $logBuff['checkAll'][] = array(
                        'method' => $method,
                        'detail' => $check['detail']
                    );
                } else {
                    \fLog("Method {$method} doesn't exist in the checker");
                }
            }
        }
        \fLog("checkAll = {$checkAll}");

        //进行前提检查(Any部分)
        if(!empty($this->data['checkAny'])) {
            $checkAny = 0;
            foreach ($this->data['checkAny'] as $k => $params) {
                // if($checkAny > 0) break; //提升性能
                $method = array_shift($params);
                if(!is_string($method)) {
                    \fLog("Error: {$this->name} \$method is not a string");
                    \fLog(\fDump($method));
                    // fPrint($this->name);
                    // fPrint($method);
                }

                if(method_exists($this->checker, $method)) {
                    $check = $this->checker->$method(...$params); //运行检查器
                    $checkAny += $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAny
                    // $this->log("encounter.checkAny.{$method}", $this->name, $check['log']);
                    $logBuff['checkAny'][] = array(
                        'method' => $method,
                        'detail' => $check['detail']
                    );
                } else {
                    \fLog("Method {$method} doesn't exist in the checker");
                }
            }
        } else {
            $checkAny = 1;
        }
        \fLog("checkAny = {$checkAny}");
        
        //检查通过，根据success配置，执行结果
        if($checkAll * $checkAny > 0) {
            $logBuff['summary'] = true;
            // $this->parent->logger->addEvent('check', $logBuff);
            if(!empty($this->data['success'])) {
                // $logBuff = array();
                foreach ($this->data['success'] as $k => $params) {
                    $method = array_shift($params);
                    if(!is_string($method)) {
                        \fLog("Error: {$this->name} \$method is not a string");
                        \fLog(\fDump($method));
                        // fPrint($this->name);
                        // fPrint($method);
                    }

                    if(method_exists($this->executor, $method)) {
                        $result = $this->executor->$method(...$params);
                        // $this->log("encounter.success.{$method}", $this->name, $result['log']);
                        // $this->parent->logger->addEvent($method, $result);
                        $logBuff['execute'][] = array(
                            'method' => $method,
                            'detail' => $result
                        );
                    } else {
                        \fLog("Method {$method} doesn't exist in the executor");
                    }
                }
            }
            $this->parent->logger->addEvent(
                'encounterEvent',
                $logBuff
            );
            return true;
        } 
        //检查失败，根据failure配置，执行结果
        else { 
            $logBuff['summary'] = false;
            // $this->parent->logger->addEvent('check', $logBuff);
            if(!empty($this->data['failure'])) {
                // $logBuff = array();
                foreach ($this->data['failure'] as $k => $params) {
                    $method = array_shift($params);
                    if(!is_string($method)) {
                        \fLog("Error: {$this->name} \$method is not a string");
                        \fLog(\fDump($method));
                        // fPrint($this->name);
                        // fPrint($method);
                    }

                    if(method_exists($this->executor, $method)) {
                        $result = $this->executor->$method(...$params);
                        // $this->log("encounter.failure.{$method}", $this->name, $result['log']);
                        // $this->parent->logger->addEvent($method, $result);
                        $logBuff['execute'][] = array(
                            'method' => $method,
                            'detail' => $result
                        );
                    } else {
                        \fLog("Method {$method} doesn't exist in the executor");
                    }
                }
            }
            $this->parent->logger->addEvent(
                'encounterEvent',
                $logBuff
            );
            return false;
        }
    }
}
?>