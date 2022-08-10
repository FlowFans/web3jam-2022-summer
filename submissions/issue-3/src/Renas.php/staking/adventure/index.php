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

// $db = new xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('staking/adventure/body.frame.html', 'body');

//从数据库中取记录
$adventureCount = $db->getCount(
    'adventures'
);

if($adventureCount > 0) {

    # 根据当前页取冒险
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['adventure']['adventuresPerPage'];

    $queryAdventures = $db->getArr(
        'adventures',
        array(),
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['adventure']['adventuresPerPage']}",
        MYSQLI_NUM,
        'totalShares',
        'DESC',
        null
    );

    foreach($queryAdventures as $k => $adventure) {
        $data = \meshal\xAdventure::getData($adventure['name']);
    
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
    
        $comp[] = array(
            '--adventureName' => "{?adventureName.{$data['name']}?}",
            '--coverImage' => 
                (
                    is_null($data['coverImage']) || $data['coverImage'] == ''
                    || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$data['coverImage'])
                )
                    ? "{?!dirImg?}adventureCover.default.jpg"
                    : _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$data['coverImage'],
            '--description' => "{?adventureDesc.{$data['name']}?}",
            '--duration' => $duration,
            '--adventureType' => $adventureType,
            '--requirement.strength' => $adventureStrength,
            '--requirement.apCost' => $adventureApCost,
            '--requirement.team' => $adventureTeam,
            '--adventures.total' => $adventuresTotal,
            '--adventures.lastEpoch' => $adventuresLastEpoch,
            '--lootList' => $lootList,
            '--hideNoneLoot' => $hideNoneLoot,
            '--ownership' => $ownerList,
            '--ownerCount' => $ownerCount,
            '--hideNoneOwner' => $ownerCount == 0 ? '' : 'hidden',
            '--hideOwner' => $ownerCount == 0 ? 'hidden' : '',
            '--staking.totalStaked' => $data['totalShares'],
            '--staking.totalStaking' => $lastEpochStaking,
            '--staking.myStaked' => xOwnership::getShares($user->uid, 'adventure', $data['name']),
            '--staking.myStaking' => xOwnership::getStaking($user->uid, 'adventure', $data['name']),
            '--url' => "{?!dirRoot?}staking/adventure/stake/?name={$data['name']}"
        );
    }

    $html->set(
        '$adventureList', 
        $html->duplicate(
            'staking/adventure/body.dup.adventure.html',
            $comp
        )
    );

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $adventureCount,
            $GLOBALS['setting']['pager']['adventure']['adventuresPerPage'],
            '?page={?$page?}'
        )
    );
} else {
    $html->set('$adventureList', '');
    $html->set('$pager', '');
}



$html->output();

\fDie();
?>