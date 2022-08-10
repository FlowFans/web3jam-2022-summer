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
$dice = new meshal\xDice;
$user = new \xUser;

//如果是submit
if($_POST['submit']) {
    if(\fPost('adventureName', '') == '') {
        $html->redirect(
            'adventure.php',
            'pageTitle.maintainance.generator.adventure',
            'redirect.message.maintainance.generator.adventure.noName'
        );
    
        \fDie();
    }

    $db->insert(
        'adventures',
        array(
            'name' => $_POST['adventureName'],
            'coverImage' => \fEncode($_POST['coverImage']),
            'duration' => $_POST['duration'],
            'teamMin' => $_POST['teamMin'],
            'teamMax' => $_POST['teamMax'],
            'strengthMin' => is_null($_POST['strengthMin']) || $_POST['strengthMin'] == '' ? null : $_POST['strengthMin'],
            'strengthMax' => is_null($_POST['strengthMax']) || $_POST['strengthMax'] == '' ? null : $_POST['strengthMax'],
            'apCost' => $_POST['apCost'],
            'type' => json_encode(json_decode($_POST['type'], true)),
            'loot' => json_encode(json_decode($_POST['loot'], true)),
            'data' => json_encode(json_decode($_POST['data'], true)),
            'lastUpdate' => time()
        )
    );
    $html->redirect(
        'adventure.php',
        'pageTitle.maintainance.generator.adventure',
        'redirect.message.maintainance.generator.adventure.created'
    );

    \fDie();
}

//批量生成随机冒险
$rand = \fArrayRandWt(
    array(
        3 => 11,
        4 => 22,
        5 => 100,
        6 => 45,
        7 => 21,
        8 => 13
    )
)[0];

$dur = mt_rand(30, $rand * 60) * 100; //冒险时长
$ap = intval($dur / 14400) + 1; //AP消耗
$teamMax = \fArrayRandWt( //组队人数要求
    array(
        1 => 320,
        2 => 80,
        3 => 20,
        4 => 1
    )
)[0];

$coverImage = \fGenGuid('adventureCover'); //冒险封面图

$encounters = $db->getArr(
    'encounters',
    array(),
    null,
    $rand,
    null,
    null,
    'RAND'
);
$count = count($encounters);

$result = array(
    'name' => $_GET['name'],
    'coverImage' => $coverImage.'.jpg',
    'duration' => $dur,
    'apCost' => $ap,
    'teamMin' => 1,
    'teamMax' => $teamMax,
    'strengthMin' => null,
    'strengthMax' => null,
    'type' => array(
        'scavenge',
        'supply'
    ),
    'loot' => array(),
    'data' => array(
        'entrance' => array(),
        'scenes' => array(),
        'end' => array(
            array(
                'checkAll' => array(),
                    'checkAny' => array(),
                    'success' => array(
                        array("addPotentialityToTeam", 1, intval($dur / 14400), 0)
                    )
            )
        )
    )
);

$loot = array();
foreach ($encounters as $k => $encounter) {
    $result['data']['scenes'][] = array(
        'encounter' => $encounter['name'],
        'next' => array(
            'success' => array(),
            'failure' => array(),
            'default' => array()
        )
    );
    $encounterLoot = json_decode($encounter['loot'], true);
    foreach ($encounterLoot as $l => $item) {
        $loot[$item] = $item;
    }
}

//设置掉落清单
$result['loot'] = array_keys($loot);

//设置入口
$entrances = \fArrayRandWt(
    array(
        1 => 64,
        2 => 16,
        3 => 4,
        4 => 1
    )
)[0];
if($entrances > $count) $entrances = $count;

$result['data']['entrance'][0] = array(
    'probability' => mt_rand(1, 100),
    'checkAll' => array(),
    'checkAny' => array()
);
$last = 1;
for ($i=0; $i < $entrances; $i++) { 
    if($last >= $count - 1) break;
    $last = mt_rand($last + 1, $count - 1);
    $result['data']['entrance'][$last] = array(
        'probability' => mt_rand(1, 100),
        'checkAll' => array(),
        'checkAny' => array()
    );
}

//设置关卡衔接
foreach($result['data']['scenes'] as $k => $encounter) {
    //总是把下一个遭遇配置为基本出口
    $result['data']['scenes'][$k]['next']['default'][$k+1] = array(
        'probability' => mt_rand(1, 100),
        'checkAll' => array(),
        'checkAny' => array()
    );

    //随机有几个额外出口
    $nexts = \fArrayRandWt(
        array(
            0 => 100,
            1 => 10,
            2 => 1
        )
    )[0];
    $last = $k + 1;

    for ($i=0; $i < $nexts; $i++) {
        if($last >= $count-1) {
            break;
        }
        $next = mt_rand($last, $count-1);
        $last = $next + 1;
        $result['data']['scenes'][$k]['next']['default'][$next] = array(
            'probability' => mt_rand(1, 100),
            'checkAll' => array(),
            'checkAny' => array()
        );
    }

    if($k == $count-1) {
        $result['data']['scenes'][$k]['next'] = null;
    }
}

$output = array(
    'name' => $result['name'],
    'coverImage' => $result['coverImage'],
    'duration' => $result['duration'],
    'teamMin' => $result['teamMin'],
    'teamMax' => $result['teamMax'],
    'strengthMin' => $result['strengthMin'],
    'strengthMax' => $result['strengthMax'],
    'apCost' => $result['apCost'],
    'type' => json_encode($result['type'], JSON_PRETTY_PRINT),
    'loot' => json_encode($result['loot'], JSON_PRETTY_PRINT),
    'data' => json_encode($result['data'], JSON_PRETTY_PRINT),
    'lastUpdate' => time()
);

$tmp = '';
$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'adventureName',
        '--var' => is_null($output['name']) || $output['name'] == '' ? '' : $output['name']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'coverImage',
        '--var' => $output['coverImage']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'duration',
        '--var' => $output['duration']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'teamMin',
        '--var' => $output['teamMin']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'teamMax',
        '--var' => $output['teamMax']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'strengthMin',
        '--var' => is_null($output['strengthMin']) ? '' : $output['strengthMin']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'strengthMax',
        '--var' => is_null($output['strengthMax']) ? '' : $output['strengthMax']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.input.html',
    array(
        '--title' => 'apCost',
        '--var' => $output['apCost']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.textarea.html',
    array(
        '--title' => 'type',
        '--rows' => 4,
        '--var' => $output['type']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.textarea.html',
    array(
        '--title' => 'loot',
        '--rows' => 8,
        '--var' => $output['loot']
    )
);

$tmp .= $html->quickRender(
    'maintainance/generator/adventure/row.textarea.html',
    array(
        '--title' => 'data',
        '--rows' => 16,
        '--var' => $output['data']
    )
);

$html->loadTpl('maintainance/generator/adventure/body.frame.html');
$html->set('$output', $tmp);
$html->output();
fDie();
?>
