<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################
$html = new \xHtml;
$user = new \xUser(false);

$html->loadCss('css/meshal.css');
$html->loadTpl('character/viewer/body.frame.html');

if($_GET['id'] == '' || is_null($_GET['id'])) {
    //如果参数传递不正确，重定向
    $html->redirect(
        _ROOT,
        'pageTitle.plaza',
        'redirect.message.character.error'
    );
    \fDie();
}

$char = new \meshal\xChar;
if($char->load($_GET['id']) ===  false) {
    //如果角色id不存在，重定向
    $html->redirect(
        _ROOT,
        'pageTitle.plaza',
        'redirect.message.character.error'
    );
    \fDie();
}

################################################
# 处理属性提升的请求
################################################
switch (\fGet('enhance', '')) {
    case 'm':
        if($char->owner->uid !== $user->uid) { //只能提升自有角色的属性
            \fLog("Error: character({$char->id}) doesn't belong to user({$user->uid})");
            break;
        }

        if(!is_null($char->stat)) { //角色必须在休息中才可以提升属性
            \fNotify('notify.enhanceAttr.characterIsBusy', 'warn', array(
                '--charName' => $char->name
            ));
            break;
        }

        //验证防刷新重入的token
        if($_GET['token'] != md5($char->id.'enhance.m'.$GLOBALS['deploy']['securityKey'].$char->m->base)) {
            break;
        }
        
        $check = $char->strength->cost($char->m->base * $GLOBALS['meshal']['character']['strength']['attr'], 'base');

        if($check == 0) {
            \fNotify('notify.enhanceAttr.success', 'success', array(
                '--charName' => $char->name,
                '--attr' => '{?term.score.attr.might?}',
                '--result' => $char->m->base + 1,
            ));
            $char->event($user->uid, 'enhanceAttr', array('attr' => 'm', 'amount' => 1, 'pp' => $char->m->base * $GLOBALS['meshal']['character']['strength']['attr']));
            $char->m->add('base', 1);
            $char->save();
        }
        
        elseif($check == 1) {
            \fNotify('notify.enhanceAttr.insufficientPotentiality', 'warn', array(
                '--charName' => $char->name
            ));
        }

        break;
        
    case 'a':
        if($char->owner->uid !== $user->uid) { //只能提升自有角色的属性
            \fLog("Error: character({$char->id}) doesn't belong to user({$user->uid})");
            break;
        }

        if(!is_null($char->stat)) { //角色必须在休息中才可以提升属性
            \fNotify('notify.enhanceAttr.characterIsBusy', 'warn', array(
                '--charName' => $char->name
            ));
            break;
        }

        //验证防刷新重入的token
        if($_GET['token'] != md5($char->id.'enhance.a'.$GLOBALS['deploy']['securityKey'].$char->a->base)) {
            break;
        }

        $check = $char->strength->cost($char->a->base * $GLOBALS['meshal']['character']['strength']['attr'], 'base');

        if($check == 0) {
            \fNotify('notify.enhanceAttr.success', 'success', array(
                '--charName' => $char->name,
                '--attr' => '{?term.score.attr.agility?}',
                '--result' => $char->a->base + 1,
            ));
            $char->event($user->uid, 'enhanceAttr', array('attr' => 'a', 'amount' => 1, 'pp' => $char->a->base * $GLOBALS['meshal']['character']['strength']['attr']));
            $char->a->add('base', 1);
            $char->save();
        }
        
        elseif($check == 1) {
            \fNotify('notify.enhanceAttr.insufficientPotentiality', 'warn', array(
                '--charName' => $char->name
            ));
        }

        break;

    case 's':
        if($char->owner->uid !== $user->uid) { //只能提升自有角色的属性
            \fLog("Error: character({$char->id}) doesn't belong to user({$user->uid})");
            break;
        }

        if(!is_null($char->stat)) { //角色必须在休息中才可以提升属性
            \fNotify('notify.enhanceAttr.characterIsBusy', 'warn', array(
                '--charName' => $char->name
            ));
            break;
        }

        //验证防刷新重入的token
        if($_GET['token'] != md5($char->id.'enhance.s'.$GLOBALS['deploy']['securityKey'].$char->s->base)) {
            break;
        }

        $check = $char->strength->cost($char->s->base * $GLOBALS['meshal']['character']['strength']['attr'], 'base');

        if($check == 0) {
            \fNotify('notify.enhanceAttr.success', 'success', array(
                '--charName' => $char->name,
                '--attr' => '{?term.score.attr.spirit?}',
                '--result' => $char->s->base + 1,
            ));
            $char->event($user->uid, 'enhanceAttr', array('attr' => 's', 'amount' => 1, 'pp' => $char->s->base * $GLOBALS['meshal']['character']['strength']['attr']));
            $char->s->add('base', 1);
            $char->save();
        }
        
        elseif($check == 1) {
            \fNotify('notify.enhanceAttr.insufficientPotentiality', 'warn', array(
                '--charName' => $char->name
            ));
        }

        break;

    default:
        break;
}

