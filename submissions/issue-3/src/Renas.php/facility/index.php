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
$html->loadTpl('facilities/body.frame.html', 'body');

/**
 * 组装用户现有的建筑列表
 */
$facilityCount = $db->getCount(
    'user_facilities',
    array(
        "`uid` = '{$user->uid}'",
        "`level` > '0'"
    )
);

if($facilityCount > 0) {
    // $html->set('$emptyWarehouseGuide', '');
    # 根据当前页取设施
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['facility']['facilitiesPerPage'];

    $queryFacilities = $db->getArr(
        'user_facilities',
        array(
            "`uid` = '{$user->uid}'",
            "`level` > '0'"
        ),
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['facility']['facilitiesPerPage']}",
        MYSQLI_NUM,
        'lastUpdate',
        'DESC'
    );

    #组装设施列表
    $facilityRenderer = new \meshal\xFacility;
    $facilityList = '';
    if($queryFacilities !== false) {
        //拼装设施数据
        $comp = array();
        foreach ($queryFacilities as $cur) {
            $facilityRenderer->load($cur['name'], $cur['level']);

            #添加设施操作
            //升级
            switch ($facilityRenderer->checkUpgrade($user->uid)) {
                case 0: //可以升级下一级，渲染enabled按钮
                    $nextLevel = $facilityRenderer->level + 1;
                    $upgradeTime = $db->getArr(
                        'facility_building',
                        array(
                            "`uid` = '{$user->uid}'",
                            "`facilityName` = '{$facilityRenderer->name}'",
                            "`facilityLevel` = '{$nextLevel}'"
                        )
                    );
                    if($upgradeTime === false) {
                        $facilityRenderer->addCtrl(
                            _ROOT."facility/upgrade/?facility={$facilityRenderer->name}&level={$facilityRenderer->level}".\fBackUrl(),
                            'button.facility.upgrade',
                            'bgOpaGreen1 colorWhite1'
                        );
                    }
                    break;

                case 1: //没有下一级数据，不渲染升级按钮
                    break;

                case 4:
                    break;
                
                default: //默认渲染一个disable按钮
                    $facilityRenderer->addCtrl(
                        '#',
                        'button.facility.upgrade',
                        'bgOpaGrey1 colorWhite1'
                    );
                    break;
            }
            
            $facilityList .= $facilityRenderer->render();
        }

        $html->set(
            '$facilityList', 
            $facilityList
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $facilityCount,
            $GLOBALS['setting']['pager']['facility']['facilitiesPerPage'],
            '?page={?$page?}'
        )
    );
} else {
    // $html->set('$emptyWarehouseGuide', $html->readTpl('guide/warehouseEmpty.html'));
    $html->set('$facilityList', '');
    $html->set('$pager', '');
}

/**
 * 组装建造中的建筑列表
 */
$buildingCount = $db->getCount(
    'facility_building',
    array(
        "`uid` = '{$user->uid}'",
        "`facilityLevel` > '0'"
    )
);
if($buildingCount > 0) {
    # 根据当前页取设施
    // $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['facility']['facilitiesPerPage'];
    $queryFacilities = $db->getArr(
        'facility_building',
        array(
            "`uid` = '{$user->uid}'",
            "`facilityLevel` > '0'"
        )
    );

    #组装设施列表
    $facilityRenderer = new \meshal\xFacility;
    $buildingList = '';
    if($queryFacilities !== false) {
        //拼装设施数据
        $comp = array();
        foreach ($queryFacilities as $cur) {
            $facilityRenderer->load($cur['facilityName'], $cur['facilityLevel']);

            //显示倒计时
            $facilityRenderer->buildCountdown = $cur['endTime'];

            $buildingList .= $facilityRenderer->render();
        }

        $html->set(
            '$buildingList', 
            $buildingList
        );
        $html->set('$showBuilding', '');
    }
} else {
    $html->set('$buildingList', '');
    $html->set('$showBuilding', 'hidden');
}

$html->output();

\fDie();
?>