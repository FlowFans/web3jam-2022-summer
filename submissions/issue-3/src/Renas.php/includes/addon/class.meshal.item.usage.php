<?php
namespace meshal\item;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal物品使用的类
################################################

/**
 * 这里的所有方法标准中，都包含基本的参数
 * @param int $uid
 * 触发此效果的用户id
 * 
 * @param string $itemName
 * 触发此效果的物品code
 * 
 * @param int $charId
 * 修改目标的角色Id
 */

/**
 * 常用方法
 */

class xUsage
{
    function __construct(
        \meshal\xItem &$parent
    ) {
        $this->parent = $parent;
    }

    /**
     * 增加角色的属性
     * 
     * @param int|float $amount
     * 修改属性的数量
     * 
     * @param string $scoreName
     * 属性名（参考char->score)
     * 
     * @param string $scoreProperty = 'current'
     * 属性的构成部分
     * 
     * @return bool
     */
    public static function addScore (
        int $uid,
        string $itemName,
        int $charId,
        
        $amount,
        string $scoreName,
        string $scoreProperty = 'current'
    ) {
        if(!is_numeric($amount)) {
            \fLog('Error: $amount is not numeric.');
            return false;
        }
        $char = new \meshal\xChar;
        $char->load($charId);

        # 如果是对基础部分做操作，则需要同时修改实力
        if($scoreProperty == 'base') {
            $strength = \meshal\char\calcStrength($scoreName, $char->$scoreName->base, $char->$scoreName->base + $amount);
            $char->strength->add('base', $strength);
        }
        
        //记录修改
        $char->event($uid, 'itemModScore', array('attr' => $scoreName, 'property' => $scoreProperty, 'value' => $amount, 'item' => $itemName));
        $char->$scoreName->add($scoreProperty, $amount);
        $char->save();

        return true;
    }

    /**
     * 减少角色的属性
     * 
     * @param int|float $amount
     * 修改属性的数量
     * 
     * @param string $scoreName
     * 属性名（参考char->score)
     * 
     * @param string $scoreProperty = 'current'
     * 属性的构成部分
     * 
     * @return bool
     */
    public static function subScore (
        int $uid,
        string $itemName,
        int $charId,
        
        $amount,
        string $scoreName,
        string $scoreProperty = 'current'
    ) {
        if(!is_numeric($amount)) {
            \fLog('Error: $amount is not numeric.');
            return false;
        }
        $char = new \meshal\xChar;
        $char->load($charId);

        # 如果是对基础部分做操作，则需要同时修改实力
        if($scoreProperty == 'base') {
            $strength = \meshal\char\calcStrength($scoreName, $char->$scoreName->base - $amount, $char->$scoreName->base);
            $char->strength->sub('base', $strength);
        }
        
        //记录修改
        $char->event($uid, 'itemModScore', array('attr' => $scoreName, 'property' => $scoreProperty, 'value' => - $amount, 'item' => $itemName));
        $char->$scoreName->sub($scoreProperty, $amount);
        $char->save();

        return true;
    }
}
?>