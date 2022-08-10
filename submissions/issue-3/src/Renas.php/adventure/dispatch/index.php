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

if(!isset($_GET['adventure'])) {
    $html->redirect(
        _ROOT.'adventure/',
        'pageTitle.adventure',
        'redirect.message.adventure.error'
    );
    \fDie();
}

$adventureData = \meshal\xAdventure::getData($_GET['adventure']);

$query = $db->getArr(
    'adventure_today',
    array(
        "`uid` = '{$user->uid}'",
        "`adventureName` = '{$_GET['adventure']}'"
    ),
    null,
    1
);

if($query === false) {
    $html->redirect(
        _ROOT.'adventure/',
        'pageTitle.adventure',
        'redirect.message.adventure.error'
    );
    \fDie();
}

$stageData = json_decode($query[0]['stageData'], true); //加载冒险的stage data
$team = new \meshal\xTeam; //创建临时队伍对象

if(!empty($stageData['team'])) {
    foreach ($stageData['team'] as $k => $charId) { //遍历stage data中的team数组，向队伍对象添加该角色
        $charTmp = $db->getArr(
            'characters',
            array(
                "`id` = '{$charId}'"
            ),
            null,
            1
        );

        if($charTmp !== false) {
            if(is_null($charTmp[0]['stat'])) { //检查角色的状态，防止在启动这个冒险之前角色被其他事占用了
                $team->add($charId);
                if($team->members[$charId]->ap->current < $adventureData['apCost']) {
                    $team->remove($charId);
                }
            }
        }
    }
}

switch (\fGet('action')) {
    case 'dispatch': //进行派遣
        $adventure = new \meshal\xAdventure;
        $check = $adventure->new(
            $_GET['adventure'],
            ...$team->export()
        );
        if($check == 0) {
            $adventure->save();
            $db->delete( //从Today中删除这条记录
                'adventure_today',
                array(
                    "`uid` = '{$user->uid}'",
                    "`adventureName` = '{$_GET['adventure']}'"
                ),
                1
            );

            $html->set('$adventureName', "{?adventureName.{$adventureData['name']}?}");
            $html->redirect(
                _ROOT.'adventure/',
                'pageTitle.adventure',
                'redirect.message.adventure.dispatched'
            );
            \fDie();
        } else {
            \fLog("Error while creating new adventure: error code = {$check}");
            $html->redirect(
                _ROOT.'adventure/',
                'pageTitle.adventure',
                'redirect.message.adventure.dispatchError'
            );
            \fDie();
            break;
        }
        break;

    case 'add': //向队伍添加角色
        $team->add($_GET['char']);
        $stageData['team'] = $team->export();

        $db->update(
            'adventure_today',
            array(
                'stageData' => json_encode($stageData)
            ),
            array(
                "`uid` = '{$user->uid}'",
                "`adventureName` = '{$_GET['adventure']}'" 
            ),
            1
        );

        header("Location: "._ROOT."adventure/dispatch/?adventure={$_GET['adventure']}");

        break;

    case 'remove': //从队伍移除角色
        $team->remove($_GET['char']);
        $stageData['team'] = $team->export();

        $db->update(
            'adventure_today',
            array(
                'stageData' => json_encode($stageData)
            ),
            array(
                "`uid` = '{$user->uid}'",
                "`adventureName` = '{$_GET['adventure']}'" 
            ),
            1
        );

        header("Location: "._ROOT."adventure/dispatch/?adventure={$_GET['adventure']}");

        break;
    
    default: //默认显示队伍信息
        $charCount = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$user->uid}'"
            )
        );

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
                'stat',
                'ASC'
            );

            $renderer = new \meshal\xChar; //创建一个临时角色对象用于渲染
            $charList = '';
            if($query !== false) {
                foreach ($query as $k => $charData) {
                    $renderer->load($charData['id']);

                    #添加操作
                    if(
                        !is_null($renderer->stat)
                    ) {
                        $renderer->addCtrl(
                            '#',
                            'button.characterController.occupied',
                            'bgOpaGrey1 colorWhite1',
                            array('any'),
                            null,
                            true
                        );

                        $charList .= $renderer->renderLite(
                            true,
                            $user,
                            'filterOldPhoto',
                            'characterLite-50'
                        );
                    } else {
                        if($team->isCharInTeam($charData['id']) === true) {
                            $renderer->addCtrl(
                                "?adventure={$_GET['adventure']}&action=remove&char={$charData['id']}",
                                'button.adventureDispatch.remove',
                                'bgOpaRed1 colorWhite1',
                                array('any'),
                                null,
                                true
                            );
                            $charList .= $renderer->renderLite(
                                true,
                                $user,
                                'filterOldPhoto',
                                'characterLite-50'
                            );
                        } else {
                            switch (TRUE) {
                                //机动不足的角色显示
                                case $renderer->ap->current < $adventureData['apCost']:
                                    $renderer->addCtrl(
                                        '#',
                                        'button.adventureDispatch.shortOfAp',
                                        'bgOpaGrey1 colorWhite1',
                                        array('any'),
                                        null,
                                        true
                                    );
                                    $charList .= $renderer->renderLite(
                                        true,
                                        $user,
                                        'filterOldPhoto',
                                        'characterLite-50'
                                    );
                                    break;

                                //实力不足的角色显示
                                case !is_null($adventureData['strengthMin']) && $renderer->strength->st < $adventureData['strengthMin']:
                                    $renderer->addCtrl(
                                        '#',
                                        'button.adventureDispatch.strengthLower',
                                        'bgOpaGrey1 colorWhite1',
                                        array('any'),
                                        null,
                                        true
                                    );
                                    $charList .= $renderer->renderLite(
                                        true,
                                        $user,
                                        'filterOldPhoto',
                                        'characterLite-50'
                                    );
                                    break;

                                //实力超出的角色显示
                                case !is_null($adventureData['strengthMax']) && $renderer->strength->st > $adventureData['strengthMax']:
                                    $renderer->addCtrl(
                                        '#',
                                        'button.adventureDispatch.strengthGreater',
                                        'bgOpaGrey1 colorWhite1',
                                        array('any'),
                                        null,
                                        true
                                    );
                                    $charList .= $renderer->renderLite(
                                        true,
                                        $user,
                                        'filterOldPhoto',
                                        'characterLite-50'
                                    );
                                    break;

                                //队伍满员的角色显示
                                case $adventureData['teamMax'] <= $team->count:
                                    $renderer->addCtrl(
                                        '#',
                                        'button.adventureDispatch.teamFull',
                                        'bgOpaGrey1 colorWhite1',
                                        array('any'),
                                        null,
                                        false
                                    );
                                    $charList .= $renderer->renderLite(
                                        true,
                                        $user,
                                        'filterOldPhoto',
                                        'characterLite-50'
                                    );
                                    break;
                                
                                //默认的角色显示样式
                                default:
                                    $renderer->addCtrl(
                                        "?adventure={$_GET['adventure']}&action=add&char={$charData['id']}",
                                        'button.adventureDispatch.add',
                                        'bgOpaGreen1 colorWhite1',
                                        array('any')
                                    );
                                    $charList .= $renderer->renderLite(
                                        true,
                                        $user,
                                        '',
                                        'characterLite-50'
                                    );
                                    break;
                            }
                        }
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
                    '?adventure={?$adventureName?}&page={?$page?}'
                )
            );
        } else {
            $html->set('$pager', '');
            $charList = $html->readTpl('guide/charEmpty.html');
        }
        
        break;
}

