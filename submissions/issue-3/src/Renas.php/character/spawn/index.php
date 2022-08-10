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
$dice = new \meshal\xDice;
$char = new \meshal\xChar($user->uid);
$name = new \meshal\xName;

$html->loadCss('css/meshal.css');
$html->set('$chargen.randomize.cpCost', $GLOBALS['cp']['character']['generate']);

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

localReload();

if(\fGet('action') === 'new') {
    //为了防止被攻击，每个账号有生成间隔限制
    $timer = $db->getArr(
        'character_stage',
        array(
            "`uid` = '{$user->uid}'"
        ),
        null,
        1
    )[0]['timestamp'] + $GLOBALS['setting']['character']['generateInterval'];
    //时间间隔不够，notify
    if($timer > time()) {
        $html->set('$interval', $timer - time());
        \fNotify('notify.chargen.tooOften', 'warn');
        localFetch();
        \fDie();
    }

    //如果没有足够的CP
    if(bccomp($user->cp, $GLOBALS['cp']['character']['generate']) == -1) {
        $html->set('$genCost', $GLOBALS['cp']['character']['generate']);
        \fNotify('notify.chargen.insufficientCP', 'fatal');
        localFetch();
        \fDie();
    }

    $user->cp = \fSub(
        $user->cp, 
        $GLOBALS['cp']['character']['generate'],
        $GLOBALS['cp']['decimal']
    );
    $user->save();
    $user->fetch();

    //把原来的肖像图删除
    $fetch = $db->getArr(
        'character_stage',
        array(
            "`uid` = {$user->uid}"
        ),
        null,
        1
    );
    if(
        file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($fetch[0]['portrait']))
        && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($fetch[0]['portrait']))
    ) {
        unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($fetch[0]['portrait']));
    }

    localGen();
    localReload();
    \fSaveLogToFile();
    header('Location: '._ROOT.'character/spawn/');
    \fDie();
} else { //没有动作的访问
    localFetch();
    localReload();
}

/**
 * 从stage读取已经存在的角色数据
 */
function localFetch() {
    global $char;
    global $db;
    global $user;
    global $html;

    $fetch = $db->getArr(
        'character_stage',
        array(
            "`uid` = {$user->uid}"
        ),
        null,
        1
    );

    //如果有记录，读取记录
    if($fetch !== false) {
        $char->import($fetch[0]);
        $char->viewerUrl = '#';
        $html->loadTpl('character/spawn/body.frame.html');

        $html->set('$characterPreview', $char->render(null, false));
        $html->output();
        \fDie();
    }
    //如果没有记录，则问用户是否要生成
    else {
        $html->loadTpl('character/spawn/body.frame.empty.html');
        $html->output();
        \fDie();
    }
}

/**
 * 做一些数据校准的操作，并影响页面呈现
 */
function localReload() {
    global $db;
    global $user;
    global $html;

    //计算slot
    $currentChars = $db->getCount('characters', array("`ownerId` = '{$user->uid}'"));
    $charSlots = $db->getArr('character_slot', array("`uid` = '{$user->uid}'"))[0]['slot'] + $GLOBALS['meshal']['character']['initialSlot'] + $user->efx->modifier['survivorSlots'];
    $availableSlots = $charSlots - $currentChars;

    $html->set('$character.slot.available', $availableSlots);
    $html->set('$chargen.currentChars', $currentChars);
    $html->set('$chargen.slots', $charSlots);

    if($availableSlots <= 0) {
        $html->set('$slots', '{?button.characterSlot.insufficient?}');
        $html->set('$accept.disable', 'nzButtonDisabled');
        $html->set('$accept.url', '#');
    } else {
        $html->set('$slots', '{?button.characterSlot.sufficient?}');
        $html->set('$accept.disable', '');
        $html->set('$accept.url', '{?!dirRoot?}character/pick/');
    }

    //计算cp
    if(bccomp($user->cp, $GLOBALS['cp']['character']['generate']) == -1 ) {
        $html->set('$new.disable', 'nzButtonDisabled');
        $html->set('$new.url', '#');
    } else {
        $html->set('$new.disable', '');
        $html->set('$new.url', '{?!dirRoot?}character/spawn/?action=new');
    }
}

/**
 * 生成一个随机角色
 */
