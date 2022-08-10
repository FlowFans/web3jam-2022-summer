<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal攻防的类
################################################

class xAttack {

    public static function attack (
        \meshal\xChar &$targetChar,
        $attackType = array(),
        $targetAttr = array(),
        int $dicePt = 0,
        int $diceNum = 0,
        int $baseAmount = 0
    ) {
        //如果传递进来的$attackType不是数组，组装成数组
        if(!is_array($attackType)) $attackType = array($attackType);
        if(empty($attackType)) $attackType = array('r'); //如果没有指定攻击类型，那么视作真实攻击
        
        //如果传递进来的$targetAttr不是数组，组装成数组
        if(!is_array($targetAttr)) $targetAttr = array($targetAttr);

        //先掷骰计算
        $dice = new \meshal\xDice;
        $amountPt = $dice->ptDetail($dicePt);
        $amountNum = $dice->numDetail($diceNum);
        $amount = $amountPt['result'] + $amountNum['result'] + $baseAmount;

        if($amount > 0) {
            //预设用于对应此次攻击的防护和免疫变量
            $immuned = false; //此次攻击是否被免疫了
            $reduction = 0; //攻击掷骰的减免量
            $immutable = true; //这个标记高于其他immunity，为false表示该攻击无法被免疫
            $protectable = true; //这个标记高于其他protection，为false表示该攻击无法被防护
            $immunity = array();
            $protection = array();

            //收集用于对应此次攻击的角色防护和免疫数据
            foreach($attackType as $k => $type) {
                //如果配置里不存在，记录错误
                if(!isset($GLOBALS['meshal']['attack'][$type])) {
                    \fLog("Error: attackType({$type}) is not registered");
                } else {
                    //根据攻击类型组装免疫数组
                    if(is_null($GLOBALS['meshal']['attack'][$type]['immunity'])) { //为null则表示这个攻击无法被免疫
                        $immutable = false;
                    } else {
                        $immunity[$type] = $targetChar->{$GLOBALS['meshal']['attack'][$type]['immunity']}->immune == true ? 1 : 0;
                    }
                    
                    //根据攻击类型组装防护数组
                    if(is_null($GLOBALS['meshal']['attack'][$type]['protection'])) { //为null则表示这个攻击无法被防护
                        $protectable = false;
                    } else {
                        $protection[$type] = $targetChar->{$GLOBALS['meshal']['attack'][$type]['protection']}->current;
                    }
                }
            }

            switch (TRUE) {
                case (
                    $immutable === false //不可免疫
                    && $protectable === false //不可防护
                ):
                    #不可免疫不可防护时，无需整理防护和免疫数据
                    $reduction = 0;
                    $immuned = false;
                    break;
                
                case (
                    $immutable === true //可免疫
                    && $protectable === false //不可防护
                ):
                    $reduction = 0;
                    #可免疫、不可防护时，只检查免疫数据
                    if(array_sum($immunity) < count($immunity)) {
                        $immuned = false;
                    } else {
                        $immuned = true;
                    }
                    break;

                case (
                    $immutable === false //不可免疫
                    && $protectable === true //可防护
                ):
                    #不可免疫，可防护时，只检查防护数据
                    $immuned = false;
                    $reduction = min($protection);
                    break;

                default: //默认可免疫、可防护
                    #可免疫、可防护时，先检查免疫，再检查防护
                    $tempProtection = $protection;
                    if(!empty($immunity)) {
                        foreach($immunity as $i => $imm) {
                            if($imm == 1) unset($tempProtection[$i]); //如果已经免疫，那么就忽略防护
                        }
                    }

                    if(empty($tempProtection)) { //如果可用防护都为空，表示这次攻击被免疫了
                        $immuned = true;
                    } else { //否则取最低的防护用于伤害减免
                        $reduction = min($tempProtection);
                    }
                    break;
            }

            //如果没有成功免疫，计算对角色造成的伤害
            $return = array();
            if($immuned === false) {
                $return['damage'] = self::takeDamage(
                    $targetChar,
                    $amount - $reduction,
                    $targetAttr
                );
                $return['immunity'] = $immunity;
                $return['protection'] = $protection;
                $return['detail'] = array(
                    'ptRoll' => $amountPt,
                    'numRoll' => $amountNum,
                    'baseAmount' => $baseAmount,
                );
            } else {
                $return = false; //返回为false代表免疫
            }
        } else { //掷骰得到0，视作未命中
            $return = array(
                'damage' => array(),
                'immunity' => array(),
                'protection' => array(),
                'detail' => array(
                    'ptRoll' => $amountPt,
                    'numRoll' => $amountNum,
                    'baseAmount' => $baseAmount,
                )
            );
        }

        return $return;
    }