//组装冒险类型
$adventureType = '';
if(!empty($adventureData['type'])) {
    $types = array();
    foreach($adventureData['type'] as $k => $t) {
        $types[] = "{?adventureType.{$t}?}";
    }
    $adventureType = implode('{?common.comma?}', $types);
}

//组装队伍要求
if(is_null($adventureData['teamMin']) && is_null($adventureData['teamMax'])) {
    $adventureTeam = '';
}
elseif(!is_null($adventureData['teamMin']) && !is_null($adventureData['teamMax'])) {
    $adventureTeam = "{?common.adventure.requirement.teamSize?} {$adventureData['teamMin']} {?common.tilde?} {$adventureData['teamMax']}";
}
elseif(!is_null($adventureData['teamMin'])) {
    $adventureTeam = "{?common.adventure.requirement.teamSize?} {?common.adventure.requirement.teamMin?} {$adventureData['teamMin']}";
}
else {
    $adventureTeam = "{?common.adventure.requirement.teamSize?} {?common.adventure.requirement.teamMax?} {$adventureData['teamMax']}";
}

//组装实力要求
if(is_null($adventureData['strengthMin']) && is_null($adventureData['strengthMax'])) {
    $adventureStrength = '';
}
elseif(!is_null($adventureData['strengthMin']) && !is_null($adventureData['strengthMax'])) {
    $adventureStrength = "{?common.adventure.requirement.strengthRange?} {$adventureData['strengthMin']} {?common.tilde?} {$adventureData['strengthMax']}";
}
elseif(!is_null($adventureData['strengthMin'])) {
    $adventureStrength = "{?common.adventure.requirement.strengthRange?} {?common.adventure.requirement.strengthMin?} {$adventureData['strengthMin']}";
}
else {
    $adventureStrength = "{?common.adventure.requirement.strengthRange?} {?common.adventure.requirement.strengthMax?} {$adventureData['strengthMax']}";
}

//组装机动花费
$adventureApCost = "{?common.adventure.requirement.apCost?} {$adventureData['apCost']}";

//组装物品loot清单
if(!empty($adventureData['loot'])) {
    // fPrint($adventureData['loot']);
    $lootList = '';
    foreach ($adventureData['loot'] as $k => $itemName) {
        $lootList .= \meshal\xItem::renderTag($itemName);
    }
    $hideNoneLoot = 'hidden';
} else {
    $lootList = '';
    $hideNoneLoot = '';
}

