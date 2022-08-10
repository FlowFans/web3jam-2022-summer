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

// $GLOBALS['debug']['log'] = FALSE; //在本脚本中，临时关闭debug日志记录，以避免产生无意义的数据库查询记录。

################################################
# 初始化结束
################################################

/** 
 * GET方法传入参数
 * 
 * token
 * 通过这个选择器给什么特征赋予特征，token对应的是feature_stage中的数据记录
 * 
 * containerCategory
 * 向哪一组数据添加特征，目前可用的有2项
 * - availableFeature: 可选特征
 * - addFeature: 会自动获得的特征
 * 
 * container
 * 向哪个特征容器添加特征
 * 多数特征容器和特征类型相同，但也需要注意比如半速移动方式（mobilityPoor)和减半感知方式(perceptionPoor)
 * 
 * featureType
 * 筛选什么特征类型用于选择
 * 
 * action
 * 操作参数，须和name参数一同使用
 * - 添加特征则action=add
 * - 移除特征则action=remove
 * 
 * name
 * 和action参数一同使用，添加/移除特征的名称
 */


use \meshal\xFeature as xFeature;
// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/embeded.css');
$html->loadCss('css/meshal.css');
$html->loadTpl('editor/feature/dup.featureSelector.html', 'body');

$user->challengeRole('admin', 'editor');



if(!$_GET['featureType']) {
    \fLog("Error: no type assigned");
//还需要设计一个内容为空的页面
    \fDie();
}

if(!$_GET['containerCategory']) {
    \fLog("Error: no container assigned");
//还需要设计一个内容为空的页面
    \fDie();
}

if(!$_GET['container']) {
    \fLog("Error: no container assigned");
//还需要设计一个内容为空的页面
    \fDie();
}

//从feature_stage读取记录
//检查是否有token
if(!$_GET['token']) {
    \fLog("Error: no token is given");
//还需要设计一个内容为空的页面
    \fDie();
}

$stage = $db->getArr(
    'feature_stage',
    array(
        "`stageToken` = '{$_GET['token']}'"
    ),
    null,
    1
);
if($stage === false) {
    \fLog("Error: no feature stage data fetched");
//还需要设计一个内容为空的页面
    \fDie();
} else {
    $stage = $stage[0];
    $stage['name'] = $stage['name'];
}

//如果有提交add/remove，做添加/移除操作
if($_GET['action']) {
    switch (true) {
        case (
            $_GET['action']=='add'
            && $_GET['name']
        ):        
            $stageData = json_decode($stage[$_GET['containerCategory']], true);

            if($stageData[$_GET['container']]) {
                if(array_search($_GET['name'], $stageData[$_GET['container']]) === false) {
                    $stageData[$_GET['container']][] = $_GET['name'];
                }
            } else {
                $stageData[$_GET['container']][] = $_GET['name'];
            }

            $db->update(
                'feature_stage',
                array(
                    $_GET['containerCategory'] => json_encode($stageData)
                ),
                array(
                    "`stageToken` = '{$_GET['token']}'"
                ),
                1
            );
            break;
        
        case (
            $_GET['action']=='remove'
            && $_GET['name']
        ):
            $stageData = json_decode($stage[$_GET['containerCategory']], true);

            if(array_search($_GET['name'], $stageData[$_GET['container']]) !== false) {
                unset($stageData[$_GET['container']][array_search($_GET['name'], $stageData[$_GET['container']])]);
            }
            $db->update(
                'feature_stage',
                array(
                    $_GET['containerCategory'] => json_encode($stageData)
                ),
                array(
                    "`stageToken` = '{$_GET['token']}'"
                ),
                1
            );
            break;

        default:
            \fLog("Error: invalid action submitted: action={$_GET['action']}, name={$_GET['name']}");
            break;
    }
}

/**
 * 以下代码则是用于显示选择器
 */
//重新获取更新后的数据
$stage = $db->getArr(
    'feature_stage',
    array(
        "`stageToken` = '{$_GET['token']}'"
    ),
    null,
    1
);
if($stage === false) {
    \fLog("Error: no feature stage data fetched");
//还需要设计一个内容为空的页面
    \fDie();
} else {
    $stage = $stage[0];
    $stage['name'] = $stage['name'];
}

//根据传入的参数进行查询
$select = array();
if($_GET['featureType']) {
    $select[] = "`type` = '{$_GET['featureType']}'";
}

$featureCount = $db->getCount(
    'features',
    $select
);

if($featureCount > 0) {
    # 根据当前页取特征
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['editor']['FeatureSelectorPerPage'];

    $queryFeatures = $db->getArr(
        'features',
        $select,
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['editor']['FeatureSelectorPerPage']}",
        null,
        'name',
        'DESC'
    );

    #组装特征列表
    if($queryFeatures !== false) {
        //拼装特征数据
        $comp = array();
        foreach ($queryFeatures as $cur) {
            $tmp = array();
            $decodedName = $cur['name']; //数据库中的名字是做过编码处理的，因此要先做解码
            $tmp['--name'] = $decodedName;
            $stageData = json_decode($stage[$_GET['containerCategory']], true);
            
            if(!is_null($stageData[$_GET['container']])) {
                if(array_search($cur['name'], $stageData[$_GET['container']]) !== false) {
                    //给已经选中的特征做选中标记
                    $tmp['--selected'] = 'nzSelectionActive';
                    $tmp['--url'] = "?action=remove&name={$decodedName}&containerCategory={$_GET['containerCategory']}&container={$_GET['container']}&featureType={$_GET['featureType']}&token={$_GET['token']}&page=".\fGet('page', 1);
                } else {
                    $tmp['--selected'] = '';
                    $tmp['--url'] = "?action=add&name={$decodedName}&containerCategory={$_GET['containerCategory']}&container={$_GET['container']}&featureType={$_GET['featureType']}&token={$_GET['token']}&page=".\fGet('page', 1);
                }
            } else {
                $tmp['--selected'] = '';
                $tmp['--url'] = "?action=add&name={$decodedName}&containerCategory={$_GET['containerCategory']}&container={$_GET['container']}&featureType={$_GET['featureType']}&token={$_GET['token']}&page=".\fGet('page', 1);
            }
            $tmp['--featurePoor'] = '';
            $tmp['--displayPoor'] = 'hidden';
            $tmp['--featureType'] = $_GET['featureType'];
            $comp[] = $tmp;
        }

        $html->set(
            '$featureList', 
            $html->duplicate(
                'editor/feature/dup.featureSelector.option.html',
                $comp
            )
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $featureCount,
            $GLOBALS['setting']['pager']['editor']['FeatureSelectorPerPage'],
            "?containerCategory={$_GET['containerCategory']}&container={$_GET['container']}&featureType={$_GET['featureType']}&token={$_GET['token']}&page=".'{?$page?}'
        )
    );
} else {
    $html->set('$featureList', '');
    $html->set('$pager', '');
}

# 组装特征选择器
$typeMenu = array();
foreach ($GLOBALS['meshal']['featureType'] as $typeName => $settings) {
    $typeMenu[] = array(
        // '--type' => $typeName,
        '--url' => $typeName == $_GET['type'] ? '?type=' : "?type={$typeName}",
        '--active' => $typeName == $_GET['type'] ? 'nzSelectionActive' : '',
        '--name' => "{?{$settings['name']}?}"
    );
}
$html->set('$typeMenu', $html->duplicate(
    'editor/feature/dup.filterRow.html',
    $typeMenu
));

$html->output('embed');
\fDie();



?>