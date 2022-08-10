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

$db = new xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('staking/adventure/stake/body.frame.html', 'body');

if(!isset($_GET['name'])) {
    $html->redirect(
        _ROOT.'staking/adventure/',
        'pageTitle.staking.adventure',
        'redirect.message.staking.error'
    );
    \fDie();
}

$data = \meshal\xAdventure::getData($_GET['name']);

//处理请求
if(
    $_POST['stake'] 
    && is_numeric($_POST['stakeAmount'])
    && \fPost('confirm', '') == md5($user->uid.'stake'.$GLOBALS['deploy']['securityKey'].$user->cp)
) { //这是一个stake请求
    $stat = \fStake(
        $user->uid,
        'adventure',
        $data['name'],
        $_POST['stakeAmount']
    );

    if($stat == 0) {
        \fNotify(
            'notify.stake.succeeded',
            'success',
            array(
                '$stakeContent' => "{?adventureName.{$data['name']}?}",
                '$stakeAmount' => $_POST['stakeAmount']
            )
        );
    } else {
        \fNotify(
            'notify.stake.failed',
            'fatal',
            array(
                '$stakeContent' => "{?adventureName.{$data['name']}?}"
            )
        );
    }
}

elseif(
    $_POST['unstake'] 
    && is_numeric($_POST['unstakeAmount'])
    && \fPost('confirm', '') == md5($user->uid.'stake'.$GLOBALS['deploy']['securityKey'].$user->cp)
) { //这是一个unstake请求
    $stat = \fUnstake(
        $user->uid,
        'adventure',
        $data['name'],
        $_POST['unstakeAmount']
    );

    if($stat == 0) {
        \fNotify(
            'notify.unstake.succeeded',
            'success',
            array(
                '$unstakeContent' => "{?adventureName.{$data['name']}?}",
                '$unstakeAmount' => $_POST['unstakeAmount']
            )
        );
    } else {
        \fNotify(
            'notify.unstake.failed',
            'fatal',
            array(
                '$unstakeContent' => "{?adventureName.{$data['name']}?}"
            )
        );
    }
}

/**
 * 渲染固定资料
 */
$data = \meshal\xAdventure::getData($_GET['name']);
$user->fetch();

//组装冒险类型
$adventureType = '';
if(!empty($data['type'])) {
    $types = array();
    foreach($data['type'] as $k => $t) {
        $types[] = "{?adventureType.{$t}?}";
    }
    $adventureType = implode('{?common.comma?}', $types);
}

//组装队伍要求
if(is_null($data['teamMin']) && is_null($data['teamMax'])) {
    $adventureTeam = '';
}
elseif(!is_null($data['teamMin']) && !is_null($data['teamMax'])) {
    $adventureTeam = "{?common.adventure.requirement.teamSize?} {$data['teamMin']} {?common.tilde?} {$data['teamMax']}";
}
elseif(!is_null($data['teamMin'])) {
    $adventureTeam = "{?common.adventure.requirement.teamSize?} {?common.adventure.requirement.teamMin?} {$data['teamMin']}";
}
else {
    $adventureTeam = "{?common.adventure.requirement.teamSize?} {?common.adventure.requirement.teamMax?} {$data['teamMax']}";
}

//组装实力要求
if(is_null($data['strengthMin']) && is_null($data['strengthMax'])) {
    $adventureStrength = '';
}
elseif(!is_null($data['strengthMin']) && !is_null($data['strengthMax'])) {
    $adventureStrength = "{?common.adventure.requirement.strengthRange?} {$data['strengthMin']} {?common.tilde?} {$data['strengthMax']}";
}
elseif(!is_null($data['strengthMin'])) {
    $adventureStrength = "{?common.adventure.requirement.strengthRange?} {?common.adventure.requirement.strengthMin?} {$data['strengthMin']}";
}
else {
    $adventureStrength = "{?common.adventure.requirement.strengthRange?} {?common.adventure.requirement.strengthMax?} {$data['strengthMax']}";
}

//组装机动花费
$adventureApCost = "{?common.adventure.requirement.apCost?} {$data['apCost']}";

//组装物品loot清单
if(!empty($data['loot'])) {
    $lootList = '';
    foreach ($data['loot'] as $k => $itemName) {
        $lootList .= \meshal\xItem::renderTag($itemName);
    }
    $hideNoneLoot = 'hidden';
} else {
    $lootList = '';
    $hideNoneLoot = '';
}

//组装时间要求
$duration = \fFormatTime(ceil($data['duration'] / $GLOBALS['setting']['adventure']['adventureSpeedFactor']), 'hour');

//组装历史回溯统计
$epoch = \fEpochRange(\fEpoch() - 1);
$adventuresLastEpoch = $db->getCount(
    'adventure_instances',
    array(
        "`templateName` = '{$data['name']}'",
        "`endTime` > '{$epoch['start']}'",
        "`endTime` <= '{$epoch['end']}'"
    )
);
$adventuresTotal = $db->getCount(
    'adventure_instances',
    array(
        "`templateName` = '{$data['name']}'"
    )
);

//组装所有权
$ownerCount = xOwnership::count(
    'adventure',
    $data['name']
);

