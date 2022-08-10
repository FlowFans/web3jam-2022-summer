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
$char = new \meshal\xChar;

//初始化slot
if($db->getArr(
    'character_slot',
    array(
        "`uid` = '{$user->uid}'"
    ),
    null,
    1
) === false) {
    //没有记录，创建一条记录
    $db->insert(
        'character_slot',
        array(
            'uid' => $user->uid,
            'slot' => 0
        )
    );
}

$currentChars = $db->getCount('characters', array("`ownerId` = '{$user->uid}'"));
$charSlots = $db->getArr('character_slot', array("`uid` = '{$user->uid}'"))[0]['slot'] + $GLOBALS['meshal']['character']['initialSlot'] + $user->efx->modifier['survivorSlots'];
$availableSlots = $charSlots - $currentChars;

$html->loadCss('css/meshal.css');
$html->set('$recruit.cpCost', $GLOBALS['cp']['character']['recruit']);
$html->set('$character.slot.available', $availableSlots);


if($_GET['confirm'] && $_GET['id']) { //有confirmCode，做招募
    //加载角色
    $char->load($_GET['id']);

    if(//检查招募的有效性
        !is_null($char->owner->uid)
        || is_null($user->uid)
        || is_null($char->id)
        || $_GET['confirm'] !== md5($user->uid.'recruit'.$GLOBALS['deploy']['securityKey'].$char->id)
    ) {
        $html->redirectBack(
            'redirect.message.character.invalidRecruit'
        );
        \fDie();
    }

    //检查是否有足够的slot招募
    if(
        $availableSlots <= 0
    ) {
        $html->loadTpl('character/recruit/body.frame.html');
        \fNotify('notify.recruit.noSlots', 'fatal');
        localFetch();
        $html->output();
        \fDie();
    }

    if(//检查是否有足够的CP招募
        bccomp($user->cp, $GLOBALS['cp']['character']['recruit']) == -1
    ) {
        $html->loadTpl('character/recruit/body.frame.html');
        \fNotify('notify.recruit.insufficientCP', 'fatal');
        localFetch();
        $html->output();
        \fDie();
    }

    $char->owner->uid = $user->uid; //建立和用户的拥有关系
    $stat = $char->save();

    if($stat == false){ //没有修改成功
        $html->redirectBack(
            'redirect.message.character.invalidRecruit'
        );
        \fDie();
    } else { //绑定成功
        //在interaction表中记录此次招募
        $char->event(
            $user->uid,
            'recruit'
        );

        //扣除用户的cp
        $user->cp = \fSub(
            $user->cp, 
            $GLOBALS['cp']['character']['recruit'],
            $GLOBALS['cp']['decimal']
        );
        $user->save();
        $user->fetch();

        //给创作者发二级佣金
        $creator = $db->getArr(
            'users',
            array(
                "`uid` = '{$char->creator->uid}'"
            ),
            null,
            1
        );
        if($creator !== false) { //累加cp
            $creatorIncome = \fmul(
                $GLOBALS['cp']['character']['recruitCreatorFeeRate'],
                $GLOBALS['cp']['character']['recruit'],
                $GLOBALS['cp']['decimal']
            );
            $creatorCP = \fAdd(
                $creator[0]['cp'], 
                $creatorIncome,
                $GLOBALS['cp']['decimal']
            );

            $statCP = $db->update( //写入数据
                'users',
                array(
                    'cp' => $creatorCP
                ),
                array(
                    "`uid` = '{$char->creator->uid}'"
                ),
                1
            );

            if(
                $statCP !== false
            ) { 
                if( //给creator发消息
                    $user->uid !== $char->creator->uid
                ) {
                    \fMsg(
                        $char->creator->uid,
                        'recruit',
                        'message.recruit.character.creator',
                        array(
                            '$recruitor.username' => $user->username,
                            '$characterName' => $char->name,
                            '$recruit.fee' => $creatorIncome
                        )
                    );
                } 

                else { //creator自己买入
                    \fMsg(
                        $char->creator->uid,
                        'recruit',
                        'message.recruit.character.creatorSelf',
                        array(
                            '$characterName' => $char->name
                        )
                    );
                }
                
            }
        }

        //给创作者发消息


        $html->set('$charName', $char->name, true);
        $html->redirectBack(
            'redirect.message.character.recruited'
        );
        \fDie();
    }
} 

elseif(!$_GET['id']) { //没有设置id
    $html->redirectBack(
        'redirect.message.character.invalidRecruit'
    );
    \fDie();
}

else { //默认显示角色并提供选项
    $html->loadTpl('character/recruit/body.frame.html');
    
    localFetch();
    $html->set('$charName', $char->name, true);
    $html->output();
    \fDie();
}

/**
 * 取角色和用户数据并进行预渲染
 */
function localFetch() {
    global $char;
    global $user;
    global $html;
    global $availableSlots;

    $char->load($_GET['id']);
    $char->viewerUrl = '#';

    if( //检查这个角色有没有owner
        !is_null($char->owner->uid)
        || is_null($char->id)
    ) {
        $html->redirectBack(
            'redirect.message.character.invalidRecruit'
        );
        \fDie();
    }

    //检查用户是否有足够的cp
    $disable = 0;
    if(bccomp($user->cp, $GLOBALS['cp']['character']['recruit']) == -1) {
        $disable ++;
    }

    //检查用户是否有足够的slot
    if($availableSlots <= 0) {
        $disable ++;
        $html->set('$slots', '{?button.characterSlot.insufficient?}');
    } else {
        $html->set('$slots', '{?button.characterSlot.sufficient?}');
    }

    //汇总检查结果
    if($disable > 0) {
        $html->set('$recruitUrl', '#');
        $html->set('$recruit.disable', 'nzButtonDisabled');
    } else {
        $html->set('$recruitUrl', '?id={?$charId?}&confirm={?$confirmCode?}&_back='.\fGet('_back'));
        $html->set('$recruit.disable', '');
    }

    //对confirmCode做加密，$GLOBALS['deploy']['securityKey']作为salt
    $html->set('$confirmCode', md5($user->uid.'recruit'.$GLOBALS['deploy']['securityKey'].$char->id));

    $html->set('$characterPreview',$char->render());
    $html->set('$charId', $_GET['id']);
}
?>