function localGen() {
    ################################################
    # 开始生成随机角色
    ################################################
    global $char;
    global $dice;
    global $name;
    global $db;
    global $user;

    $char->version = $GLOBALS['meshal']['version']['character'];

    if($dice->pt(3) > 1) {
        $char->name = $name->gen('en', 'given', 'middle', 'sur');
    } else {
        $char->name = $name->gen('en', 'given', 'sur');
    }

    //随机决定属性
    $char->m->set('base', $GLOBALS['meshal']['generate']['m']['min'] + $dice->pt($GLOBALS['meshal']['generate']['m']['dice']));
    $char->a->set('base', $GLOBALS['meshal']['generate']['a']['min'] + $dice->pt($GLOBALS['meshal']['generate']['a']['dice']));
    $char->s->set('base', $GLOBALS['meshal']['generate']['s']['min'] + $dice->pt($GLOBALS['meshal']['generate']['s']['dice']));

    $char->t->set('base', $GLOBALS['meshal']['generate']['t']['min'] + $dice->pt($GLOBALS['meshal']['generate']['t']['dice']));
    $char->e->set('base', $GLOBALS['meshal']['generate']['e']['min'] + $dice->pt($GLOBALS['meshal']['generate']['e']['dice']));
    $char->r->set('base', $GLOBALS['meshal']['generate']['r']['min'] + $dice->pt($GLOBALS['meshal']['generate']['r']['dice']));

    $char->pr->set('base', $GLOBALS['meshal']['generate']['pr']['min'] + $dice->num($GLOBALS['meshal']['generate']['pr']['diceNum']) * $dice->pt($GLOBALS['meshal']['generate']['pr']['dicePt']));
    $char->ms->set('base', $GLOBALS['meshal']['generate']['ms']['min'] + $dice->pt($GLOBALS['meshal']['generate']['ms']['dice']));

    $char->ap->set('base', $GLOBALS['meshal']['generate']['ap']['min'] + $dice->pt($GLOBALS['meshal']['generate']['ap']['dice']));

    //计算实力
    $strength = array();

    $strength['m'] = array_sum(range(0, $char->m->base)) * $GLOBALS['meshal']['character']['strength']['attr'];
    $strength['a'] = array_sum(range(0, $char->a->base)) * $GLOBALS['meshal']['character']['strength']['attr'];
    $strength['s'] = array_sum(range(0, $char->s->base)) * $GLOBALS['meshal']['character']['strength']['attr'];

    if($char->ip->base == 0) {
        $strength['t'] = array_sum(range(0, $char->t->base)) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['t'] = 0;
    }

    if($char->ie->base == 0) {
        $strength['e'] = array_sum(range(0, $char->e->base)) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['e'] = 0;
    }

    if($char->io->base == 0) {
        $strength['r'] = array_sum(range(0, $char->r->base)) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['r'] = 0;
    }

    $strength['pr'] = $char->pr->base * $GLOBALS['meshal']['character']['strength']['pr'];
    $strength['ms'] = $char->ms->base * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['ap'] = array_sum(range(0, $char->ap->base)) * $GLOBALS['meshal']['character']['strength']['ap'];

    $char->strength->set('base', array_sum($strength));

    //生成随机特征
    localRandFeature('species');
    localRandFeature('ethnicity', true);
    localRandFeature('faction', true);
    localRandFeature('gender', true);
    localRandFeature('size', true);
    localRandFeature('form', true);


    //把当前属性设置成最大属性值
    $char->m->restore();
    $char->a->restore();
    $char->s->restore();
    $char->ap->restore();
    $char->cc->update();
    
    //更新或保存用户生成记录
    if(
        $db->getCount(
            'character_stage',
            array(
                "`uid` = '{$user->uid}'"
            )
        ) == 0
    ) {
        $insert = $char->export();
        $insert['uid'] = $user->uid;
        $insert['creatorId'] = $user->uid;
        $insert['timestamp'] = time();
        unset($insert['id']);
        unset($insert['ownerId']);

        $db->insert(
            'character_stage',
            $insert
        );
    } else {
        //删除这个角色的肖像文件
        $record = $db->getArr(
            'character_stage',
            array("`uid` = '{$user->uid}'"),
            null,
            1
        )[0];
        if(
            file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$record['portrait'])
            && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$record['portrait'])
        ) {
            unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$record['portrait']);
        }

        //更新角色数据到stage中
        $insert = $char->export();
        $insert['timestamp'] = time();
        unset($insert['id']);
        unset($insert['ownerId']);
        unset($insert['creatorId']);

        $db->update(
            'character_stage',
            $insert,
            array(
                "`uid` = '{$user->uid}'"
            ),
            1
        );
    }
}

function localRandFeature(
    string $featureType,
    bool $checkAvailability = false
) {
    global $db;
    global $char;

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
    \fLog(\fDump($pool));

    $pick = ( //决定要随机几个特征
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
