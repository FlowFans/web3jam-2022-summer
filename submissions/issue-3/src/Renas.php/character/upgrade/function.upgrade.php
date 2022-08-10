<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 提供角色升级的方法
################################################


/**
 * 一些常规检查
 */
function localCheck() {
    global $char;
    global $user;
    global $html;

    $char->load($_GET['id']);

    //检查这个角色是否可被升级
    if(
        \fCheckVersion($char->version, $GLOBALS['meshal']['version']['character']) != -1
    ) {
        $html->set('characterName', $char->name);
        $html->redirect(
            _ROOT.'character/',
            'pageTitle.myCharacters',
            'redirect.message.character.noNeedUpgrade'
        );
        \fDie();
    }

    //检查这个角色是否属于用户
    if(
        $char->owner->uid !== $user->uid
    ) {
        $html->redirect(
            _ROOT.'character/',
            'pageTitle.myCharacters',
            'redirect.message.character.invalidUpgrade'
        );
        \fDie();
    }
}
?>