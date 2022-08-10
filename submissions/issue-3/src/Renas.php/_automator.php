<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 本脚本会自动执行一些常规检查
################################################

require_once _ROOT.DIR_AUTO.'user.balance.deposit.php';
require_once _ROOT.DIR_AUTO.'user.balance.withdraw.php';
require_once _ROOT.DIR_AUTO.'user.facilities.php';
require_once _ROOT.DIR_AUTO.'epoch.stake.php';
require_once _ROOT.DIR_AUTO.'epoch.incentivise.php';
?>