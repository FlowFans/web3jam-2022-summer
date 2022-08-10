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

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');

$chars = localChar();
$adventures = localGen();

# 没有可选冒险，显示冒险刷新时间
if($adventures === false) {
    $check = $db->getArr(
        'user_adventureUpdate',
        array(
            "`uid` = '{$user->uid}'"
        ),
        null,
        1
    );

    $html->set(
        '$countdown', 
        \fFormatTime(
            $check[0]['lastUpdate'] + $GLOBALS['setting']['adventure']['regenerateInterval']
        )
    );
    $html->loadTpl('adventure/new/body.frame.empty.html');

    $html->output();
    \fDie();
}

//渲染可选冒险
$comp = array();
foreach($adventures as $k => $adventure) {
    $data = \meshal\xAdventure::getData($adventure['adventureName']);

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
        // fPrint($data['loot']);
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
        $duration = \fFormatTime(ceil($data['duration'] / $msHighest / $GLOBALS['setting']['adventure']['adventureSpeedFactor']), 'hour');
    } else {
        $duration = \fFormatTime(ceil($data['duration'] / $msHighest / $GLOBALS['setting']['adventure']['adventureSpeedFactor']), 'hour')." {?common.tilde?} ".\fFormatTime(ceil($data['duration'] / $msLowest / $GLOBALS['setting']['adventure']['adventureSpeedFactor']), 'hour');
    }

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
        '--url' => "{?!dirRoot?}adventure/dispatch/?adventure={$data['name']}"
    );
}

$html->set(
    '$adventureList', 
    $html->duplicate(
        'adventure/new/body.dup.adventure.html',
        $comp
    )
);

$html->loadTpl('adventure/new/body.frame.html');

$html->output();
\fDie();

function localChar() {
    global $db;
    global $user;

    //查找用户的所有角色，从而可以根据这些角色的数据进行冒险的随机
    $characters = $db->getArr(
        'characters',
        array(
            "`ownerId` = '{$user->uid}'"
        ),
        '`id`'
    );

    $allChars = array();
    if($characters === false) {
        \fLog("No characters under user({$user->uid})'s name");
        return false;
    } else {
        foreach ($characters as $k => $char) { //分别加载每个角色的简要数据
            $allChars[$char['id']] = new \meshal\char\xAdapter;
            $allChars[$char['id']]->load($char['id']);
        }
    }
    return $allChars;
}

/**
 * 随机生成或获取当前的冒险
 */
function localGen() {
    global $user;
    global $db;

    global $chars;
    global $msHighest;
    global $msLowest;

    $today = $db->getArr(
        'adventure_today',
        array(
            "`uid` = '{$user->uid}'"
        ),
        null
    );

    $lastUpdate = $db->getArr(
        'user_adventureUpdate',
        array(
            "`uid` = '{$user->uid}'"
        ),
        null,
        1
    );

    if($lastUpdate === false) { //没有记录则新建记录
        $db->insert(
            'user_adventureUpdate',
            array(
                'uid' => $user->uid
            )
        );
    }


    # 没记录或者超出时间间隔，就生成新的随机记录
    if(
        $lastUpdate === false //没有记录
        || $lastUpdate[0]['lastUpdate'] + $GLOBALS['setting']['adventure']['regenerateInterval'] < time() //超出时间间隔
    ) { 
        $random = mt_rand(
            $GLOBALS['setting']['adventure']['randomAdventureMin'] + $user->efx->modifier['adventuresRenewal.randomMin'], 
            $GLOBALS['setting']['adventure']['randomAdventureMax'] + $user->efx->modifier['adventuresRenewal.randomMax']
        ) + $user->efx->modifier['adventuresRenewal.base'];
        # 用户没有角色，随机选择若干个冒险
        if($chars === false) {
            //从数据库随机选择
            $tpls = $db->getArr(
                'adventures',
                array(),
                null,
                $random,
                null,
                null,
                'RAND'
            );
        }
        
        #查询到用户有角色，根据角色的数据选择冒险
        else {
            $stHighest = 0;
            foreach ($chars as $charId => $charData) {
                //记录最高的实力值
                if($charData->data['strength']['st'] > $stHighest) {
                    $stHighest = $charData->data['strength']['st'];
                }
                //记录最低的实力值
                if(!isset($stLowest) || $stLowest > $charData->data['strength']['st']) {
                    $stLowest = $charData->data['strength']['st'];
                }
            }
            $stHighest *= $GLOBALS['setting']['adventure']['strengthToleranceMax'];

            //从数据库中随机取实力范围内的冒险
            $tpls = $db->getArr(
                'adventures',
                array(
                    "`strengthMax` > '{$stHighest}'",
                    "`strengthMin` < '{$stLowest}'"
                ),
                null,
                $random,
                null,
                null,
                'RAND'
            );
            if($tpls === false) $tpls = array();

            //数量不足，随机取冒险并补足
            $addon = array();
            if(count($tpls) < $random) {
                $addon = $db->getArr(
                    'adventures',
                    array(),
                    null,
                    $random - count($tpls),
                    null,
                    null,
                    'RAND'
                );
            }
        }

        if($addon !== false && !is_null($addon)) {
            $tpls = $tpls + $addon;
        }

        if($tpls === false) {
            \fLog("Didn't fetch any adventure templates");
            return false;
        }

        //把过期的冒险从数据库中删除
        $db->delete(
            'adventure_today',
            array(
                "`uid` = '{$user->uid}'"
            )
        );

        //把随机到的新冒险id写入数据库
        foreach($tpls as $k => $tpl) {
            $db->insert(
                'adventure_today',
                array(
                    'uid' => $user->uid,
                    'adventureName' => $tpl['name'],
                    'lastUpdate' => time()
                )
            );
        }

        //更新时间记录
        $db->update(
            'user_adventureUpdate',
            array(
                'lastUpdate' => time()
            ),
            array(
                "`uid` = '{$user->uid}'"
            ),
            1
        );
    }

    #读取记录
    return $db->getArr(
        'adventure_today',
        array(
            "`uid` = '{$user->uid}'"
        )
    );
}
?>