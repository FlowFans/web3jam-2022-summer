<?php
################################################
# 本脚本不允许外部直接访问
################################################

use user\xAdapter;

if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供Stake相关的方法
################################################

/**
 * Stake
 * 
 * @param int $uid
 * 用户uid
 * 
 * @param string $type
 * 内容的类型
 * 
 * @param string $content
 * 内容的标识
 * 
 * @param int|float|string $shares
 * 质押的数量
 * 
 * @return int
 * 返回状态码
 * - 0：成功
 * - 1：输入的stake数额≤0
 * - 2：输入的stake数量>用户的cp
 */
function fStake(
    int $uid,
    string $type,
    string $content,
    $shares
) {
    global $db;
    $currentEpoch = \fEpoch();

    if(\bccomp($shares, 0) != 1) {
        \fLog("Staking amount <= 0");
        return 1;
    }

    $user = new \user\xAdapter;
    $user->load($uid);

    //检查用户是否在当前epoch有正在撤销中的staking
    $query = $db->getArr(
        'epoch_staking',
        array(
            "`uid` = '{$uid}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'",
            "`epoch` = '{$currentEpoch}'"
        ),
        null,
        1
    );
    $staking = empty($query) ? 0 : $query[0]['shares'];
    
    # 如果有取消stake的未结算部分，则优先处理这个部分
    if(
        $staking < 0
    ) {
        switch (bccomp(abs($staking), $shares)) {
            case -1: //如果目前abs(staking)的量<输入的量，扣掉部分，然后扣除现有的CP部分
                $db->update(
                    'epoch_staking',
                    array(
                        'shares' => $staking + $shares
                    ),
                    array(
                        "`uid` = '{$uid}'",
                        "`type` = '{$type}'",
                        "`content` = '{$content}'",
                        "`epoch` = '{$currentEpoch}'"
                    ),
                    1
                );

                //从用户身上扣除溢出的部分
                $user->modCP(abs($staking) - $shares);
                break;
            
            case 1: //如果目前abs(staking)的量>输入的量，那么就从staking中扣除
                $db->update(
                    'epoch_staking',
                    array(
                        'shares' => $staking + $shares
                    ),
                    array(
                        "`uid` = '{$uid}'",
                        "`type` = '{$type}'",
                        "`content` = '{$content}'",
                        "`epoch` = '{$currentEpoch}'"
                    ),
                    1
                );
                break;

            case 0: //如果目前abs(staking)的量=输入的量，那么就删除该staking记录
                $db->delete(
                    'epoch_staking',
                    array(
                        "`uid` = '{$uid}'",
                        "`type` = '{$type}'",
                        "`content` = '{$content}'",
                        "`epoch` = '{$currentEpoch}'"
                    ),
                    1
                );
                break;

            default:
                break;
        }
    } 
    
    # 如果staking>=0
    else {
        if(\bccomp($user->cp, $shares) == -1) { //质押量必须≤用户现有的cp
            \fLog("Error: staking amount is more than user's cp");
            return 2;
        }
    
        //检查当前epoch是否有记录
        $query = $db->getArr(
            'epoch_staking',
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'",
                "`epoch` = '{$currentEpoch}'"
            ),
            null,
            1
        );
    
        if(empty($query)) { //没有则创建新记录
            $db->insert(
                'epoch_staking',
                array(
                    'uid' => $uid,
                    'type' => $type,
                    'content' => $content,
                    'epoch' => $currentEpoch,
                    'shares' => \fAdd(0, $shares)
                )
            );
        }
    
        else { //有则更新记录
            $db->update(
                'epoch_staking',
                array(
                    'shares' => \fAdd($query[0]['shares'], $shares)
                ),
                array(
                    "`uid` = '{$uid}'",
                    "`type` = '{$type}'",
                    "`content` = '{$content}'",
                    "`epoch` = '{$currentEpoch}'"
                ),
                1
            );
        }
    
        //从用户身上扣除对应的cp
        $user->modCP(-$shares);
    }
    return 0;
}