    /**
     * 计算并返回各属性要承受的伤害数量
     * 
     */
    public static function takeDamage(
        \meshal\xChar &$targetChar,
        $damage,
        $targetAttr = array()
    ) {
        //如果传递进来的$targetAttr不是数组，组装成数组
        if(!is_array($targetAttr)) $targetAttr = array($targetAttr);

        //取可被扣除的属性
        $attr = array(); //这个数组用于记录用于承受伤害的属性原数值
        $dealt = array(); //这个数组用于记录每个属性承受的伤害
        $attrSum = 0;
        foreach($targetAttr as $k => $attrName) {
            $attr[] = array(
                'attr' => $attrName,
                'value' => $targetChar->$attrName->current
            );
            $attrSum += $targetChar->$attrName->current;
            $dealt[$attrName] = 0;
        }

        if(empty($attr)) { //没有合法的可用于承受伤害的属性
            \fLog("Error: no valid attr scores for taking damage.");
            return false;
        }
        
        switch (TRUE) {
            case count($attr) == 1: //如果只有1个属性用于承受伤害
                foreach($attr as $k => $data) {
                    // $targetChar->{$data['attr']}->sub('current', $damage);
                    $dealt[$data['attr']] = $damage > $data['value'] ? $data['value'] : $damage;
                }
                break;
            
            case $damage >= $attrSum: //如果伤害≥用于承受伤害的属性总和
                foreach($attr as $k => $data) { //遍历每个用于承受伤害的属性，将它们都设为0
                    // $targetChar->{$data['attr']}->set('current', 0);
                    $dealt[$data['attr']] = $data['value'];
                }
                break;

            case $damage > $attrSum - count($attr): //如果伤害可能致命（其中一项或多项属性会被扣至0）
                $temp = $attr;
                shuffle($temp);
                for ($i=0; $i < $damage; $i++) {
                    $random = mt_rand(0, count($attr) - 1); //随机选择一个属性
                    $temp[$random]['value'] --; //对该属性做扣除
                    $dealt[$temp[$random]['attr']] ++; //对该属性承受伤害数量做累加
                    if($temp[$random]['value'] <= 0) unset($temp[$random]); //把不能再承受伤害的属性剔除
                }
                break;

            default: //如果有多个属性用于承受伤害，且此次伤害不会致命
                $temp = $attr;
                shuffle($attr);
                for ($i=0; $i < $damage; $i++) {
                    $random = mt_rand(0, count($attr) - 1); //随机选择一个属性
                    $temp[$random]['value'] --; //对该属性做扣除
                    $dealt[$temp[$random]['attr']] ++; //对该属性承受伤害数量做累加
                    if($temp[$random]['value'] <= 1) unset($temp[$random]); //把不能再承受伤害的属性剔除
                }
                break;
        }

        //根据dealt中的记录，分别扣除对应的属性
        foreach($dealt as $attrName => $dmg) {
            $targetChar->{$attrName}->sub('current', $dmg);
        }
        $targetChar->save();
        
        return $dealt;
    }
}
?>