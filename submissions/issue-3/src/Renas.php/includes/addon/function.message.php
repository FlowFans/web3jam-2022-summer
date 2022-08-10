<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供对消息做处理的方法
################################################


/**
 * 向数据库推入一条消息
 * 
 * @param string|int $uid
 * 消息接受者的uid
 * 
 * @param string $type
 * 消息的分类
 * 
 * @param string $message
 * 消息的语言代码
 * 
 * @param array $vars
 * 用于替代语言内容中的变量内容，留作之后的渲染
 * 
 * @param int $timestamp = null
 * 时间戳，为null时取当前时间戳
 */
function fMsg (
    $uid,
    string $type,
    string $message,
    array $vars = array(),
    $timestamp = null
) {
    // $db = new \xDatabase;
    global $db;

    return $db->insert(
        'messages',
        array(
            'uid' => $uid,
            'type' => $type,
            'message' => "{?{$message}?}",
            'data' => \fEncode(json_encode($vars)),
            'timestamp' => is_null($timestamp) ? time() : $timestamp,
            'unread' => 1
        )
    );
}



?>