<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 框架配置
################################################

#敏感信息设置
#DIR_CERT
define("DIR_CERT", '.certs/');

#计算设置
$GLOBALS['setting']['bcmath']['decimals'] = 4;
$GLOBALS['setting']['bcmath']['decimalRound'] = 4;


#进行文本替换时的最大递归层数。建议设为3~12。递归层数越多，性能开销就越大。
$GLOBALS['setting']['fReplace']['maxRecursive'] = 5;

#查询分页设置
$GLOBALS['setting']['pager']['default']['less'] = 12; //默认的分页数量（较少）
$GLOBALS['setting']['pager']['default']['normal'] = 30; //默认的分页数量（一般）
$GLOBALS['setting']['pager']['default']['more'] = 50; //默认的分页数量（较多）
$GLOBALS['setting']['pager']['message']['messagesPerPage'] = 50; //在消息列表中，每页显示消息的数量
$GLOBALS['setting']['pager']['character']['charactersPerPage'] = 12; //在角色列表中，每页显示角色的数量
$GLOBALS['setting']['pager']['warehouse']['itemsPerPage'] = 50; //在仓库列表中，每页显示物品的数量
$GLOBALS['setting']['pager']['facility']['facilitiesPerPage'] = 50; //在仓库列表中，每页显示物品的数量
$GLOBALS['setting']['pager']['adventure']['adventuresPerPage'] = 12; //在冒险列表中，每页显示冒险的数量
$GLOBALS['setting']['pager']['stake']['epochPerPage'] = 30; //在stake历史列表中，每页显示的epoch数量
$GLOBALS['setting']['pager']['editor']['defaultRowsPerPage'] = 50; //编辑器中，默认的分页显示条目数量
$GLOBALS['setting']['pager']['editor']['featuresPerPage'] = 50; //在特征列表中，每页显示特征的数量
$GLOBALS['setting']['pager']['editor']['FeatureSelectorPerPage'] = 50; //特征选择器中，每页显示特征的数量
$GLOBALS['setting']['pager']['editor']['itemsPerPage'] = 50; //在物品列表中，每页显示物品的数量
$GLOBALS['setting']['pager']['editor']['facilitiesPerPage'] = 50; //在编辑列表中，每页显示设施的数量
$GLOBALS['setting']['pager']['editor']['encountersPerPage'] = 50; //在编辑列表中，每页显示遭遇的数量
$GLOBALS['setting']['pager']['editor']['adventuresPerPage'] = 50; //在编辑列表中，每页显示冒险的数量
?>