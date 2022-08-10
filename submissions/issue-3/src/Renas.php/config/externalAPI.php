<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 外部API设置相关参数
################################################

# 通过EmeraldId查询discordId <-> flowAddress的接口
$GLOBALS['deploy']['external']['emeraldId'] = 'https://s.renas.thing.fund/api/data/accounts/emerald';

?>