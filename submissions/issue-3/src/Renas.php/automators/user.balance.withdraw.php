<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 自动分批处理未上账的token存取
################################################

// $db = new \xDatabase;

if(!empty($GLOBALS['deploy']['network'])) {
    foreach ($GLOBALS['deploy']['network'] as $networkName => $networkConfig) { //遍历每个network处理上账
        # 处理状态为null的提现记录
        \fLog("Querying unsealed withdrawal records of {$networkName}");
        $query = $db->getArr( //取未sealed的数据
            "balance_record_".$networkName,
            array(
                "`status` is null",
                "`action` = 'withdraw'"
            ),
            null,
            $GLOBALS['setting']['automator']['balancePerBatch'],
            null,
            '`lastCheck`',
            'ASC'
        );
        \fLog(\fDump($query));

        if($query !== false) {
            foreach ($query as $record) {
                \fAsync($GLOBALS['deploy']['siteRoot'].DIR_ASYNC.'updateTransactionResult.php?network='.$networkName.'&id='.$record['id']);
            }
        }

        // if($query !== false) {
        //     foreach ($query as $record) {
        //         \fLog('Calling async script: '. $GLOBALS['deploy']['siteRoot'].DIR_ASYNC.'updateTransactionResult.php?network='.$networkName.'&id='.$record['id']);
        //         $check = \fAsync( //调自有异步脚本
        //             'get',
        //             $GLOBALS['deploy']['siteRoot'].DIR_ASYNC.'updateTransactionResult.php?network='.$networkName.'&id='.$record['id']
        //         );
        //         \fLog("fAsync returns {$check}");
        //     }
        // }
    }
}



?>