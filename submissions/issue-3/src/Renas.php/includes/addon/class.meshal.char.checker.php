<?php
namespace meshal\char;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供对角色做检查的类
################################################

/**
 * 常用方法
 * 
 * 角色属性必须大于给定值
 * scoreGreaterThan(
 *  int $charId, //角色id
 *  int|float $number, //比较的属性值
 *  string $scoreName, //比较的属性代码
 *  string $scoreProperty //比较的属性构成部分
 * )
 * 
 * 角色属性必须小于给定值
 * scoreLessThan(
 *  int $charId, //角色id
 *  int|float $number, //比较的属性值
 *  string $scoreName, //比较的属性代码
 *  string $scoreProperty //比较的属性构成部分
 * )
 * 
 * 角色属性必须等于给定值
 * scoreEqual(
 *  int $charId, //角色id
 *  int|float $number, //比较的属性值
 *  string $scoreName, //比较的属性代码
 *  string $scoreProperty //比较的属性构成部分
 * )
 */

class xChecker
{
    // function __construct(
    //     \meshal\xItem &$parent
    // ) {
    //     $this->parent = $parent;
    // }

    /**
     * 检查角色属性是否大于给定的值
     * 
     * @param int $charId
     * 角色Id
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @param string $scoreName
     * 属性名（参考char->score)
     * 
     * @param string $scoreProperty = 'current'
     * 属性的构成部分
     * 
     * @return bool
     */
    public static function scoreGreaterThan (
        int $charId,
        $number,
        string $scoreName,
        string $scoreProperty = 'current'
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        $char = new \meshal\char\xAdapter;
        $char->load($charId);

        if(\bccomp($char->data[$scoreName][$scoreProperty], $number) == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查角色属性是否小于给定的值
     * 
     * @param int $charId
     * 角色Id
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @param string $scoreName
     * 属性名（参考char->score)
     * 
     * @param string $scoreProperty = 'current'
     * 属性的构成部分
     * 
     * @return bool
     */
    public static function scoreLessThan (
        int $charId,
        $number,
        string $scoreName,
        string $scoreProperty
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        $char = new \meshal\char\xAdapter;
        $char->load($charId);

        if(\bccomp($char->data[$scoreName][$scoreProperty], $number) == -1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查角色属性是否大于给定的值
     * 
     * @param int $charId
     * 角色Id
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @param string $scoreName
     * 属性名（参考char->score)
     * 
     * @param string $scoreProperty = 'current'
     * 属性的构成部分
     * 
     * @return bool
     */
    public static function scoreEqual (
        int $charId,
        $number,
        string $scoreName,
        string $scoreProperty
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        $char = new \meshal\char\xAdapter;
        $char->load($charId);

        if(\bccomp($char->data[$scoreName][$scoreProperty], $number) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
?>