################################################
# 渲染角色卡
################################################
$html->set('$charId', $_GET['id']);
$html->set('$charName', $char->name);
$html->set('$portrait', 
    (
        is_null($char->portrait) 
        || $char->portrait == '' 
        || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait)
    ) 
        ? $GLOBALS['deploy']['siteRoot'].'{?!relDirImg?}cardBg.default.jpg' 
        : $GLOBALS['deploy']['siteRoot'].DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait
);
$html->set('$bio', substr(htmlentities($char->bio), 0, 200));

$html->headInject('character/viewer/head.meta.html');


$item = new \meshal\xItem;

################################################
# 渲染属性提升
################################################
if($char->owner->uid != $user->uid) {
    $html->set('$enhance', '');
} else {
    $html->set('$enhance', $html->readTpl('character/viewer/body.enhance.html'));

    if(is_null($char->stat)) { //如果角色不在营地，统一修改按钮主文字显示
        $html->set('$enhanceEnhancable', '');
        $html->set('$enhanceNotResting', 'hidden');
    } else {
        $html->set('$enhanceEnhancable', 'hidden');
        $html->set('$enhanceNotResting', '');
    }

    $html->set('$enhanceAttrBase.m', $char->m->base);
    $html->set('$enhanceAttrNext.m', $char->m->base + 1);
    $html->set('$enhanceAttrCost.m', $char->m->base * $GLOBALS['meshal']['character']['strength']['attr']);
    if(
        $char->strength->pp < $char->m->base * $GLOBALS['meshal']['character']['strength']['attr']
        || !is_null($char->stat)
    ) {
        $html->set('$enhanceUrl.m', '#');
        $html->set('$enhanceEligibility.m', 'bgGrey1');
        $html->set('$enhanceButton.m', '{?button.enhanceAttr.insufficientPotentiality?}');
    } else {
        $html->set('$enhanceUrl.m', "?id={$_GET['id']}&enhance=m&token=".md5($char->id.'enhance.m'.$GLOBALS['deploy']['securityKey'].$char->m->base)."&_back={$_GET['_back']}");
        $html->set('$enhanceEligibility.m', 'bgGreen1');
        $html->set('$enhanceButton.m', '{?button.enhanceAttr.m?}');
    }

    $html->set('$enhanceAttrBase.a', $char->a->base);
    $html->set('$enhanceAttrNext.a', $char->a->base + 1);
    $html->set('$enhanceAttrCost.a', $char->a->base * $GLOBALS['meshal']['character']['strength']['attr']);
    if(
        $char->strength->pp < $char->a->base * $GLOBALS['meshal']['character']['strength']['attr']
        || !is_null($char->stat)
    ) {
        $html->set('$enhanceUrl.a', '#');
        $html->set('$enhanceEligibility.a', 'bgGrey1');
        $html->set('$enhanceButton.a', '{?button.enhanceAttr.insufficientPotentiality?}');
    } else {
        $html->set('$enhanceUrl.a', "?id={$_GET['id']}&enhance=a&token=".md5($char->id.'enhance.a'.$GLOBALS['deploy']['securityKey'].$char->a->base)."&_back={$_GET['_back']}");
        $html->set('$enhanceEligibility.a', 'bgGreen1');
        $html->set('$enhanceButton.a', '{?button.enhanceAttr.a?}');
    }

    $html->set('$enhanceAttrBase.s', $char->s->base);
    $html->set('$enhanceAttrNext.s', $char->s->base + 1);
    $html->set('$enhanceAttrCost.s', $char->s->base * $GLOBALS['meshal']['character']['strength']['attr']);
    if(
        $char->strength->pp < $char->s->base * $GLOBALS['meshal']['character']['strength']['attr']
        || !is_null($char->stat)
    ) {
        $html->set('$enhanceUrl.s', '#');
        $html->set('$enhanceEligibility.s', 'bgGrey1');
        $html->set('$enhanceButton.s', '{?button.enhanceAttr.insufficientPotentiality?}');
    } else {
        $html->set('$enhanceUrl.s', "?id={$_GET['id']}&enhance=s&token=".md5($char->id.'enhance.s'.$GLOBALS['deploy']['securityKey'].$char->s->base)."&_back={$_GET['_back']}");
        $html->set('$enhanceEligibility.s', 'bgGreen1');
        $html->set('$enhanceButton.s', '{?button.enhanceAttr.s?}');
    }
}


