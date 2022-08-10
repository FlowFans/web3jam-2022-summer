<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 创建一个用于记录本次执行日志的临时log文件
################################################

#如果没有定义常量 _NOREQUESTLOG 和 _NOLOG，那么会将页面请求写入log。
#在被频繁调取的接口（比如聊天拉数据）中，应当定义 _NOREQUESTLOG 和 _NOLOG，以免产生无用日志。
if (
	!defined('_NOREQUESTLOG')
	&& !defined('_NOLOG')
) {
    $GLOBALS['debug']['logFileName'] = \fGenGuid();
    $GLOBALS['debug']['logFile'] = fopen(
        _ROOT.DIR_LOG.'temp/'.$GLOBALS['debug']['logFileName'].'.log',
        'w'
    );
}
?>