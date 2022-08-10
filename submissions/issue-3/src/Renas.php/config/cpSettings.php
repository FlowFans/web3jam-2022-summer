<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# CP经济设置相关参数
################################################

#小数位设置
$GLOBALS['cp']['decimal'] = 18;

#新注册用户的CP水龙头总量
$GLOBALS['cp']['faucet'] = '300.0000';

#角色相关的花费
$GLOBALS['cp']['character']['generate'] = 3; //生成角色的花费
$GLOBALS['cp']['character']['expand'] = 5; //扩充角色槽位的花费（系数）
$GLOBALS['cp']['character']['recruit'] = 2; //招募已有角色的花费
$GLOBALS['cp']['character']['recruitCreatorFeeRate'] = '0.1'; //招募已有角色时，创作者可得的收入比例

#CP质押奖励
$GLOBALS['cp']['staking']['epochReward']['adventure'] = 30; //每个epoch给冒险类型激励的数量

?>