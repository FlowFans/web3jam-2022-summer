<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 自动分批入账上个周期中的staking请求
################################################

// $db = new \xDatabase;
$currentEpoch = \fEpoch();

//取最早未入账的stake请求
$queryEpoch = $db->getArr(
    'epoch_staking',
    array(
        "`sealed` = '0'",
        "`epoch` < '{$currentEpoch}'"
    ),
    null,
    1,
    null,
    '`epoch`',
    'ASC'
);

if(!empty($queryEpoch)) { //如果取到有未结算的epoch，则准备进行结算
    $lastEpoch = $queryEpoch[0]['epoch'];
    \fLog("Last undone epoch = {$lastEpoch}");
    
    /**
     * 如果过去的epoch没有结算完，先结算过去的epoch
     */
    $query = $db->getArr( //随机取该epoch下未结算完的记录
        'epoch_staking',
        array(
            "`sealed` = '0'",
            "`epoch` = '{$lastEpoch}'"
        ),
        null,
        $GLOBALS['setting']['automator']['stakingPerBatch'],
        null,null,'RAND'
    );

    if(!empty($query)) {
        foreach($query as $k => $record) {
            //查找ownership表，获取用户在该内容的已有shares
            $ownership = $db->getArr(
                'ownership',
                array(
                    "`uid` = '{$record['uid']}'",
                    "`type` = '{$record['type']}'",
                    "`content` = '{$record['content']}'"
                ),
                null,
                1
            );

            if(empty($ownership)) { //如果用户不曾质押过，创建新的记录
                $db->insert(
                    'ownership',
                    array(
                        'uid' => $record['uid'],
                        'type' => $record['type'],
                        'content' => $record['content'],
                        'shares' => $record['shares']
                    )
                );
            }

            else { //如果用户已质押过，则累加shares
                $db->update(
                    'ownership',
                    array(
                        'shares' => \fAdd($ownership[0]['shares'], $record['shares'])
                    ),
                    array(
                        "`uid` = '{$record['uid']}'",
                        "`type` = '{$record['type']}'",
                        "`content` = '{$record['content']}'"
                    ),
                    1
                );
            }

            //如果是负数，则为unstake，因此要向用户退款（添加cp）
            if(bccomp($record['shares'], 0) == -1) {
                $user = new \user\xAdapter;
                $user->load($record['uid']);
                $user->modCP(abs($record['shares']));
            }

            /**
             * 根据不同的类型，执行不同的数据操作
             * 向对应目标记录增加totalShares
             */
            switch ($record['type']) {
                case 'adventure':
                    $db->update(
                        'adventures',
                        array(
                            'totalShares' => "`totalShares` + {$record['shares']}"
                        ),
                        array(
                            "`name` = '{$record['content']}'"
                        ),
                        1,
                        false
                    );
                    break;

                case 'encounter':
                    $db->update(
                        'encounters',
                        array(
                            'totalShares' => "`totalShares` + {$record['shares']}"
                        ),
                        array(
                            "`name` = '{$record['content']}'"
                        ),
                        1,
                        false
                    );
                    break;

                case 'feature':
                    $db->update(
                        'features',
                        array(
                            'totalShares' => "`totalShares` + {$record['shares']}"
                        ),
                        array(
                            "`name` = '{$record['content']}'"
                        ),
                        1,
                        false
                    );
                    break;
                
                case 'ability':
                    $db->update(
                        'abilities',
                        array(
                            'totalShares' => "`totalShares` + {$record['shares']}"
                        ),
                        array(
                            "`name` = '{$record['content']}'"
                        ),
                        1,
                        false
                    );
                    break;    

                case 'item':
                    $db->update(
                        'items',
                        array(
                            'totalShares' => "`totalShares` + {$record['shares']}"
                        ),
                        array(
                            "`name` = '{$record['content']}'"
                        ),
                        1,
                        false
                    );
                    break;

                default:
                    # code...
                    break;
            }

            //将这条记录设为sealed
            $db->update(
                'epoch_staking',
                array(
                    'sealed' => 1
                ),
                array(
                    "`epoch` = '{$lastEpoch}'",
                    "`uid` = '{$record['uid']}'",
                    "`type` = '{$record['type']}'",
                    "`content` = '{$record['content']}'"
                ),
                1
            );
        }
    }
}
?>