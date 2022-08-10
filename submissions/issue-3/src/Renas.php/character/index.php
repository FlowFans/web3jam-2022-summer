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
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('character/body.frame.html', 'body');
$html->set('$chargen.randomize.cpCost', $GLOBALS['cp']['character']['generate']);

$charCount = $db->getCount(
    'characters',
    array(
        "`ownerId` = '{$user->uid}'"
    )
);

$html->set('$generateNew', $html->readTpl('character/body.frame.buttons.html'));

//初始化slot
if($db->getArr(
    'character_slot',
    array(
        "`uid` = {$user->uid}"
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

//如果还有slot且cp够，则可以创建新角色，否则不行
$currentChars = $db->getCount('characters', array("`ownerId` = '{$user->uid}'"));
$rawSlots = $db->getArr('character_slot', array("`uid` = '{$user->uid}'"))[0]['slot'] + $GLOBALS['meshal']['character']['initialSlot'];
$charSlots =  $rawSlots + $user->efx->modifier['survivorSlots'];
$availableSlots = $charSlots - $currentChars;

$html->set('$character.slot.available', $availableSlots);
$html->set('$chargen.currentChars', $currentChars);
$html->set('$slots.current', $charSlots);


//stage中有没有存量角色
$stageEmpty = $db->getArr(
    'character_stage',
    array(
        "`uid` = '{$user->uid}'"
    ),
    null,
    1,
) === false ? true : false;

//检查是否有足够的cp扩充slot
$html->set('$characterSlot.expand.cpCost', $GLOBALS['cp']['character']['expand'] * $rawSlots);
if(bccomp($user->cp, $GLOBALS['cp']['character']['expand'] * $rawSlots) == -1) { //没有足够cp
    $html->set('$expand.url', '#');
    $html->set('$expand.disable', 'nzButtonDisabled');
} else { //有足够的cp
    $html->set('$expand.url', '{?!dirRoot?}character/expand/?confirm='.md5($user->uid.'expand'.$GLOBALS['deploy']['securityKey'].$rawSlots).\fBackUrl());
    $html->set('$expand.disable', '');
}


//检查是否有足够的cp招人
$cpEnoughForRecruit = bccomp($user->cp, $GLOBALS['cp']['character']['generate']) == -1 ? false : true;

//slot有空位
$slotEnough = $availableSlots <= 0 ? false : true;

$html->set('$slotStat', '{?button.characterSlot.sufficient?}');
switch (TRUE) {
    case (
        $slotEnough === false
    ):
        //空位不够，直接disable
        $html->set('$slotStat', '{?button.characterSlot.insufficient?}');
        $html->set('$generate.disable', 'nzButtonDisabled');
        $html->set('$generate.url', '#');
        break;
    
    case (
        $cpEnoughForRecruit === false
        && $stageEmpty === true
    ):
        //cp不够也没有库存角色，disable
        $html->set('$generate.disable', 'nzButtonDisabled');
        $html->set('$generate.url', '#');
        break;

    case (
        $stageEmpty === false
    ):
        //stage有角色
        $html->set('$generate.disable', '');
        $html->set('$generate.url', '{?!dirRoot?}character/spawn/');
        break;

    default:
        //stage没角色
        $html->set('$generate.disable', '');
        $html->set('$generate.url', '{?!dirRoot?}character/spawn/?action=new');
        break;
}



if($charCount > 0) {

    # 根据当前页取角色
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['character']['charactersPerPage'];

    $queryChars = $db->getArr(
        'characters',
        array(
            "`ownerId` = '{$user->uid}'"
        ),
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['character']['charactersPerPage']}",
        MYSQLI_NUM,
        'lastUpdate',
        'DESC'
    );

    #组装角色列表
    if($queryChars !== false) {
        $characterList = '';
        $charRenderer = new meshal\xChar;
        //拼装角色数据
        foreach ($queryChars as $cur) {
            $charRenderer->load($cur['id']);
            # 添加操作
            // 分享
            $charRenderer->addCtrl(
                \fTwitterShareUrl(
                    $html->dbLang('twitter.shareCharacter'),
                    "{$GLOBALS['deploy']['siteRoot']}c/?id={$charRenderer->id}",
                    $GLOBALS['social']['twitter']['hashtag']['character'],
                    array('$charName' => $charRenderer->name)
                ),
                'button.characterController.tweet',
                'colorWhite1 bgOpaBlue2',
                'any',
                '_blank'
            );
            
            // 管理
            $charRenderer->addCtrl(
                _ROOT.'c/?id={?--charId?}'.\fBackUrl(),
                'button.characterController.manage'
            );

            // 编辑
            $charRenderer->addCtrl(
                _ROOT.'character/edit/?id={?--charId?}'.\fBackUrl(),
                'button.characterController.edit'
            );

            // 放逐
            $charRenderer->addCtrl( 
                _ROOT.'character/expel/?id={?--charId?}'.\fBackUrl(),
                'button.characterController.expel',
                'colorWhite1 bgOpaRed1',
                array('owner'),
                null,
                false,
                array('adventure')
            );

            // 版本升级
            if(\fCheckVersion($charRenderer->version, $GLOBALS['meshal']['version']['character']) == -1) {
                $charRenderer->addCtrl(
                    _ROOT.'character/upgrade/?id={?--charId?}'.\fBackUrl(),
                    'button.characterController.upgrade',
                    'colorWhite1 bgOpaGreen1 nzButtonBreath',
                    null,
                    null,
                    true
                );
            }

            $characterList .= $charRenderer->render(null, true, $user);
        }

        $html->set('$characterList',$characterList);
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $charCount,
            $GLOBALS['setting']['pager']['character']['charactersPerPage'],
            '?page={?$page?}&_back='.\fGet('_back')
        )
    );
} else {
    $html->set('$characterList', '');
    $html->set('$pager', '');
}



$html->output();

\fDie();
?>