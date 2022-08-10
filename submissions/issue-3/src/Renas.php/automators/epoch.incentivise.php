<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 自动分批处理每个周期中的收益
################################################

# 自动处理的注册表
$registry = array(
    'adventure'
);

// $db = new \xDatabase;
$currentEpoch = \fEpoch();

/**
 * 遍历注册表并批量处理
 */
foreach ($registry as $k => $type) {
    #先取最早未结算的epoch
    $queryEpoch = $db->getArr(
        'epoch_records',
        array(
            "`sealed` = '0'",
            "`epoch` < '{$currentEpoch}'",
            "`type` = '{$type}'"
        ),
        null,
        1,
        null,
        '`epoch`',
        'ASC'
    );

    if(!empty($queryEpoch)) { //如果取到有未结算的epoch，则准备进行结算
        $lastEpoch = $queryEpoch[0]['epoch'];
    } else { //如果没有取到未结算的epoch，跳出循环
        break;
    }

    \fLog("Last undone epoch = {$lastEpoch}");

    /**
     * 如果过去的epoch没有结算完，先结算过去的epoch
     */
    $query = $db->getArr( //随机取该epoch下未结算完的记录
        'epoch_records',
        array(
            "`sealed` = '0'",
            "`epoch` = '{$lastEpoch}'",
            "`type` = '{$type}'"
        ),
        null,
        $GLOBALS['setting']['automator']['incentivePerBatch'][$type],
        null,null,'RAND'
    );

    if(!empty($query)) {
        $epochSum = $db->getSum( //从epoch_records表中取该epoch下该类型的计数总和（作为后面计算比率的分母）
            'epoch_records',
            '`count`',
            array(
                "`epoch` = '{$lastEpoch}'",
                "`type` = '{$type}'"
            )
        );

        #对获取到的数据进行处理
        foreach($query as $j => $record) { //遍历每条记录
            $allShares = $db->getSum( //从ownership表取对应数据的所有shares总和
                'ownership',
                '`shares`',
                array(
                    "`type` = '{$type}'",
                    "`content` = '{$record['content']}'",
                    "`shares` > '0'"
                )
            );

            $ownerships = $db->getArr( //从ownership表取对应的数据
                'ownership',
                array(
                    "`type` = '{$record['type']}'",
                    "`content` = '{$record['content']}'",
                    "`shares` > '0'"
                )
            );

            if(!empty($ownerships)) {
                foreach($ownerships as $l => $ownership) { //遍历每个ownership记录，给对应的用户加cp
                    /**
                     * 这里的计算方式是：
                     * (内容在类型中的访问量 ÷ 类型总访问量) × (用户在该内容的share ÷ 该内容的总share) × 每周期奖励量
                     */
                    $reward = \fMul( //用该内容在一个epoch中分得的奖励数额×单个用户的share = 用户得到的cp
                        \fMul( //用该类型的奖励总额×该内容访问率比重
                            $GLOBALS['cp']['staking']['epochReward']['adventure'],
                            \fDiv( //用该内容的访问率÷总访问率得到比重
                                $record['count'],
                                $epochSum
                            )
                        ),
                        \fDiv( //计算单个用户在这个内容中的shares比重
                            $ownership['shares'],
                            $allShares
                        )
                    );

                    //增加cp给用户
                    \xUser::addCP(
                        $ownership['uid'],
                        $reward
                    );

                    //向log_epochReward添加记录
                    $db->insert(
                        'log_epochReward',
                        array(
                            'epoch' => $lastEpoch,
                            'uid' => $ownership['uid'],
                            'type' => $type,
                            'content' => $record['content'],
                            'reward' => $reward,
                            'timestamp' => time()
                        )
                    );

                    //向该用户发消息
                    \fMsg(
                        $ownership['uid'],
                        'reward',
                        "message.epochIncentive.{$type}",
                        array(
                            '$contentName' => "{?adventureName.{$record['content']}?}",
                            '$amount' => $reward,
                            '$epoch' => $lastEpoch
                        )
                    );
                }
            }

            //将该记录sealed设为1，标记为结算完成
            $db->update(
                'epoch_records',
                array(
                    'sealed' => 1
                ),
                array(
                    "`epoch` = '{$lastEpoch}'",
                    "`type` = '{$type}'",
                    "`content` = '{$record['content']}'"
                ),
                1
            );
        }
    }
}
?>