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
$html->loadTpl('facility/upgrade/body.confirm.html', 'body');
$nextLevel = $_GET['level'] + 1;
$html->set('$facilityName', $_GET['facility']);
$html->set('$facilityLevel', $nextLevel);

//检查参数合法性
if(is_null(\fGet('facility'))) {
    \fLog('Error: no facililty given');
    $html->redirectBack(
        'redirect.message.facility.upgradeError'
    );
    \fDie();
}

if(is_null(\fGet('level'))) {
    \fLog('Error: no level given');
    $html->redirectBack(
        'redirect.message.facility.upgradeError'
    );
    \fDie();
}

//检查用户当前设施等级是否和请求的参数一致
if($user->facility->getLevel($_GET['facility']) != $_GET['level']) {
    \fLog("Error: request level doesn't match user's facility level");
    $html->redirectBack(
        'redirect.message.facility.upgradeError'
    );
    \fDie();
}

//检查设施是否正在建造中
$check = $db->getCount(
    'facility_building',
    array(
        "`uid` = '{$user->uid}'",
        "`facilityName` = '{$_GET['facility']}'"
    ),
    null,
    1
);

if($check > 0) {
    \fLog("Error: facility {$_GET['facility']} is under construction");
    $html->redirectBack(
        'redirect.message.facility.upgradeError'
    );
    \fDie();
}

//检查用户当前设施是否已经大于等于升级参数
if($user->facility->getLevel($_GET['facility']) >= $nextLevel) {
    \fLog("Error: level of user({$user->uid})'s facility {$_GET['facility']} is ≥ {$nextLevel}");
    $html->redirectBack(
        'redirect.message.facility.upgradeLevelReached'
    );
    \fDie();
}

//加载设施
$facility = new \meshal\xFacility; //这是用户现有的设施信息
if($facility->load($_GET['facility'], $_GET['level']) === false) { //检查设施是否在数据库中存在
    \fLog("Error: facility {$_GET['facility']}({$_GET['level']}) doesn't exist in the library");
    $html->redirectBack(
        'redirect.message.facility.upgradeError'
    );
    \fDie();
}

$nextFacility = new \meshal\xFacility; //这是下一级的设施信息
if($nextFacility->load($_GET['facility'], $nextLevel) === false) {
    \fLog("Error: facility {$_GET['facility']}(next level {$nextLevel}) doesn't exist in the library");
    $html->redirectBack(
        'redirect.message.facility.upgradeError'
    );
    \fDie();
}

//取缓存数据
$query = $db->getArr(
    'facility_prepare',
    array(
        "`uid` = '{$user->uid}'",
        "`facilityName` = '{$_GET['facility']}'",
        "`facilityLevel` = '{$nextLevel}'"
    ),
    null,
    1
);

if($query === false) { //如果没有查到prepare数据，插入一个
    $db->insert(
        'facility_prepare',
        array(
            'uid' => $user->uid,
            'facilityName' => $_GET['facility'],
            'facilityLevel' => $nextLevel
        )
    );
}

$stageData = json_decode($query[0]['stageData'], true); //加载建造的stage data
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
            if(is_null($charTmp[0]['stat'])) { //检查角色的状态，防止在启动建造之前角色被其他事占用了
                $team->add($charId);
                if($team->members[$charId]->ap->current < $facility->dataNextLevel['build']['ap']) {
                    $team->remove($charId);
                }
            }
        }
    }
}

$buildCheck = $facility->checkUpgrade($user->uid);

