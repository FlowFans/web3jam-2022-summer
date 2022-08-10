<?php
namespace meshal\adventure;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#记录冒险日志的类
################################################

/*
常用方法

进行一次使用属性的尝试，这个方法总是用对应属性最高的角色进行尝试
attemptAttrHighest(
    int $difficulty = 1, //尝试难度
    $scoreName = null, //属性名，为空时使用默认掷骰
    $scoreProperty = null, //用于取值的属性部分
    $modifier = 0, //掷骰数量的修正（加数）
    $multiplier = 1 //掷骰数量的修正（系数）
)

进行一次使用属性的尝试，这个方法总是用对应属性最低的角色进行尝试
attemptAttrLowest(
    int $difficulty = 1, //尝试难度
    $scoreName = null, //属性名，为空时使用默认掷骰
    $scoreProperty = null, //用于取值的属性部分
    $modifier = 0, //掷骰数量的修正（加数）
    $multiplier = 1 //掷骰数量的修正（系数）
)

进行一次使用属性的尝试，这个方法会随机选择一个角色进行尝试
attemptRandomMember(
    int $difficulty = 1, //尝试难度
    $scoreName = null, //属性名，为空时使用默认掷骰
    $scoreProperty = null, //用于取值的属性部分
    $modifier = 0, //掷骰数量的修正（加数）
    $multiplier = 1 //掷骰数量的修正（系数）
)

*/

class xLogger
{
    function __construct(
        \meshal\xAdventure &$parent,
        $lang = NULL
    ) {
        global $db;
        $this->db = $db;

        $this->parent = $parent; //父级冒险对象
        $this->current = null; //当前log条目的计步器
        $this->log = array(); //存储所有log内容

        //语言代码设置
        if(is_null($lang)) {
            $this->langCode = $GLOBALS['deploy']['lang'];
        } else {
            $this->langCode = $lang;
        }
    }

    /**
     * 创建一个scene，也就是场景，是一组event的容器。相当于在这个场景中发生的所有事，都会记录在一组记录中。
     * 
     * @param string $sceneName
     * 场景名称
     */
    public function addScene(
        string $sceneName
    ) {
        $this->log[] = array(
            'scene' => $sceneName,
            'events' => array()
        );
        
        if(is_null($this->current)) {
            $this->current = 0;
        } else {
            $this->current ++;
        }
    }

    /**
     * 在当前场景中添加一条事件记录。
     * 
     * @param string $eventCode
     * 事件代码
     * 
     * @param array $data
     * 事件资料，根据不同事件，细节中的数据也会不同。
     */
    public function addEvent(
        string $eventCode,
        array $data
    ) {
        $this->log[$this->current]['events'][] = array(
            'event' => $eventCode,
            'data' => $data
        );
    }

    /**
     * 返回json格式的log
     */
    public function export() {
        return json_encode($this->log);
    }
}
?>