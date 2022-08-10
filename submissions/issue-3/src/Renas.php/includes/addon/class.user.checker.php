<?php
namespace user;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供基于用户维度做检查的类
################################################

/**
 * 常用方法
 */

class xChecker
{
    // function __construct(
    //     \meshal\xItem &$parent
    // ) {
    //     $this->parent = $parent;
    // }

    /**
     * 检查用户拥有的角色数量大于某个值
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @return bool
     */
    public static function survivorsMoreThan (
        int $uid,
        $number
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        global $db;

        $count = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$uid}'"
            )
        );

        if(\bccomp($count, $number) == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查用户拥有的角色数量小于某个值
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @return bool
     */
    public static function survivorsLessThan (
        int $uid,
        $number
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        global $db;

        $count = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$uid}'"
            )
        );

        if(\bccomp($count, $number) == -1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查用户拥有的角色数量等于某个值
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @return bool
     */
    public static function survivorsEqual (
        int $uid,
        $number
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        global $db;

        $count = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$uid}'"
            )
        );

        if(\bccomp($count, $number) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查用户拥有的空闲角色数量大于某个值
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @return bool
     */
    public static function restingSurvivorsMoreThan(
        int $uid,
        $number
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        global $db;

        $count = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$uid}'",
                "`stat` is null"
            )
        );

        if(\bccomp($count, $number) == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查用户拥有的空闲角色数量小于某个值
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @return bool
     */
    public static function restingSurvivorsLessThan (
        int $uid,
        $number
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        global $db;

        $count = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$uid}'",
                "`stat` is null"
            )
        );

        if(\bccomp($count, $number) == -1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查用户拥有的空闲角色数量等于某个值
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|float $number
     * 用于比较的值
     * 
     * @return bool
     */
    public static function restingSurvivorsEqual (
        int $uid,
        $number
    ) {
        if(!is_numeric($number)) {
            \fLog('Error: $number is not numeric.');
            return false;
        }

        global $db;

        $count = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$uid}'",
                "`stat` is null"
            )
        );

        if(\bccomp($count, $number) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function facilityLevelGreaterThan(
        int $uid,
        string $facilityName,
        int $benchmark
    ) {
        if(!is_numeric($benchmark)) {
            \fLog('Error: $benchmark is not numeric.');
            return false;
        }

        global $db;


    }
}
?>