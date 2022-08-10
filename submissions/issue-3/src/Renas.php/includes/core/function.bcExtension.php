<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供BC Math的扩展方法
################################################

/**
 * 输入一个任意精度的数值(string)，返回一个指定精度的结果(string)
 * 
 * @param string $num
 * 输入的数值
 * 
 * @param int $decimals
 * 返回的精度
 */
function fRound(
    string $num,
    int $decimals = -1
) {
    if($decimals < 0) $decimals = $GLOBALS['setting']['bcmath']['decimalRound'];
    return rtrim(rtrim(
        bcadd($num, 0, $decimals),
        0
    ), '.');
}

/**
 * 用bcadd进行加法计算，并舍去小数最后的0
 * 
 * @param string|int|float $numA
 * 第一个加数
 * 
 * @param string|int|float $numB
 * 第二个加数
 * 
 * @return string
 * 返回计算结果
 */
function fAdd(
    $numA,
    $numB,
    int $decimals = -1
) {
    if($decimals < 0) $decimals = $GLOBALS['setting']['bcmath']['decimals'];
    return rtrim(rtrim(
        bcadd($numA, $numB, $decimals),
        0
    ), '.');
}

/**
 * 用bcsub进行减法计算，并舍去小数最后的0
 * 
 * @param string|int|float $numA
 * 被减数
 * 
 * @param string|int|float $numB
 * 减数
 * 
 * @return string
 * 返回计算结果
 */
function fSub(
    $numA,
    $numB,
    int $decimals = -1
) {
    if($decimals < 0) $decimals = $GLOBALS['setting']['bcmath']['decimals'];
    return rtrim(rtrim(
        bcsub($numA, $numB, $decimals),
        0
    ), '.');
}

/**
 * 用bcmul进行乘法计算，并舍去小数最后的0
 * 
 * @param string|int|float $numA
 * 被乘数
 * 
 * @param string|int|float $numB
 * 乘数
 * 
 * @return string
 * 返回计算结果
 */
function fMul(
    $numA,
    $numB,
    int $decimals = -1
) {
    if($decimals < 0) $decimals = $GLOBALS['setting']['bcmath']['decimals'];
    return rtrim(rtrim(
        bcmul($numA, $numB, $decimals),
        0
    ), '.');
}

/**
 * 用bcdiv进行除法计算，并舍去小数最后的0
 * 
 * @param string|int|float $numA
 * 被除数
 * 
 * @param string|int|float $numB
 * 除数
 * 
 * @return string
 * 返回计算结果
 */
function fDiv(
    $numA,
    $numB,
    int $decimals = -1
) {
    if($numA == 0 || $numB == 0) return 0;
    if($decimals < 0) $decimals = $GLOBALS['setting']['bcmath']['decimals'];
    if($numA == $numB) return 1;
    return rtrim(rtrim(
        bcdiv($numA, $numB, $decimals),
        0
    ), '.');
}

/**
 * 用bcpow进行指数计算，并舍去小数最后的0
 * 
 * @param string|int|float $numA
 * 底数
 * 
 * @param string|int|float $numB
 * 指数
 * 
 * @return string
 * 返回计算结果
 */
function fPow(
    $numA,
    $numB,
    int $decimals = -1
) {
    if($decimals < 0) $decimals = $GLOBALS['setting']['bcmath']['decimals'];
    return rtrim(rtrim(
        bcpow($numA, $numB, $decimals),
        0
    ), '.');
}

/**
 * 求取多个数字的平均数，并舍去小数最后的0
 * 
 * @param string|int|float $array
 * 每个成员一个数字
 * 
 * @return string
 * 返回计算结果
 */
function fAverage(
    array $array = array(),
    int $decimals = -1
) {
    if(empty($array)) return 0;
    if($decimals < 0) $decimals = $GLOBALS['setting']['bcmath']['decimals'];
    $sum = 0;
    $counts = count($array);
    foreach($array as $k => $num) {
        $sum = bcadd($sum, $num, $decimals);
    }
    return rtrim(rtrim(
        bcdiv($sum, $counts, $decimals),
        0
    ), '.');
}

/**
 * 获取一个浮点数的小数部分
 * 
 * @param string|float $number
 * 传入的数字
 * 
 * @return string
 * 返回一个字符串格式的小数
 */
function fGetDigit(
    $number
) {
    return '0.'.explode('.', $number)[1];
}
?>