<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供Twitter分享的方法
################################################

/**
 * 计算传递的时间戳属于哪个epoch
 * 
 * @param int $timestamp = null
 * 被计算的时间戳，为null时取当前时间。
 * 
 * @return int
 * 返回所属的epoch
 */
function fEpoch(
    int $timestamp = null
) {
    if(is_null($timestamp)) $timestamp = time();

    return floor(($timestamp - $GLOBALS['setting']['epoch']['begin']) / $GLOBALS['setting']['epoch']['span']);
}

/**
 * 传递一个epoch，返回该epoch的时间戳范围
 * 
 * @param int $epoch = null
 * 传递的epoch，为null时取当前epoch
 * 
 * @return array
 * 返回时间戳范围，格式为：$return = array(
 *  'min' => int,
 *  'max' => int
 * )
 */
function fEpochRange(
    int $epoch = null
) {
    if(is_null($epoch)) $epoch = \fEpoch();
    return array(
        'start' => $epoch * $GLOBALS['setting']['epoch']['span'] + $GLOBALS['setting']['epoch']['begin'],
        'end' => ($epoch + 1) * $GLOBALS['setting']['epoch']['span'] + $GLOBALS['setting']['epoch']['begin'] - 1
    );
}

/**
 * 传递2个epoch，返回这两者之间的时间跨度
 * 
 * @param int $startEpoch
 * 开始的epoch
 * 
 * @param int $endEpoch
 * 结束的epoch
 * 
 * @return array
 * 返回时间戳范围，格式为：$return = array(
 *  'min' => int,
 *  'max' => int
 * )
 */
function fRangeBetweenEpochs(
    int $startEpoch,
    int $endEpoch
) {
    if($startEpoch > $endEpoch) {
        $a = $endEpoch;
        $b = $startEpoch;
    } else {
        $a = $startEpoch;
        $b = $endEpoch;
    }
    $start = \fEpochRange($a);
    $end = \fEpochRange($b);
    return array(
        'start' => $start['start'],
        'end' => $end['end']
    );
}

/**
 * 向epochs表增加计数
 * 
 * @param string $type
 * 内容的类型
 * 
 * @param string $content
 * 内容的标识
 * 
 * @param int $count = 1
 * 添加的计数量
 * 
 * @param int $epoch = null
 * 向哪个epoch添加，null为向当前epoch添加
 */
function fEpochAdd(
    string $type,
    string $content,
    int $count = 1,
    int $epoch = null
) {
    global $db;

    if(is_null($epoch)) $epoch = \fEpoch();
    $query = $db->getArr(
        'epoch_records',
        array(
            "`epoch` = '{$epoch}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'"
        ),
        null,
        1
    );

    if(empty($query)) { //没有则新建
        $db->insert(
            'epoch_records',
            array(
                'epoch' => $epoch,
                'type' => $type,
                'content' => $content,
                'count' => $count
            )
        );
    }

    else { //有则更新
        $db->update(
            'epoch_records',
            array(
                'count' => $query[0]['count'] + $count
            ),
            array(
                "`epoch` = '{$epoch}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'"
            ),
            1
        );
    }
}

/**
 * 取某个epoch中某个内容的count
 * 
 * @param string $type
 * 内容的类型
 * 
 * @param string $content
 * 内容的标识
 * 
 * @param int $epoch = null
 * 从哪个epoch查询，null为当前epoch
 * 
 * @return int
 * 返回找到的count，未找到时返回0
 */
function fEpochGetCount (
    string $type,
    string $content,
    int $epoch = null
) {
    global $db;
    if(is_null($epoch)) $epoch = \fEpoch(); //如果没有epoch为空，则取当前epoch
    $query = $db->getArr(
        'epoch_records',
        array(
            "`epoch` = '{$epoch}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'"
        ),
        null,
        1
    );

    if(empty($query)) return 0;
    return $query[0]['count'];
}

/**
 * 取某个epoch中某个内容的奖励总额
 * 
 * @param string $type
 * 内容的类型
 * 
 * @param string $content
 * 内容的标识
 * 
 * @param int $epoch = null
 * 从哪个epoch查询，null为当前epoch
 * 
 * @return int
 * 返回找到的奖励总和，未找到时返回0
 */
function fEpochGetReward (
    string $type,
    string $content,
    int $epoch = null
) {
    global $db;
    if(is_null($epoch)) $epoch = \fEpoch(); //如果没有epoch为空，则取当前epoch

    return $db->getSum(
        'log_epochReward',
        '`reward`',
        array(
            "`epoch` = '{$epoch}'",
            "`type` = '{$type}'",
            "`content` = '{$content}'"
        )
    );
}

?>