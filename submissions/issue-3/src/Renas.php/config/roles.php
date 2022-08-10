<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 权限控制设置相关参数
################################################

//服务器日志
$GLOBALS['auth']['serverLogs']['admin'] = true;

//特征编辑器
$GLOBALS['auth']['editor']['feature']['admin'] = true;
$GLOBALS['auth']['editor']['feature']['editor'] = true;
?>