/**
 * Unstake
 * 
 * @param int $uid
 * 用户uid
 * 
 * @param string $type
 * 内容的类型
 * 
 * @param string $content
 * 内容的标识
 * 
 * @param int|float|string $shares
 * 解除质押的数量
 * 
 * @return int
 * 返回状态码
 * - 0：成功
 * - 1：输入的unstake数额≤0
 * - 2：用户没有足够的质押量
 */
function fUnstake(
    int $uid,
    string $type,
    string $content,
    $shares
) {
    global $db;
    $currentEpoch = \fEpoch();

    if(\bccomp($shares, 0) != 1) {
        \fLog("Unstaking amount <= 0");
        return 1;
    }
    
    //先检查用户是否有足够的shares（ownership表和epoch_staking表一起检查)
    $currentShares = 0;

    $queryOwnership = $db->getArr(
        'ownership',
        array(
            "`uid` = '{$uid}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'"
        ),
        null,
        1
    );
    if(!empty($queryOwnership)) $currentShares += $queryOwnership[0]['shares'];

    $currentShares += $db->getSum(
        'epoch_staking',
        '`shares`',
        array(
            "`uid` = '{$uid}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'",
            "`sealed` = '0'"
        )
    );

    //如果用户的shares不足，返回错误码
    if(\bccomp($currentShares, $shares) == -1) {
        return 2;
    }

    //获取epoch_staking未结算记录
    $userRecords = $db->getArr(
        'epoch_staking',
        array(
            "`uid` = '{$uid}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'",
            "`sealed` = '0'"
        ),
        null,null,null,
        '`epoch`',
        'ASC'
    );

    $reduct = $shares;

    if(!empty($userRecords)) {
        $user = new \user\xAdapter;
        $user->load($uid);
        $refund = 0;

        foreach ($userRecords as $k => $record) { //遍历每一笔未结算的记录，先从这些记录中扣除
            // fPrint($record);
            if(bccomp($reduct, 0) == 1) { //如果还有余额未扣除
                if(bccomp($reduct, $record['shares']) == -1) { //余额小于这笔记录
                    //从这笔记录里扣掉余额部分
                    $refund += $reduct;
                    $record['shares'] -= $reduct;
                    $reduct = 0;
                    $db->update(
                        'epoch_staking',
                        array(
                            'shares' => $record['shares']
                        ),
                        array(
                            "`epoch` = '{$record['epoch']}'",
                            "`uid` = '{$record['uid']}'",
                            "`type` = '{$record['type']}'",
                            "`content` = '{$record['content']}'"
                        ),
                        1
                    );
                } else { //余额大于等于这笔记录
                    //将这笔记录删除
                    $refund += $record['shares'];
                    $reduct -= $record['shares'];
                    $db->delete(
                        'epoch_staking',
                        array(
                            "`epoch` = '{$record['epoch']}'",
                            "`uid` = '{$record['uid']}'",
                            "`type` = '{$record['type']}'",
                            "`content` = '{$record['content']}'",
                            "`shares` = '{$record['shares']}'"
                        ),
                        1
                    );
                }
            }
        }

        // fPrint($refund);
        //把cp退给用户
        $user->modCP($refund);
    }

    //检查当前epoch是否有记录
    $query = $db->getArr(
        'epoch_staking',
        array(
            "`uid` = '{$uid}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'",
            "`epoch` = '{$currentEpoch}'"
        ),
        null,
        1
    );

    if(empty($query)) { //没有则创建新记录
        $db->insert(
            'epoch_staking',
            array(
                'uid' => $uid,
                'type' => $type,
                'content' => $content,
                'epoch' => $currentEpoch,
                'shares' => \fSub(0, $reduct)
            )
        );
    }

    else { //有则更新记录
        $db->update(
            'epoch_staking',
            array(
                'shares' => \fSub($query[0]['shares'], $reduct)
            ),
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'",
                "`epoch` = '{$currentEpoch}'"
            ),
            1
        );
    }
    return 0;
}
?>