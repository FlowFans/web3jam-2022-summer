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
$html->loadTpl('item/transfer/body.confirm.html', 'body');

//检查参数合法性
if(is_null(\fGet('item'))) {
    \fLog('Error: no item given');
    $html->redirect(
        _ROOT,
        'pageTitle.plaza',
        'redirect.message.item.transferError'
    );
    \fDie();
}

if(is_null(\fGet('char'))) {
    \fLog('Error: no charId given');
    $html->redirectBack(
        'redirect.message.item.transferError'
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
    $html->redirectBack(
        'redirect.message.item.cantTransferToOthersChar'
    );
    \fDie();
}

//检查这个角色是否在营地
if(!is_null($char->stat)) {
    \fLog("Error: target character({$char->id}) is not in campsite");
    $html->redirectBack(
        'redirect.message.item.transferToBusyChar'
    );
    \fDie();
}

//加载物品
$item = new \meshal\xItem; 
if($item->load($_GET['item']) === false) { //检查物品是否在数据库中存在
    \fLog("Error: item {$_GET['item']} doesn't exist in the library");
    $html->redirect(
        _ROOT,
        'pageTitle.plaza',
        'redirect.message.item.transferError'
    );
    \fDie();
}

//取物品库存
$stock = $db->getArr( 
    'user_items',
    array(
        "`uid` = '{$user->uid}'",
        "`name` = '{$_GET['item']}'"
    ),
    null,
    1
);

//如果用户没有此物品记录或库存为0则报错
if($stock === false || $stock[0]['amount'] <= 0) { 
    \fLog('Error: not enough items');
    $html->set('$itemName', "{?itemName.{$_GET['item']}?}");
    $html->redirect(
        _ROOT,
        'pageTitle.plaza',
        'redirect.message.item.transferInsufficient'
    );
    \fDie();
}

$item->amount = $stock[0]['amount']; //设置物品库存数量


# 如果有submit则为一次提交
if($_GET['submit']) {
    $result = $char->inventory->acquire(
        $_GET['item'],
        $_GET['amount'],
        true
    );

    switch ($result) {
        case 0:
            $char->save();
            $char->event($user->uid, 'item.give', array('item' => $_GET['item'], 'amount' => $_GET['amount']));

            $html->set('$itemName', "{?itemName.{$_GET['item']}?}");
            $html->set('$amount', $_GET['amount']);
            $html->set('$charName', $char->name);

            if($user->inventory->getStock($_GET['item']) == 0) {
                $html->redirect(
                    _ROOT.'warehouse',
                    'pageTitle.warehouse',
                    'redirect.message.item.transferSuccess',
                );
            } else {
                $html->redirectBack(
                    'redirect.message.item.transferSuccess'
                );
            }
            
            \fDie();
            break;

        case 1:
            \fLog("Error: item doesn't exist in the library");
            $html->redirect(
                _ROOT,
                'pageTitle.plaza',
                'redirect.message.item.transferError'
            );
            \fDie();
            break;
        
        case 2:
            \fLog("Error: user doesn't have enough items");
            $html->set('$itemName', "{?itemName.{$_GET['item']}?}");
            \fNotify(
                'notify.item.transfer.userStockInsufficient',
                'warn'
            );
            break;
        
        case 3:
            \fLog("Error: character doesn't have enough carrying capability");
            $html->set('$itemName', "{?itemName.{$_GET['item']}?}");
            $html->set('$charName', $char->name);
            \fNotify(
                'notify.item.transfer.charOverload',
                'warn'
            );
            break;

        default:
            \fLog("Error: unknown error");
            $html->redirectBack(
                'redirect.message.item.transferError'
            );
            \fDie();
            break;
    }
}


# 如果没有confirm则显示表单
$item->amount = $stock[0]['amount']; //设置物品库存数量
$html->set('$backUrl', \fGet('_back'));
$html->set('$itemCard', $item->render());
$html->set('$charSheet', $char->renderLite());

$html->set('$maxStock', $stock[0]['amount']);
$html->set('$charName', $char->name);
$html->set('$itemName', "{?itemName.{$_GET['item']}?}");
$html->set('$itemCode', $_GET['item']);
$html->set('$charId', $_GET['char']);
$html->set('$token', md5($user->uid.'transfer'.$GLOBALS['deploy']['securityKey'].$_GET['item'].' to '.$char->id));

//渲染物品
$html->set('$itemCard', $item->render());

//渲染角色列表
$html->set('$charList', $charList);


$html->output();

\fDie();
?>