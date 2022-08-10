<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';
################################################
# 初始化结束
################################################
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('item/transfer/body.frame.html', 'body');

//检查参数合法性
if(is_null(\fGet('item'))) {
    \fLog('Error: no item given');
    $html->redirectBack(
        'redirect.message.item.transferError'
    );
    \fDie();
}


$item = new \meshal\xItem; 
//加载物品
if($item->load($_GET['item']) === false) { //检查物品是否在数据库中存在
    \fLog("Error: item {$_GET['item']} doesn't exist in the library");
    $html->redirectBack(
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
    $html->redirectBack(
        'redirect.message.item.transferInsufficient'
    );
    \fDie();
}

$item->amount = $stock[0]['amount']; //设置物品库存数量

//获取角色数量
$charCount = $db->getCount(
    'characters',
    array(
        "`ownerId` = '{$user->uid}'"
    )
);

//如果用户没有角色则报错
if($charCount == 0) {
    \fLog("User({$user->uid}) doesn't have any characters");
    $html->redirectBack(
        'redirect.message.item.transferNoChars'
    );
    \fDie();
}


if($charCount > 0) {
    # 根据当前页取角色
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['character']['charactersPerPage'];

    $query = $db->getArr(
        'characters',
        array(
            "`ownerId` = '{$user->uid}'"
        ),
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['character']['charactersPerPage']}",
        MYSQLI_NUM,
        'name',
        'ASC'
    );

    $renderer = new \meshal\xChar; //创建一个临时角色对象用于渲染
    $charList = '';
    if($query !== false) {
        foreach ($query as $k => $charData) {
            $renderer->load($charData['id']);

            #添加操作
            if(
                !is_null($renderer->stat) //不在休息中的角色不可接受物品
            ) {
                $renderer->addCtrl(
                    "#",
                    'button.characterController.notInCamp',
                    'bgOpaGrey1 colorWhite1',
                    array('any'),
                    null,
                    true
                );

                $charList .= $renderer->renderLite(
                    true,
                    $user,
                    'filterOldPhoto'
                );
            }
            
            elseif( //角色版本过老
                \fCheckVersion($renderer->version, $GLOBALS['meshal']['version']['character']) == -1
            ) {
                $renderer->addCtrl(
                    "#",
                    'button.characterController.characterVersionObsolete',
                    'bgOpaGrey1 colorWhite1',
                    array('any'),
                    null,
                    true
                );
                
                $charList .= $renderer->renderLite(
                    true,
                    $user,
                    'filterOldPhoto'
                );
            }

            else {
                $renderer->addCtrl(
                    "confirm/?item={$_GET['item']}&char={$charData['id']}".\fBackUrl(),
                    'button.characterController.select',
                    'bgOpaGreen1 colorWhite1',
                    array('any')
                );

                $charList .= $renderer->renderLite(
                    true,
                    $user
                );
            }
        }
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $charCount,
            $GLOBALS['setting']['pager']['character']['charactersPerPage'],
            '?item='.$_GET['item'].'&page={?$page?}&_back='.$_GET['_back']
        )
    );
} else {
    $html->set('$pager', '');
    $charList = $html->readTpl('guide/charEmpty.html');
}

// foreach ($characters as $k => $cur) {
//     $charRenderer->load($cur['id']);
//     $characterList .= $charRenderer->render('lite');
// }

//渲染物品
$html->set('$itemCard', $item->render());

//渲染角色列表
$html->set('$charList', $charList);


$html->output();

\fDie();
?>