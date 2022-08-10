<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal能力的类
################################################


class xFeature
{
    /**
     * 从数据库中获取特征的资料
     * 
     * @param string $featureType
     * 查询的特征类型（参考__construct())
     * 
     * @param string $featureName
     * 查询的特征名称
     * 
     * @return mixed
     * 如果没有查到，返回false；
     * 如果查到了特征，以数组返回包含该特征的数据
     */
    public static function getData (
        string $featureType,
        string $featureName
    ) {
        // $db = new \xDatabase;
        global $db;
        $arr = $db->getArr(
            'features',
            array(
                "`type` = '{$featureType}'",
                "`name` = '{$featureName}'"
            ),
            NULL,
            1
        );
        if($arr === false) {
            \fLog("{$featureType}.{$featureName} doesn't exist in library");
            return false;
        }

        ################################################
        # 稀有度计算
        ################################################
        $sum = $db->getArr(
            'feature_index',
            array(
                "`name` = '{$featureType}'"
            ),
            null,
            1
        );
        if($sum === false) { //如果没有权重总和记录，创建一个
            $db->insert(
                'feature_index',
                array(
                    'name' => "{$featureType}"
                )
            );
            $sum = $db->getArr( //重新获取该特征类型的权重总和数据
                'feature_index',
                array(
                    "`name` = '{$featureType}'"
                ),
                null,
                1
            );
        }
        
        $probability = array();

        //计算这个特征的随机权重
        $probability['weight'] = 
            $sum[0]['strength'] + $sum[0]['count'] //总实力 + 总数量
            - abs($arr[0]['strength']) //减去这个特征的实力绝对值
            + $arr[0]['probabilityModifier']; //加上这个特征的概率修正

        //根据特征的平均实力值，反向求取特征的平均实力权重
        $probability['benchmark']['strengthWt'] = \fSub(
            $sum[0]['strength'],
            \fDiv( //计算所有同类特征的平均实力值
                $sum[0]['strength'] + $sum[0]['count'],
                $sum[0]['count']
            )
        );

        //计算平均权重修正
        $probability['benchmark']['modifierWt'] = \fDiv(
            $sum[0]['probabilityModifier'] + $sum[0]['count'],
            $sum[0]['count']
        );

        //计算这个特征的随机概率
        $probability['result'] = \fDiv(
            $probability['weight'],
            \fMul( //将特征的平均实力权重 × 特征数，得到总权重
                $probability['benchmark']['strengthWt'],
                $sum[0]['count']
            ) + $sum[0]['probabilityModifier'] //加上总权重修正
        , 8);

        //根据比例进行渲染
        $descArr = $GLOBALS['meshal']['rarity']['feature'][$featureType]
            ? $GLOBALS['meshal']['rarity']['feature'][$featureType]
            : $GLOBALS['meshal']['rarity']['feature']['default']
        ;
        foreach ($descArr as $k => $v) {
            if(
                is_null($v['max']) //如果配置中的max为null，就意味着这是最大概率的描述
                || ( 
                    //判断是否在区间内
                    $probability['result'] <= $v['max']
                    && $probability['result'] > $v['min']
                )
            ) {
                $probability['rarity'] = "{?{$v['desc']}?}";
                $probability['style'] = $v['style'];
                break;
            }
        }

        ################################################
        # 稀有度计算结束
        ################################################

        $return = array(
            'fullname' => "meshal.{$featureType}.{$featureName}",
            'name' => $featureName,
            'type' => $featureType,
            'data' => json_decode($arr[0]['data'], true),
            'strength' => $arr[0]['strength'],
            'probability' => array(
                'modifier' => $arr[0]['probabilityModifier'],
                'result' => $probability['result'],
                'rarity' => $probability['rarity'],
                'rarityStyle' => $probability['style']
            ),
            'totalShares' => $arr[0]['totalShares']
        );
        // fPrint($return);

        return $return;
    }
}
?>