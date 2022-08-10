<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################
// $db = new xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('item/equip/body.confirm.html', 'body');

//检查参数合法性
if(is_null(\fGet('item'))) {
    \fLog('Error: no item given');
    $html->redirectBack(
        'redirect.message.item.useError'
    );
    \fDie();
}

if(is_null(\fGet('char'))) {
    \fLog('Error: no charId given');
    $html->redirect(
        _ROOT,
        'pageTitle.plaza',
        'redirect.message.item.useError'
    );
    \fDie();
}

$char = new \meshal\xChar;
$char->load($_GET['char']);

//检查这个角色的版本
if(\fCheckVersion($char->version, '0.2.0') == -1) {
    \fLog("Error: target character({$char->id}) version is obsolete");
    $html->set('$charName', $char->name);
    $html->redirectBack(
        'redirect.message.item.characterVersionObsolete'
    );
    \fDie();
}

//检查这个角色是否是用户的
if($char->owner->uid != $user->uid) {
    \fLog("Error: target character({$char->id}) doesn't belong to the user({$user->uid})");
    $html->set('$charName', $char->name);
    $html->redirectBack(
        'redirect.message.item.cantManipulateOthersChar'
    );
    \fDie();
}

//检查这个角色是否在营地
if(!is_null($char->stat)) {
    \fLog("Error: target character({$char->id}) is not in campsite");
    $html->set('$charName', $char->name);
    $html->redirectBack(
        'redirect.message.item.manipulateBusyChar'
    );
    \fDie();
}

//加载物品
$item = new \meshal\xItem; 
if($item->load($_GET['item']) === false) { //检查物品是否在数据库中存在
    \fLog("Error: item {$_GET['item']} doesn't exist in the library");
    $html->redirectBack(
        'redirect.message.item.useError'
    );
    \fDie();
}

# 如果有submit则为一次提交
if($_GET['submit']) {
    /**
     * 使用前提检查
     */
    //遍历物品使用条件，并做检查
    $checkAll = 1;
    if(!empty($item->data['use']['checkAll'])) {
        foreach ($item->data['use']['checkAll'] as $k => $check) {
            $param = $check;
            $param[0] = $char->id;
            if(method_exists('\meshal\char\xChecker', $check[0])) { //检查方法是否存在
                $checkAll *= \meshal\char\xChecker::{$check[0]}(...$param) == true ? 1 : 0; //累乘检查结果
            }
        }
    }

    $checkAny = 1;
    if(!empty($item->data['use']['checkAny'])) {
        $checkAny = 0;
        foreach ($item->data['use']['checkAny'] as $k => $check) {
            $param = $check;
            $param[0] = $char->id;
            if(method_exists('\meshal\char\xChecker', $check[0])) { //检查方法是否存在
                $checkAny += \meshal\char\xChecker::{$check[0]}(...$param) == true ? 1 : 0; //累加检查结果
            }
        }
    }
    
    //条件不满足
    if($checkAll * $checkAny == 0) {
        \fLog("Error: character({$char->id}) doesn't meet the use condition");
        $html->set('$charName', $char->name);
        $html->redirectBack(
            'redirect.message.item.useConditionNotMet'
        );
        \fDie();
    }

    /**
     * 物品数量扣除检查
     */
    # 这是从角色携带的物品中使用
    if(\fGet('carrying', '') != '') {
        if($char->inventory->checkCarrying($_GET['item'],1) == false) { // 检查是否有足够数量的物品供消耗
            \fLog("Character({$char->id}) doesn't have enough {$_GET['item']}");
            $html->set('$itemName', "{?itemName.{$_GET['item']}?}");
            $html->set('$charName', $char->name);
            $html->redirectBack(
                'redirect.message.item.insufficientCarrying'
            );
            \fDie();
        }

        $check = $char->inventory->discard($_GET['item'], 1, false);
        if($check != 0) {
            \fLog("Error while using {$_GET['item']} on character({$char->id}). Code({$check})");
            $html->redirectBack(
                'redirect.message.item.useError'
            );
            \fDie();
        }
        $char->save();
    } 
    
    # 这是从用户的库存中使用
    else {
        if($user->inventory->checkStock($_GET['item'],1) == false) { // 检查是否有足够数量的物品供消耗
            \fLog("User({$user->uid}) doesn't have enough {$_GET['item']}");
            $html->set('$itemName', "{?itemName.{$_GET['item']}?}");
            $html->redirectBack(
                'redirect.message.item.insufficientStock'
            );
            \fDie();
        }

        $check = $user->inventory->remove($_GET['item'], 1);
        if($check != 0) {
            \fLog("Error while using {$_GET['item']} on character({$char->id}). Code({$check})");
            $html->redirectBack(
                'redirect.message.item.useError'
            );
            \fDie();
        }
    }

    /**
     * 效果触发
     */
    if(!empty($item->data['use']['efx'])) {
        foreach ($item->data['use']['efx'] as $k => $efx) {
            $method = $efx[0];
            $param = array(
                $user->uid,
                $item->name,
                $char->id
            );
            unset($efx[0]);
            $param = array_merge($param, $efx);

            \fLog("Triggering using efx: {$method}");
            \fLog(\fDump($param), 1);
            if(method_exists('\meshal\item\xUsage', $method)) { //检查方法是否存在
                \meshal\item\xUsage::$method(...$param); //执行效果
            }
        }
        \fLog("Item {$_GET['item']} has been used on char({$char->id})");
        $html->set('$itemName', "{?itemName.{$_GET['item']}?}");
        $html->set('$charName', $char->name);

        if(
            \fGet('carrying', '') == ''
            && $user->inventory->getStock($_GET['item']) == 0
        ) {
            $html->redirect(
                _ROOT.'warehouse',
                'pageTitle.warehouse',
                'redirect.message.item.useSuccess',
            );
        } else {
            $html->redirectBack(
                'redirect.message.item.useSuccess'
            );
        }
        
        \fDie();
    } else {
        \fLog("Error: item has no usage");
        $html->redirectBack(
            'redirect.message.item.useError'
        );
        \fDie();
    }
}

\fDie();
?>