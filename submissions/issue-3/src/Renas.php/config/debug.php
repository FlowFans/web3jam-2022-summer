<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# Debug 配置
################################################

#debug模式总开关
$GLOBALS['debug']['debugMode'] = true;

#debug模式下，以什么方式输出变量
$GLOBALS['debug']['dumpEncode'] = 'print_r';

#debug模式下，是否记录日志
$GLOBALS['debug']['log'] = true;

#是否在tooltip中显示debug信息
$GLOBALS['debug']['tooltip'] = false;

#是否显示角色卡的debug信息
$GLOBALS['debug']['characterSheet'] = false;
?>