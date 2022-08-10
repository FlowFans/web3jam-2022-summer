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
$event = new \xEvent;

$html->loadCss('css/meshal.css');

$event->load(1);
$eventStat = $event->check();
$html->set('$eventCard', $event->render());


################################################
# 兑换 Code
################################################
if($_GET['redeem'] == md5($user->uid.'event.202201.code'.$GLOBALS['deploy']['securityKey'])) {
    # 检查活动是否结束
    $event->check(true);

    # 检查是否已兑换过
    $redeemed = $db->getCount(
        'event_202201_code',
        array(
            "`redeemerId` = '{$user->uid}'"
        ),
        null,
        1
    );

    if($redeemed != 0) {
        $html->redirect(
            './',
            'pageTitle.event.202201',
            'redirect.message.event.202201.code.alreadyRedeemed'
        );
        \fDie();
    }

    //取一条记录
    $query = $db->getArr(
        'event_202201_code',
        array(
            "`redeemerId` is NULL"
        ),
        null,
        1
    );

    if($query === false) { //超过活动库存
        $html->redirect(
            './',
            'pageTitle.event.202201',
            'redirect.message.event.202201.code.outOfStock'
        );
        \fDie();
    }

    # 检查物品
    $q = 1;
    foreach ($event->data['code'] as $itemName => $amount) {
        $stock = $user->inventory->getStock($itemName);
        $q *= $stock >= $amount ? 1 : 0;
    }

    if($q == 0) {
        $html->redirect(
            './',
            'pageTitle.event.202201',
            'redirect.message.event.202201.insufficientItem'
        );
        \fDie();
    }

    //扣除物品
    foreach ($event->data['code'] as $itemName => $amount) {
        $user->inventory->remove($itemName, $amount);
    }

    //添加记录
    $db->update(
        'event_202201_code',
        array(
            'redeemerId' => $user->uid
        ),
        array(
            "`code` = '{$query[0]['code']}'"
        ),
        1
    );

    \fMsg(
        $user->uid,
        'event',
        'message.event.202201.codeRedeemed',
        array('$secretCode' => $query[0]['code'])
    );

    $html->redirect(
        './',
        'pageTitle.event.202201',
        'redirect.message.event.202201.code.redeemSuccess'
    );
    \fDie();
}

################################################
# 兑换 Helmet
################################################
elseif($_GET['redeem'] ==md5($user->uid.'event.202201.helmet'.$GLOBALS['deploy']['securityKey'])) {
    # 检查活动是否结束
    $event->check(true);

    # 检查是否已兑换过
    $redeemed = $db->getCount(
        'event_202201_helmet',
        array(
            "`redeemerId` = '{$user->uid}'"
        ),
        null,
        1
    );

    if($redeemed != 0) {
        $html->redirect(
            './',
            'pageTitle.event.202201',
            'redirect.message.event.202201.helmet.alreadyRedeemed'
        );
        \fDie();
    }

    //检查总发放量
    $count = $db->getCount(
        'event_202201_helmet',
        array()
    );

    if($count >= $event->data['helmetLimit']) { //超过活动库存
        $html->redirect(
            './',
            'pageTitle.event.202201',
            'redirect.message.event.202201.helmet.outOfStock'
        );
        \fDie();
    }

    # 检查物品
    $q = 1;
    foreach ($event->data['helmet'] as $itemName => $amount) {
        $stock = $user->inventory->getStock($itemName);
        $q *= $stock >= $amount ? 1 : 0;
    }

    if($q == 0) {
        $html->redirect(
            './',
            'pageTitle.event.202201',
            'redirect.message.event.202201.insufficientItem'
        );
        \fDie();
    }

    //扣除物品
    foreach ($event->data['helmet'] as $itemName => $amount) {
        $user->inventory->remove($itemName, $amount);
    }

    //添加记录
    $db->insert(
        'event_202201_helmet',
        array(
            'redeemerId' => $user->uid
        )
    );

    \fMsg(
        $user->uid,
        'event',
        'message.event.202201.footballHelmetRedeemed',
        array('$itemName' => '{?itemName.footballHelmet?}', '$amount' => 1)
    );

    $user->inventory->add('footballHelmet', 1);
    $html->redirect(
        './',
        'pageTitle.event.202201',
        'redirect.message.event.202201.helmet.redeemSuccess'
    );
    \fDie();
}