$ownerList = '';
$owners = xOwnership::getTopOwners(
    'adventure',
    $data['name']
);
if(!empty($owners)) {
    foreach ($owners as $k => $owner) {
        $ownerList .= xUser::renderTag($owner['uid']);
    }
}

//组装staking数据
$lastEpoch = \fEpoch() - 1;
$lastEpochStaking = $db->getSum(
    'epoch_staking',
    '`shares`',
    array(
        "`sealed` = '1'",
        "`epoch` = '{$lastEpoch}'",
        "`type` = 'adventure'",
        "`content` = '{$data['name']}'"
    )
);

$html->set('$adventureName', "{?adventureName.{$data['name']}?}");
if (
    is_null($data['coverImage']) || $data['coverImage'] == ''
    || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$data['coverImage'])
) {
    $html->set('$coverImage', "{?!dirImg?}adventureCover.default.jpg");
} else {
    $html->set('$coverImage', _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$data['coverImage']);
}
$html->set('$name', $data['name']);
$html->set('$description', "{?adventureDesc.{$data['name']}?}");
$html->set('$duration', $duration);
$html->set('$adventureType', $adventureType);
$html->set('$requirement.strength', $adventureStrength);
$html->set('$requirement.apCost', $adventureApCost);
$html->set('$requirement.team', $adventureTeam);
$html->set('$adventures.total', $adventuresTotal);
$html->set('$adventures.lastEpoch', $adventuresLastEpoch);
$html->set('$lootList', $lootList);
$html->set('$hideNoneLoot', $hideNoneLoot);
$html->set('$ownership', $ownerList);
$html->set('$ownerCount', $ownerCount);
$html->set('$hideNoneOwner', $ownerCount == 0 ? '' : 'hidden');
$html->set('$hideOwner', $ownerCount == 0 ? 'hidden' : '');
$html->set('$staking.totalStaked', $data['totalShares']);
$html->set('$staking.totalStaking', xOwnership::getStakingAll('adventure', $data['name']));
$html->set('$staking.myStaked', xOwnership::getShares($user->uid, 'adventure', $data['name']));
$html->set('$staking.myStaking', xOwnership::getStaking($user->uid, 'adventure', $data['name']));
$html->set('$maxUnstake', xOwnership::getStaking($user->uid, 'adventure', $data['name']) + xOwnership::getShares($user->uid, 'adventure', $data['name']));
$html->set('$maxStake', $user->cp);
$html->set('$myStakedRatio', \fRound(
    \fDiv(
        xOwnership::getShares($user->uid, 'adventure', $data['name']),
        $data['totalShares']
    ),
    4
) * 100);
// $html->set('$myStakingRatio', \fRound(
//     \fDiv(
//         xOwnership::getStaking($user->uid, 'adventure', $data['name']),
//         $data['totalShares']
//     ),
//     4
// ) * 100);
$html->set('$url', "{?!dirRoot?}staking/adventure/stake/?adventure={$data['name']}");
$html->set('$confirm', md5($user->uid.'stake'.$GLOBALS['deploy']['securityKey'].$user->cp));

/**
 * 整理历史记录
 */
$comp = array();
for (
    $i=$lastEpoch;
    $i > $lastEpoch - $GLOBALS['setting']['pager']['stake']['epochPerPage'];
    $i--
) { 
    $iRewarded = \fEpochGetReward('adventure', $data['name'], $i);
    $epochNewStake = $db->getSum(
        'epoch_staking',
        '`shares`',
        array(
            "`epoch` = '{$i}'",
            "`type` = 'adventure'",
            "`content` = '{$data['name']}'",
            "`sealed` = '1'"
        )
    );
    $iStaked = $db->getSum(
        'epoch_staking',
        '`shares`',
        array(
            "`epoch` < '{$i}'",
            "`type` = 'adventure'",
            "`content` = '{$data['name']}'",
            "`sealed` = '1'"
        )
    );
    $arr = array();
    $arr['--epoch'] = $i;
    $arr['--adventures'] = \fEpochGetCount('adventure', $data['name'], $i);
    $arr['--staked'] = $iStaked;
    switch (\bccomp($epochNewStake, 0)) {
        case -1:
            $arr['--stakedVariation'] = $epochNewStake;
            $arr['--stakedVariationStyle'] = 'staking-diffSub';
            break;
        
        case 1:
            $arr['--stakedVariation'] = "{?common.add?}{$epochNewStake}";
            $arr['--stakedVariationStyle'] = 'staking-diffAdd';
            break;
            
        default:
            $arr['--stakedVariation'] = "{?common.add?}{$epochNewStake}";
            $arr['--stakedVariationStyle'] = '';
            break;
    }
    $arr['--stakedVariation'] = bccomp($epochNewStake, 0) != -1 ? "{?common.add?}{$epochNewStake}" : $epochNewStake;
    $arr['--rewarded'] = $iRewarded;
    $arr['--interestRate'] = \fDiv($iRewarded, $iStaked) * 100;

    $comp[] = $arr;
}
$html->set(
    '$history',
    $html->duplicate(
        'staking/adventure/stake/dup.history.row.html',
        $comp
    )
);


$html->output();

\fDie();
?>