//组装时间要求
$msHighest = 1;
if(!empty($chars)) {
    foreach ($chars as $k => $charData) {
        //记录最高的移动速度
        if($charData->data['ms']['current'] > $msHighest) {
            $msHighest = $charData->data['ms']['current'];
        }

        //记录最低的移动速度
        if(!isset($msLowest) || $charData->data['ms']['current'] < $msLowest) {
            $msLowest = $charData->data['ms']['current'];
        }
    }
} else {
    $msHighest = 1;
    $msLowest = 1;
}

if($msHighest == $msLowest) {
    $duration = \fFormatTime(ceil($adventureData['duration'] / $msHighest / $GLOBALS['setting']['adventure']['adventureSpeedFactor']), 'hour');
} else {
    $duration = \fFormatTime(ceil($adventureData['duration'] / $msHighest / $GLOBALS['setting']['adventure']['adventureSpeedFactor']), 'hour')." {?common.tilde?} ".\fFormatTime(ceil($adventureData['duration'] / $msLowest / $GLOBALS['setting']['adventure']['adventureSpeedFactor']), 'hour');
}

//组装历史回溯统计
$epoch = \fEpochRange(\fEpoch() - 1);
$adventuresLastEpoch = $db->getCount(
    'adventure_instances',
    array(
        "`templateName` = '{$adventureData['name']}'",
        "`endTime` > '{$epoch['start']}'",
        "`endTime` <= '{$epoch['end']}'"
    )
);
$adventuresTotal = $db->getCount(
    'adventure_instances',
    array(
        "`templateName` = '{$adventureData['name']}'"
    )
);

//组装所有权
$ownerCount = xOwnership::count(
    'adventure',
    $adventureData['name']
);

$ownerList = '';
$owners = xOwnership::getTopOwners(
    'adventure',
    $adventureData['name']
);
if(!empty($owners)) {
    foreach ($owners as $k => $owner) {
        $ownerList .= xUser::renderTag($owner['uid']);
    }
}

//组装已选择队员
if(!empty($team->members)) {
    $selectedChars = '';
    foreach($team->members as $charId => $charData) {
        $selectedChars .= \meshal\xChar::renderTag($charId);
    }
    $html->set('--team', $selectedChars);
    $html->set('--hideTeamMember', '');
    $duration = \fFormatTime(ceil($adventureData['duration'] / $team->getMemberScoreLowest('ms')['value']), 'hour');
} else {
    $html->set('--team', '');
    $html->set('--hideTeamMember', 'hidden');
}

$html->set('--adventureName', "{?adventureName.{$adventureData['name']}?}");
$html->set('--coverImage', 
    (
        is_null($adventureData['coverImage']) || $adventureData['coverImage'] == ''
        || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$adventureData['coverImage'])
    )
        ? "{?!dirImg?}adventureCover.default.jpg"
        : _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$adventureData['coverImage']
);
$html->set('--description', "{?adventureDesc.{$adventureData['name']}?}");
$html->set('--duration', $duration);
$html->set('--adventureType', $adventureType);
$html->set('--requirement.strength', $adventureStrength);
$html->set('--requirement.apCost', $adventureApCost);
$html->set('--requirement.team', $adventureTeam);
$html->set('--lootList', $lootList);
$html->set('--hideNoneLoot', $hideNoneLoot);
$html->set('--adventures.total', $adventuresTotal);
$html->set('--adventures.lastEpoch', $adventuresLastEpoch);
$html->set('--ownerCount', $ownerCount);
$html->set('--ownership', $ownerList);
if($ownerCount == 0) {
    $html->set('--hideNoneOwner', '');
    $html->set('--hideOwner', 'hidden');
} else {
    $html->set('--hideNoneOwner', 'hidden');
    $html->set('--hideOwner', '');
}

if(
    $team->count < $adventureData['teamMin']
    || $team->count > $adventureData['teamMax']
) {
    $html->set('--url', '#');
    $html->set('--dispatchable', 'bgOpaGrey1');
} else {
    $html->set('--url', "{?!dirRoot?}adventure/dispatch/?adventure={$adventureData['name']}&action=dispatch");
    $html->set('--dispatchable', 'bgOpaGreen1');
}

$html->set('$adventureName', $_GET['adventure']);
$html->set('$charList', $charList);

//组装历史记录
$query = $db->getArr(
    array(
        'a' => 'adventure_instances',
        'c' => 'adventure_chars'
    ),
    array(
        'c.`adventureId` = a.id',
        "c.`uid` = '{$user->uid}'",
        "a.`templateName` = '{$_GET['adventure']}'",
        "a.`version` = '{$GLOBALS['meshal']['version']['adventure']}'"
    ),
    'a.`id`',
    $GLOBALS['setting']['adventure']['referenceAdventures'],
    null,
    'a.`endTime`',
    'DESC',
    null,
    true
);

$previousLogs = '';
if($query == false) {
    $html->set('--hideNoneLog', '');
} else {
    $html->set('--hideNoneLog', 'hidden');
    foreach($query as $previous) {
        $previousLogs .= \meshal\xAdventure::renderTag($previous['id'], '_BLANK');
    }
}
$html->set('--previousLogs', $previousLogs);

$html->loadTpl(
    'adventure/dispatch/body.frame.html'
);

$html->output();
\fDie();
?>