switch (\fGet('action')) {
    case 'construct': //进行建造
        //结束建造的时间戳
        $endTime = time() + $facility->dataNextLevel['build']['time'] / $GLOBALS['setting']['facility']['buildSpeedFactor'];

        //检查建造人数
        if(count($stageData['team']) < $facility->dataNextLevel['build']['char']) {
            \fLog("Error while start building, not enough builders");
            $html->redirect(
                _ROOT.'facility/',
                'pageTitle.facility',
                'redirect.message.facility.upgradeError'
            );
            \fDie();
        }

        //检查现在是否足够条件
        if($buildCheck > 0) {
            \fLog("Error while checking upgrade prerequisites: error code = {$error}");
            $html->redirect(
                _ROOT.'facility/',
                'pageTitle.facility',
                'redirect.message.facility.upgradeError'
            );
            \fDie();
            break;
        }

        //遍历检查每个角色的状态
        $error = 0;
        if(!empty($team->members)) {
            foreach($team->members as $builder) {
                if(!is_null($builder->stat)) {
                    \fLog("Error: character({$charId}) is occupied");
                    $error ++;
                }
            }
        }

        if($error > 0) {
            \fLog("Stop initiating building process due to errors");
            $html->redirect(
                _ROOT.'facility/',
                'pageTitle.facility',
                'redirect.message.facility.upgradeError'
            );
            \fDie();
            break;
        }

        # 扣除材料
        if($facility->dataNextLevel['build']['material']) {
            foreach ($facility->dataNextLevel['build']['material'] as $k => $material) {
                $user->inventory->remove($material[0], $material[1]);
            }
        }

        # 没有检查到错误，写入数据库并更新角色状态
        foreach($team->members as $builder) {
            $builder->stat = 'building';
            $builder->ap->current -= $facility->dataNextLevel['build']['ap'];
            $builder->save();
        }

        $db->insert( //写入到building表
            'facility_building',
            array(
                'uid' => $user->uid,
                'facilityName' => $_GET['facility'],
                'facilityLevel' => $nextLevel,
                'builders' => json_encode($team->export()),
                'endTime' => $endTime
            )
        );

        $db->delete( //从prepare表删除临时数据
            'facility_prepare',
            array(
                "`uid` = '{$user->uid}'",
                "`facilityName` = '{$_GET['facility']}'",
                "`facilityLevel` = '{$nextLevel}'"
            ),
            1
        );

        $html->set('$facilityName', $_GET['facility']);
        $html->set('$facilityLevel', $nextLevel);
        $html->redirect(
            _ROOT.'facility/',
            'pageTitle.facility',
            'redirect.message.facility.upgradeSuccess'
        );
        \fDie();
        break;

    case 'add': //向队伍添加角色
        if($team->count > $facility->dataNextLevel['build']['char']) { //队伍超限
            $html->redirect(
                _ROOT."facility/upgrade/?facility={$_GET['facility']}&level={$_GET['level']}&back={$_GET['_back']}",
                'pageTitle.facility',
                'redirect.message.facility.upgradeError'
            );
            \fDie();
            break;
        }
        
        $c = new \meshal\xChar($_GET['char']);
        if($c->ap->current < $facility->dataNextLevel['build']['ap']) { //AP不足
            $html->redirect(
                _ROOT."facility/upgrade/?facility={$_GET['facility']}&level={$_GET['level']}&back={$_GET['_back']}",
                'pageTitle.facility',
                'redirect.message.facility.buildInsufficientAP'
            );
        }

        $team->add($_GET['char']);
        $stageData['team'] = $team->export();

        $db->update(
            'facility_prepare',
            array(
                'stageData' => json_encode($stageData)
            ),
            array(
                "`uid` = '{$user->uid}'",
                "`facilityName` = '{$_GET['facility']}'",
                "`facilityLevel` = '{$nextLevel}'"  
            ),
            1
        );

        header("Location: "._ROOT."facility/upgrade/?facility={$_GET['facility']}&level={$_GET['level']}&_back={$_GET['_back']}");
        break;

    case 'remove': //从队伍移除角色
        $team->remove($_GET['char']);
        $stageData['team'] = $team->export();

        $db->update(
            'facility_prepare',
            array(
                'stageData' => json_encode($stageData)
            ),
            array(
                "`uid` = '{$user->uid}'",
                "`facilityName` = '{$_GET['facility']}'",
                "`facilityLevel` = '{$nextLevel}'"
            ),
            1
        );

        header("Location: "._ROOT."facility/upgrade/?facility={$_GET['facility']}&level={$_GET['level']}&back={$_GET['_back']}");

        break;
    
    default: //默认显示队伍信息
        $charCount = $db->getCount(
            'characters',
            array(
                "`ownerId` = '{$user->uid}'"
            )
        );

        $builderList = '';
        if($team->count > 0) {
            foreach($team->members as $char) {
                $builderList .= $char->renderTag($char->id);
            }
        }
        $html->set('$builderList', $builderList);

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
                                "?facility={$_GET['facility']}&level={$_GET['level']}&action=remove&char={$charData['id']}",
                                'button.facilityUpgrade.remove',
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
                                case $renderer->ap->current < $facility->dataNextLevel['build']['ap']:
                                    $renderer->addCtrl(
                                        '#',
                                        'button.facilityUpgrade.shortOfAp',
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
                                case $facility->dataNextLevel['build']['char'] <= $team->count:
                                    $renderer->addCtrl(
                                        '#',
                                        'button.facilityUpgrade.teamFull',
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
                                        "?facility={$_GET['facility']}&level={$_GET['level']}&action=add&char={$charData['id']}",
                                        'button.facilityUpgrade.add',
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
                    '?facility={?$facilityName?}&level={?$facilityLevel?}&page={?$page?}'
                )
            );
        } else {
            $html->set('$pager', '');
            $charList = $html->readTpl('guide/charEmpty.html');
        }

        //组装前提检查
        $html->set('$facilityUpgradeTime', \fFormatTime($facility->dataNextLevel['build']['time'] / $GLOBALS['setting']['facility']['buildSpeedFactor'], 'hour'));
        $html->set('$facilityUpgradeChar', $facility->dataNextLevel['build']['char']);
        $html->set('$facilityUpgradeAP', $facility->dataNextLevel['build']['ap']);

        //渲染checkAll类前提
        if(!empty($facility->dataNextLevel['build']['checkAll'])) {
            $html->set('$showNextLevelCheckAll', '');
            $comp = array();
            foreach($facility->dataNextLevel['build']['checkAll'] as $k => $cond) {
                $comp[] = array(
                    '--type' => '',
                    '--listItem' => \fReplace($html->dbLang("userCheck.{$cond[0]}"), $cond)
                );
            }

            $list = $html->duplicate(
                'facility/upgrade/dup.row.li.html',
                $comp
            );

            $html->set(
                '$facilityUpgradeCheckAll',
                $html->quickRender(
                    'facility/upgrade/dup.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );

        } else {
            $html->set('$showNextLevelCheckAll', 'hidden');
        }

        //渲染checkAny类前提
        if(!empty($facility->dataNextLevel['build']['checkAny'])) {
            $html->set('$showNextLevelCheckAny', '');
            $comp = array();
            foreach($facility->dataNextLevel['build']['checkAny'] as $k => $cond) {
                $comp[] = array(
                    '--type' => '',
                    '--listItem' => \fReplace($html->dbLang("userCheck.{$cond[0]}"), $cond)
                );
            }

            $list = $html->duplicate(
                'facility/upgrade/dup.row.li.html',
                $comp
            );

            $html->set(
                '$facilityUpgradeCheckAny',
                $html->quickRender(
                    'facility/upgrade/dup.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );

        } else {
            $html->set('$showNextLevelCheckAny', 'hidden');
        }

        //渲染material消耗前提
        if(!empty($facility->dataNextLevel['build']['material'])) {
            $html->set('$showNextLevelMaterial', '');
            $comp = array();
            foreach($facility->dataNextLevel['build']['material'] as $k => $cond) {
                if(is_null($user->uid) || !isset($user->uid)) {
                    $renderType = '';
                } else {
                    $stock = $user->inventory->getStock($cond[0]);
                    // $renderType = $user->inventory->checkStock($cond[0], $cond[1]) == false ? 'modNegative' : '';
                    $renderType = $stock < $cond[1] ? 'modNegative' : '';
                }
                $comp[] = array(
                    '--type' => $renderType,
                    // '--listItem' => \fReplace($html->dbLang("common.facility.buildMaterial"), $cond)
                    '--itemTag' => \meshal\xItem::renderTag($cond[0]),
                    '--stock' => $stock,
                    '--required' => $cond[1]
                );
            }

            $list = $html->duplicate(
                'facility/upgrade/dup.row.itemList.html',
                $comp
            );

            $html->set(
                '$facilityUpgradeMaterial',
                $html->quickRender(
                    'facility/upgrade/dup.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );
        } else {
            $html->set('$showNextLevelMaterial', 'hidden');
        }
        
        break;
}

$html->loadTpl(
    'facility/upgrade/body.frame.html'
);


//检查建造人数
if(
    $team->count < $facility->dataNextLevel['build']['char']
    || $buildCheck > 0
) {
    $html->set('$url', '#');
    $html->set('$constructable', 'bgOpaGrey1');
    $nextFacility->addCtrl(
        "#",
        'button.facility.upgrade',
        'bgOpaGrey1 colorWhite1',
        array('any'),null,true
    );
} else {
    $html->set('$url', "?facility={$_GET['facility']}&level={$_GET['level']}&action=construct");
    $html->set('$constructable', 'bgOpaGreen1');
    $nextFacility->addCtrl(
        "?facility={$_GET['facility']}&level={$_GET['level']}&action=construct",
        'button.facility.upgrade',
        'bgOpaGreen1 colorWhite1',
        array('any'),null,true
    );
}

$html->set('$builderAmount', $facility->dataNextLevel['build']['char']);
$html->set('$apAmount', $facility->dataNextLevel['build']['ap']);
$html->set('$facilityCard', $nextFacility->render(false));
$html->set('$charList', $charList);

$html->output();

// fPrint($facility->dataNextLevel);
\fDie();

?>