<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal掷骰的类
################################################

class xDice
{
    function __construct() {
        $this->dice = array(
            //定义骰子随机范围
            'min' => 1,
            'max' => 6,

            //定义骰面数字
            'numbers' => array(
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6
            ),
            //定义骰面点数
            'points' => array(
                1 => 1,
                2 => 2,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0
            )
        );

        //用于存储最后一次掷骰信息
        $this->lastRoll = array();
    }

    /**
     * 获取掷骰点数
     * 
     * @param int $dice
     * 掷骰数量
     * 
     * @return int
     * 仅返回点数结果
     */
    public function pt(
        int $dice = 3
    ) {
        $return = $this->ptDetail($dice);
        return $return['result'];
    }

    /**
     * 获取掷骰骰面
     * 
     * @param int $dice
     * 掷骰数量
     * 
     * @return int
     * 仅返回骰面结果
     */
    public function num(
        int $dice = 3
    ) {
        $return = $this->numDetail($dice);
        return $return['result'];
    }

    /**
     * 获取掷骰点数和掷骰细节
     * 
     * @param int $dice
     * 掷骰数量
     * 
     * @return array
     * 返回一个数组，array(
     *  int 'result', //结果的数字
     *  array 'detail' //每个骰子的结果，数组形式。每个元素的键值就是单个骰子的结果
     * )
     */
    public function ptDetail(
        int $dice = 3
    ) {
        $result = $this->roll($dice);
        $return = array(
            'result' => $result['result']['point'],
            'detail' => $result['detail']['point'],
            'raw' => $result['detail']['raw']
        );
        return $return;
    }

    /**
     * 获取掷骰骰面和掷骰细节
     * 
     * @param int $dice
     * 掷骰数量
     * 
     * @return array
     * 返回一个数组，array(
     *  int 'result', //结果的数字
     *  array 'detail' //每个骰子的结果，数组形式。每个元素的键值就是单个骰子的结果
     * )
     */
    public function numDetail(
        int $dice = 1
    ) {
        $result = $this->roll($dice);
        $return = array(
            'result' => $result['result']['number'],
            'detail' => $result['detail']['number'],
            'raw' => $result['detail']['raw']
        );
        return $return;
    }

    /**
     * 进行一次掷骰（一次掷出多个骰子）
     * 
     * @param int $dice
     * 用于掷多少枚骰子
     * 
     * @return
     * 返回掷骰结果（完整细节）
     * array(
     *      'detail' => array(
     *          'raw' => array(), //掷骰的原始随机数
     *          'number' => array(), //掷骰的骰面数字
     *          'point' => array(), //掷骰的点数
     *      ),
     * 
     *      'result' => array(
     *          'number` => int, //加总的骰面数字
     *          'point' => int, //加总的点数
     *      ),
     * );
     */
    public function roll(
        int $dice
    ) {
        $result = array();

        if($dice == 0) { //如果传入的是0，则直接返回空结果
            $result['detail']['raw'] = array();
            $result['detail']['number'] = array();
            $result['detail']['point'] = array();
            $result['result']['number'] = 0;
            $result['result']['point'] = 0;
            $this->lastRoll = $result;
            return $result;
        }

        $debugTimer = microtime(TRUE);

        for ($i=0; $i < $dice; $i++) { 
            //生成随机数
            $raw = mt_rand($this->dice['min'],$this->dice['max']);

            $result['detail']['raw'][] = $raw; //保存随机数
            $result['detail']['number'][] = $this->dice['numbers'][$raw]; //保存骰面数字
            $result['detail']['point'][] = $this->dice['points'][$raw]; //保存骰面点数
        }

        $result['result']['number'] = array_sum($result['detail']['number']);
        $result['result']['point'] = array_sum($result['detail']['point']);
        $this->lastRoll = $result; //更新最后一次掷骰的信息

        $debugTimer = microtime(TRUE) - $debugTimer;
        \fLog('rolled in : '.$debugTimer.' ms, result: '.fDump($this->lastRoll));

        return $result;
    }

}

?>