<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 排行榜设置相关参数
################################################

# 排行榜拉取数量设置
$GLOBALS['meshal']['ranking']['tops'] = array();
$GLOBALS['meshal']['ranking']['tops']['adventures'] = 30;
$GLOBALS['meshal']['ranking']['tops']['activeAdventurers'] = 30;

# 排行榜类型设置
$GLOBALS['meshal']['ranking']['types'] = array();

    #冒险次数
    $GLOBALS['meshal']['ranking']['types']['adventures'] = array(
        'icon' => 'user'
    );
    #冒险活跃角色
    $GLOBALS['meshal']['ranking']['types']['activeAdventurers'] = array(
        'icon' => 'character.player'
    );


?>