################################################
# 渲染装备物品
################################################
$equipment = '';
$inventory = $char->inventory->export();
foreach ($inventory['equipment'] as $slotName => $slotData) {
    if(!empty($slotData['items'])) {
        foreach ($slotData['items'] as $itemName => $amount) {
            if($amount > 0) {
                $item->load($itemName);
                $item->amount = $amount;
                if($user->uid == $char->owner->uid) {
                    if(is_null($char->stat)) {
                        $item->addCtrl(
                            _ROOT."item/doff/?item={$item->name}&char={$char->id}".\fBackUrl(),
                            'button.item.doff',
                            'bgOpaRed1 colorWhite1'
                        );
                    } else {
                        $item->addCtrl(
                            "#",
                            'button.item.doff',
                            'bgOpaGrey1 colorWhite1'
                        );
                    }
                }
                $equipment .= $item->render();
            }
        }
    }
}

$html->set('$equipment', $equipment);
$html->set('$equipmentEmpty', $equipment == '' ? '' : 'hidden');

################################################
# 渲染携带物品
################################################
$carrying = '';
if(!empty($char->inventory->carrying)) {
    foreach ($char->inventory->carrying as $itemName => $amount) {
        $item->load($itemName);
        $item->amount = $amount;

        //使用
        if(
            $user->uid == $char->owner->uid
            && !is_null($item->data['use']['efx'])
        ) {
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

            if($checkAll * $checkAny == 0) { //检查没有通过，禁用使用按钮
                $item->addCtrl(
                    "#",
                    'button.item.use',
                    'bgOpaGrey1 colorWhite1'
                );
            } else { //检查通过，渲染使用按钮
                $item->addCtrl(
                    _ROOT."item/use/confirm/?item={$item->name}&char={$char->id}&carrying=1&submit=1".\fBackUrl(),
                    'button.item.use',
                    'bgOpaGreen1 colorWhite1'
                );
            }
        }

        //装备
        if(
            $user->uid == $char->owner->uid
            && !is_null($item->data['occupancy']['type'])
        ) {
            if(
                is_null($char->stat)
                && $char->inventory->getAvailableSlots($item->occupancy['type']) >= $item->occupancy['slots']
            ) {
                $item->addCtrl(
                    _ROOT."item/equip/confirm/?item={$item->name}&char={$char->id}&carrying=1&submit=1".\fBackUrl(),
                    'button.item.equip',
                    'bgOpaGreen1 colorWhite1'
                );
            } else {
                $item->addCtrl(
                    "#",
                    'button.item.equip',
                    'bgOpaGrey1 colorWhite1'
                );
            }
        }

        //放回仓库
        if($user->uid == $char->owner->uid) {
            if(is_null($char->stat)) {
                $item->addCtrl(
                    _ROOT."item/putback/?item={$item->name}&char={$char->id}".\fBackUrl(),
                    'button.item.putback',
                    'bgOpaBlue1 colorWhite1'
                );
            } else {
                $item->addCtrl(
                    "#",
                    'button.item.putback',
                    'bgOpaGrey1 colorWhite1'
                );
            }
        }

        $carrying .= $item->render();
    }
    $html->set('$carrying', $carrying);
    $html->set('$carryingEmpty', 'hidden');
} else {
    $html->set('$carryingEmpty', '');
    $html->set('$carrying', '');
}

# 添加操作
// 分享
$char->addCtrl(
    \fTwitterShareUrl(
        $html->dbLang('twitter.shareCharacter'),
        "{$GLOBALS['deploy']['siteRoot']}c/?id={$_GET['id']}",
        $GLOBALS['social']['twitter']['hashtag']['character'],
        array('$charName' => $char->name)
    ),
    'button.characterController.tweet',
    'colorWhite1 bgOpaBlue2',
    'any',
    '_blank'
);

// 编辑
$char->addCtrl(
    _ROOT."character/edit/?id={$_GET['id']}".\fBackUrl(),
    'button.characterController.edit'
);

// 招募
if(is_null($char->owner->uid)) {
    $char->addCtrl(
        _ROOT."character/recruit/?id={$_GET['id']}".\fBackUrl(),
        'button.characterController.recruit',
        'colorWhite1 bgOpaGreen1',
        'guest'
    );
}

// 放逐
$char->addCtrl( 
    _ROOT."character/expel/?id={$_GET['id']}".\fBackUrl(),
    'button.characterController.expel',
    'colorWhite1 bgOpaRed1',
    array('owner'),
    null,
    false,
    array('adventure')
);

// 版本升级
if(\fCheckVersion($char->version, $GLOBALS['meshal']['version']['character']) == -1) {
    $char->addCtrl(
        _ROOT."character/upgrade/?id={$_GET['id']}".\fBackUrl(),
        'button.characterController.upgrade',
        'colorWhite1 bgOpaGreen1 nzButtonBreath',
        null,
        null,
        true
    );
}

$html->set('$characterSheet', $char->render(null, true, $user));
$html->output();
\fDie();
?>