<?php
namespace meshal\adventure;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal队伍检查器的类
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

class xChecker
{
    function __construct(
        \meshal\xAdventure &$parent
    ) {
        $this->parent = $parent; //父级冒险对象
        $this->team = $parent->team; //存储队伍信息
        $this->dice = $parent->dice; //掷骰器
    }

    /**
     * 基于属性进行尝试，这个方法总是用对应属性最高的角色进行尝试
     * 
     * @param int $difficulty = 1
     * 尝试难度，大于这个数字才算成功
     * 
     * @param string $scoreName = null
     * 用于尝试掷骰的属性，为null时表示没有属性参与掷骰
     * 
     * @param string $scoreProperty = null
     * 用于尝试掷骰的属性组成部分（参考xCommonScore）。为null时默认取'current'部分。
     * 
     * @param int $modifier = 0
     * 掷骰数量的修正（加数）
     * 
     * @param int $multiplier = 1
     * 掷骰主梁的修正（系数） $scoreName == null 时本参数无效
     * 
     * @return array
     */
    public function attemptAttrHighest(
        int $difficulty = 1,
        $scoreName = null,
        $scoreProperty = null,
        $modifier = 0,
        $multiplier = 1
    ) {
        if(is_null($scoreName)) { //没有指定属性时，随机选择一个角色掷骰
            $roll = $this->dice->ptDetail($modifier);
            $score = 0;
            $attempter = $this->team->getRandMember(1);
        } else { //有指定属性时，根据要求选择一个角色并用其属性掷骰
            $attempter = $this->team->getMemberScoreHighest(
                $scoreName,
                is_null($scoreProperty) ? 'current' : $scoreProperty
            );
            $score = $attempter['value'];
            $roll = $this->dice->ptDetail(intval($attempter['value'] * $multiplier + $modifier));
        }
        \fLog("roll result = {$roll['result']}");

        //记录关联角色
        $this->parent->addRelChar($attempter['id']);
        if($roll['result'] > $difficulty) {
            $this->parent->addRelSuccess($attempter['id']);
        } else {
            $this->parent->addRelFailure($attempter['id']);
        }

        return array(
            'summary' => $roll['result'] > $difficulty ? true : false,
            'character' => array($attempter['id']),
            'detail' => array(
                'params' => array(
                    'difficulty' => $difficulty,
                    'scoreName' => $scoreName,
                    'scoreProperty' => $scoreProperty,
                    'modifier' => $modifier,
                    'multiplier' => $multiplier
                ),
                'summary' => $roll['result'] > $difficulty ? true : false,
                'character' => array($attempter['id'] => $score),
                'result' => $roll
            )
        );
    }

    /**
     * 基于属性进行尝试，这个方法总是用对应属性最低的角色进行尝试
     * 
     * @param int $difficulty = 1
     * 尝试难度，大于这个数字才算成功
     * 
     * @param string $scoreName = null
     * 用于尝试掷骰的属性，为null时表示没有属性参与掷骰
     * 
     * @param string $scoreProperty = null
     * 用于尝试掷骰的属性组成部分（参考xCommonScore）。为null时默认取'current'部分。
     * 
     * @param int $modifier = 0
     * 掷骰数量的修正（加数）
     * 
     * @param int $multiplier = 1
     * 掷骰主梁的修正（系数） $scoreName == null 时本参数无效
     * 
     * @return array
     */
    public function attemptAttrLowest(
        int $difficulty = 1,
        $scoreName = null,
        $scoreProperty = null,
        $modifier = 0,
        $multiplier = 1
    ) {
        if(is_null($scoreName)) { //没有指定属性时，随机选一个角色掷骰
            $roll = $this->dice->ptDetail($modifier);
            $score = 0;
            $attempter = $this->team->getRandMember(1);
        } else { //有指定属性时，根据要求选择一个角色并用其属性掷骰
            $attempter = $this->team->getMemberScoreLowest(
                $scoreName,
                is_null($scoreProperty) ? 'current' : $scoreProperty
            );
            $score = $attempter['value'];
            $roll = $this->dice->ptDetail(intval($attempter['value'] * $multiplier + $modifier));
        }
        \fLog("roll result = {$roll['result']}");

        //记录关联角色
        $this->parent->addRelChar($attempter['id']);
        if($roll['result'] > $difficulty) {
            $this->parent->addRelSuccess($attempter['id']);
        } else {
            $this->parent->addRelFailure($attempter['id']);
        }

        return array(
            'summary' => $roll['result'] > $difficulty ? true : false,
            'character' => array($attempter['id']),
            'detail' => array(
                'params' => array(
                    'difficulty' => $difficulty,
                    'scoreName' => $scoreName,
                    'scoreProperty' => $scoreProperty,
                    'modifier' => $modifier,
                    'multiplier' => $multiplier
                ),
                'summary' => $roll['result'] > $difficulty ? true : false,
                'character' => array($attempter['id'] => $score),
                'result' => $roll
            )
        );
    }

    /**
     * 基于属性进行尝试，这个方法会随机选择一个角色进行尝试
     * 
     * @param int $difficulty = 1
     * 尝试难度，大于这个数字才算成功
     * 
     * @param string $scoreName = null
     * 用于尝试掷骰的属性，为null时表示没有属性参与掷骰
     * 
     * @param string $scoreProperty = null
     * 用于尝试掷骰的属性组成部分（参考xCommonScore）。为null时默认取'current'部分。
     * 
     * @param int $modifier = 0
     * 掷骰数量的修正（加数）
     * 
     * @param int $multiplier = 1
     * 掷骰主梁的修正（系数） $scoreName == null 时本参数无效
     * 
     * @return bool
     */
    public function attemptRandomMember(
        int $difficulty = 1,
        $scoreName = null,
        $scoreProperty = null,
        $modifier = 0,
        $multiplier = 1
    ) {
        if(is_null($scoreName)) { //没有指定属性时，随机选择一个角色来掷骰
            $roll = $this->dice->ptDetail($modifier);
            $score = 0;
            $attempter = $this->team->getRandMember(1)[0];
        } else { //指定属性时，随机选一个人并用该角色的属性掷骰
            $attempter = $this->team->getRandMember(1)[0];
            // fPrint($attempter);
            $score = $this->team->members[$attempter]->$scoreName->{is_null($scoreProperty) ? 'current' : $scoreProperty};
            $roll = $this->dice->ptDetail(intval(
                // $this->team->{$attempter[0]}->$scoreName->{is_null($scoreProperty) ? 'current' : $scoreProperty}
                $score * $multiplier + $modifier
            ));
        }
        \fLog("roll result = {$roll['result']}");

        //记录关联角色
        $this->parent->addRelChar($attempter['id']);
        if($roll['result'] > $difficulty) {
            $this->parent->addRelSuccess($attempter['id']);
        } else {
            $this->parent->addRelFailure($attempter['id']);
        }

        return array(
            'summary' => $roll['result'] > $difficulty ? true : false,
            'character' => array($attempter),
            'detail' => array(
                'params' => array(
                    'difficulty' => $difficulty,
                    'scoreName' => $scoreName,
                    'scoreProperty' => $scoreProperty,
                    'modifier' => $modifier,
                    'multiplier' => $multiplier
                ),
                'summary' => $roll['result'] > $difficulty ? true : false,
                'character' => array($attempter => $score),
                'result' => $roll
            )
        );
    }
}
?>