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

# 加载依赖
require_once '../function.upgrade.php';

# 加载专属配置
require_once _ROOT.DIR_CFG.'meshal.upgrade.php';

################################################
# 初始化结束
################################################

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;
$dice = new \meshal\xDice;
$char = new \meshal\xChar;

$html->loadCss('css/meshal.css');
localCheck();

$html->loadTpl('character/upgrade/body.frame.html');
$html->loadTpl('character/upgrade/body.0.1.0.html');

if(
    \fGet('action') === 'pick' 
) {
    if(
        \fGet('id', '') != '' 
        && \fGet('select', '') != ''
    ) {

        //校验confirm code
        if(
            \fGet('confirm', '') != md5('upgrade'.$GLOBALS['deploy']['securityKey'].$_GET['id'].'select'.$_GET['select'])
        ) {
            $html->redirectBack(
                'redirect.message.character.error'
            );
            \fDie();
        }

        //从数据库取临时数据
        $query = $db->getArr(
            'character_upgrade',
            array(
                "`charId` = '{$_GET['id']}'",
                "`ownerId` = '{$user->uid}'",
                "`version` = '0.2.0'"
            ),
            null,
            1
        );
        if($query === false) { //没有取到数据，报错
            $html->redirectBack(
                'redirect.message.character.error'
            );
            \fDie();
        }

        $event = array(); //这个数组用于记录角色升级事件
        $evenv['version']['old'] = $char->version;
        $event['data']['old'] = $char->export();

        $data = json_decode($query[0]['data'], true);
        $char->import(json_decode(\fDecode($data[$_GET['select']]), true));
        $char->version = '0.2.0';
        $char->save();
        $event['version']['new'] = $char->version;
        $event['data']['new'] = $char->export();
        
        
        //记录角色升级事件
        $char->event(
            $user->uid,
            'upgrade',
            $event
        );

        $db->delete(
            'character_upgrade',
            array(
                "`charId` = '{$_GET['id']}'",
                "`ownerId` = '{$user->uid}'",
                "`version` = '0.2.0'"
            ),
            1
        );
        
        $html->set('$charName', $char->name);
        $html->redirectBack(
            'redirect.message.character.upgraded.0.2.0'
        );

        \fDie();
    } else {
        $html->redirectBack(
            'redirect.message.character.error'
        );
        \fDie();
    }
}

if(\fGet('action') === 'random') {
    $error = 0;
    //检查cp数额
    if(bccomp($user->cp, $GLOBALS['meshal']['upgrade']['0.2.0']['RandomizeCpCost']) == -1) {
        \fNotify(
            'notify.upgrade.0.2.0.insufficientCp',
            'fatal'
        );
        $error ++;
    } else {
        $user->cp = \fSub(
            $user->cp,
            $GLOBALS['meshal']['upgrade']['0.2.0']['RandomizeCpCost'],
            $GLOBALS['cp']['decimal']
        );
        $user->save();
        $user->fetch();

        $generated = array();
        for ($i=0; $i < $GLOBALS['meshal']['upgrade']['0.2.0']['featureSets']; $i++) {
            $generated[$i] = \fEncode(json_encode(localGen()));
        }

        $query = $db->getArr(
            'character_upgrade',
            array(
                "`charId` = '{$char->id}'",
                "`ownerId` = '{$user->uid}'",
                "`version` = '0.2.0'"
            ),
            null,
            1
        );
        if($query === false) {
            $db->insert(
                'character_upgrade',
                array(
                    'charId' => $char->id,
                    'ownerId' => $user->uid,
                    'version' => '0.2.0',
                    'lastUpdate' => time(),
                    'data' => json_encode($generated)
                )
            );
        } else {
            $db->update(
                'character_upgrade',
                array(
                    'data' => json_encode($generated),
                    'lastUpdate' => time()
                ),
                array(
                    "`charId` = '{$char->id}'",
                    "`ownerId` = '{$user->uid}'",
                    "`version` = '0.2.0'"
                ),
                1
            );
        }

        \fSaveLogToFile();
        header("Location: "._ROOT."character/upgrade/0.1.0/?id={$char->id}&_back=".\fGet('_back')."#candidates");
        \fDie();
    }
}

//检查character_upgrade中是否有该版本的临时升级数据
$query = $db->getArr(
    'character_upgrade',
    array(
        "`charId` = '{$char->id}'",
        "`ownerId` = '{$user->uid}'",
        "`version` = '0.2.0'"
    ),
    null,
    1
);

if($query === false) { //没有记录时，为这个角色预生成
    $generated = array();
    for ($i=0; $i < $GLOBALS['meshal']['upgrade']['0.2.0']['featureSets']; $i++) {
        $generated[$i] = \fEncode(json_encode(localGen()));
    }

    $db->insert(
        'character_upgrade',
        array(
            'charId' => $char->id,
            'ownerId' => $user->uid,
            'version' => '0.2.0',
            'lastUpdate' => time(),
            'data' => json_encode($generated)
        )
    );

    \fSaveLogToFile();
    header("Location: "._ROOT."character/upgrade/0.1.0/?id={$char->id}&_back=".\fGet('_back'));
    \fDie();
}