################################################
# 渲染
################################################
else {
    $html->loadTpl('event/202201/frame.html', 'body');
    if($eventStat != 1) {
        $html->loadTpl('event/202201/exchange.html');
    }

    # code 兑换
    $count = $db->getCount(
        'event_202201_code',
        array(
            "`redeemerId` = '{$user->uid}'"
        ),
        null,
        1
    );

    $totalRedeemed = $db->getCount(
        'event_202201_code',
        array(
            "`redeemerId` is not NULL"
        )
    );

    $total = $db->getCount(
        'event_202201_code',
        array()
    );

    $comp = array();
    $stat = 1;
    foreach ($event->data['code'] as $itemName => $amount) { //拼装物品要求列表
        $stock = $user->inventory->getStock($itemName);
        $comp[] = array(
            '--itemTag' => \meshal\xItem::renderTag($itemName),
            '--required' => $amount,
            '--stock' => $stock,
            '--disabled' => $stock >= $amount ? '' : 'filterDisabled'
        );
        $stat *= $stock >= $amount ? 1 : 0;
    }

    if($count == 0) {
        if($eventStat != 0) {
            $html->set('$codeRedeemShow', 'hidden');
            $html->set('$codeUrl', '#');
        }
        elseif($stat == 1) {
            $html->set('$codeRedeemShow', 'common-controller-alwaysShow');
            $html->set('$codeUrl', '?redeem='.md5($user->uid.'event.202201.code'.$GLOBALS['deploy']['securityKey']));
            $html->set('$codeButton', '{?common.redeem?}');
        }
        else {
            $html->set('$codeRedeemShow', 'common-controller-alwaysShow filterDisabled');
            $html->set('$codeUrl', '#');
            $html->set('$codeButton', '{?common.redeem?}');
        }
        $html->set('$codeRedeemed', '');
        $html->set('$codeSticker', 'hidden');
    } else {
        $html->set('$codeRedeemShow', 'hidden');
        $html->set('$codeRedeemed', 'filterOldPhoto');
        $html->set('$codeUrl', '#');
        $html->set('$codeSticker', '');
    }

    if($total > $totalRedeemed) {
        $html->set('$codeRedeemCount', $total - $totalRedeemed);
        $html->set('$codeRedeemStat', '{?event.202201.code.redeemed?}');
    } else {
        $html->set('$codeRedeemStat', '{?event.202201.code.outOfStock?}');
    }

    $html->set(
        '$codeRequireItems',
        $html->duplicate('event/202201/itemList.html', $comp)
    );

    # Helmet 兑换
    $totalRedeemed = $db->getCount(
        'event_202201_helmet'
    );

    $count = $db->getArr(
        'event_202201_helmet',
        array(
            "`redeemerId` = '{$user->uid}'"
        ),
        null,
        1
    );


    $comp = array();
    $stat = 1;
    foreach ($event->data['helmet'] as $itemName => $amount) { //拼装物品要求列表
        $stock = $user->inventory->getStock($itemName);
        $comp[] = array(
            '--itemTag' => \meshal\xItem::renderTag($itemName),
            '--required' => $amount,
            '--stock' => $stock,
            '--disabled' => $stock >= $amount ? '' : 'filterDisabled'
        );
        $stat *= $stock >= $amount ? 1 : 0;
    }

    if($count == 0) {
        if($eventStat != 0) {
            $html->set('$helmetRedeemShow', 'hidden');
            $html->set('$helmetUrl', '#');
        }
        elseif($stat == 1) {
            $html->set('$helmetRedeemShow', 'common-controller-alwaysShow');
            $html->set('$helmetUrl', '?redeem='.md5($user->uid.'event.202201.helmet'.$GLOBALS['deploy']['securityKey']));
        }
        else {
            $html->set('$helmetRedeemShow', 'common-controller-alwaysShow filterDisabled');
            $html->set('$helmetUrl', '#');
        }
        $html->set('$helmetRedeemed', '');
        $html->set('$helmetSticker', 'hidden');
    } else {
        $html->set('$helmetRedeemShow', 'hidden');
        $html->set('$helmetRedeemed', 'filterOldPhoto');
        $html->set('$helmetUrl', '#');
        $html->set('$helmetSticker', '');
    }

    if($event->data['helmetLimit'] > $totalRedeemed) {
        $html->set('$helmetRedeemCount', $event->data['helmetLimit'] - $totalRedeemed);
        $html->set('$helmetRedeemStat', '{?event.202201.helmet.redeemed?}');
    } else {
        $html->set('$helmetRedeemStat', '{?event.202201.helmet.outOfStock?}');
    }

    $html->set(
        '$helmetRequireItems',
        $html->duplicate('event/202201/itemList.html', $comp)
    );


    $html->output();
}
\fDie();
?>