//开始渲染预存的升级选项
$rendered = '';
$data = json_decode($query[0]['data'], true);
foreach ($data as $i => $ch) {
    $charRenderer = new \meshal\xChar;
    $charRenderer->import(json_decode(\fDecode($ch), true));
    $confirmationCode = md5('upgrade'.$GLOBALS['deploy']['securityKey'].$char->id.'select'.$i);
    $charRenderer->addCtrl(
        "?id={$char->id}&select={$i}&confirm={$confirmationCode}&action=pick&_back=".\fGet('_back'),
        'button.characterController.upgrade.0.2.0',
        null,
        'owner',
        null,
        true
    );
    $charRenderer->viewerUrl = '#';
    $rendered .= $charRenderer->render(null, true, $user);
}
$html->set('$preview', $rendered);

$disable = 0;
//检查cp数额
if(bccomp($user->cp, $GLOBALS['meshal']['upgrade']['0.2.0']['RandomizeCpCost']) == -1) {
    \fNotify(
        'notify.upgrade.0.2.0.insufficientCp',
        'fatal'
    );
    $disable ++;
}

//汇总检查结果
if($disable > 0) {
    $html->set('$reRollUrl', '#');
    $html->set('$reRoll.disable', 'nzButtonDisabled');
} else {
    $html->set('$reRollUrl', '?id={?$charId?}&action=random&_back='.\fGet('_back'));
    $html->set('$reRoll.disable', '');
}

$html->set('$charId', $char->id);
$html->set('$charName', $char->name);
$html->set('$featureSets', $GLOBALS['meshal']['upgrade']['0.2.0']['featureSets']);
$html->set('$randomCost', $GLOBALS['meshal']['upgrade']['0.2.0']['RandomizeCpCost']);
$char->viewerUrl = '#';
$html->set('$characterPreview', $char->render(null, false, $user, 'filterOldPhoto'));
$html->set('$randomize.cpCost', $GLOBALS['meshal']['upgrade']['0.2.0']['RandomizeCpCost']);
$html->output();
\fDie();

/**
 * 做特征预生成并返回临时角色数据
 */
function localGen() {
    global $db;

    $char = new \meshal\xChar;
    $char->load($_GET['id']);
    localRandFeature($char, 'species');
    localRandFeature($char, 'ethnicity', true);
    localRandFeature($char, 'faction', true);
    localRandFeature($char, 'gender', true);
    localRandFeature($char, 'size', true);
    localRandFeature($char, 'form', true);

    $char->features->update();

    return $char->export();
}

/**
 * 随机选取特征
 */
function localRandFeature(
    \meshal\xChar &$char,
    string $featureType,
    bool $checkAvailability = false
) {
    global $db;

    $dice = new \meshal\xDice;

    $pre = array(
        'totalStrength' => 0,
        'totalProbabilityModifier' => 0,
        'mostNegativeStrength' => 0 
    );
    if($checkAvailability === true) { //从白名单取
        $availability = $char->features->availability($featureType);

        foreach($availability as $featureName => $v) {
            $featureData = \meshal\xFeature::getData($featureType, $featureName);
            $pre['candidates'][$featureData['name']] = array(
                'strength' => abs($featureData['strength']), //与实力相关的随机概率为以0为原点的正态分布，数字越接近0，概率越大
                'probabilityModifier' => $featureData['probabilityModifier']
            );
            $pre['totalStrength'] += abs($featureData['strength']); //把特征实力累加
            $pre['totalProbabilityModifier'] += $featureData['probabilityModifier']; //把权重修改量累加
        }
    }
    else { //从全量中取
        $query = $db->getArr(
            'features',
            array(
                "`type` = '{$featureType}'"
            ),
            array(
                '`name`',
                '`strength`',
                '`probabilityModifier`'
            )
        );
        foreach($query as $k => $featureData) {
            $pre['candidates'][$featureData['name']] = array(
                'strength' => abs($featureData['strength']), //与实力相关的随机概率为以0为原点的正态分布，数字越接近0，概率越大
                'probabilityModifier' => $featureData['probabilityModifier']
            );
            $pre['totalStrength'] += abs($featureData['strength']); //把特征实力累加
            $pre['totalProbabilityModifier'] += $featureData['probabilityModifier']; //把权重修改量累加
        }
    }

    if(empty($pre['candidates'])) return; //如果候选特征为空，直接返回不作处理。

    $pool = array();
    foreach ($pre['candidates'] as $featureName => $featureData) {
        $pool[$featureName] = 
            $pre['totalStrength'] - $featureData['strength'] //与实力相关的随机概率为以0为原点的正态分布，数字越接近0，概率越大
            + $featureData['probabilityModifier'] //应用权重修改量
            + 1 //最后加1为了确保所有修正器为0的特征也有1的权重
        ;
    }

    $pick = (
        $GLOBALS['meshal']['generate'][$featureType]['min'] 
            ? $GLOBALS['meshal']['generate'][$featureType]['min'] 
            : 1
    ) + (
        $GLOBALS['meshal']['generate'][$featureType]['dice'] 
            ? $dice->pt($GLOBALS['meshal']['generate'][$featureType]['dice']) 
            : 0
    );
    $random = \fArrayRandWt($pool, $pick);
    foreach ($random as $k => $featureName) {
        $char->features->add(
            $featureType,
            $featureName,
            !$checkAvailability
        );
    }
}
?>