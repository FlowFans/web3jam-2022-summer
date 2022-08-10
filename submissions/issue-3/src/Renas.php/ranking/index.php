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

$db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser(false);

$html->loadCss('css/meshal.css');
require_once _ROOT.DIR_CFG.'ranking.php';

/**
 * 计算统计周期
 */
//最初始排行的时间戳，比这个早的时间不统计
$genesisTime = $GLOBALS['setting']['epoch']['begin'] + $GLOBALS['setting']['epoch']['span'] * $GLOBALS['setting']['period']['epochOffset'];

//根据当前所在的排行周期-1，得到最后一个完整的排行周期
$lastPeriod = floor((\fEpoch() - $GLOBALS['setting']['period']['epochOffset']) / $GLOBALS['setting']['period']['epochPerPeriod']);

//取当前查询的排行周期
$queryPeriod = \fGet('period', $lastPeriod);

//如果当前查询的排行周期大于当前所在排行周期-1，设为当前所在排行周期-1
//如果查询的排行周期比最后一个排行周期大，那么视作查询最后一个排行周期
if($queryPeriod > $lastPeriod) $queryPeriod = $lastPeriod;

$periodEpochBegin = ($queryPeriod - 1) * $GLOBALS['setting']['period']['epochPerPeriod'] + $GLOBALS['setting']['period']['epochOffset'] + 1; //排行周期的第一个Epoch
$periodEpochEnd = $queryPeriod  * $GLOBALS['setting']['period']['epochPerPeriod'] + $GLOBALS['setting']['period']['epochOffset']; //排行周期的最后一个Epoch

$periodTimespan = \fRangeBetweenEpochs($periodEpochBegin, $periodEpochEnd); //计算出排行周期的精确时间范围

// \fPrint($lastPeriod);
// \fPrint($periodEpochBegin);
// \fPrint($periodEpochEnd);
// \fPrint($periodTimespan);

/**
 * 根据上面计算得到的时间范围进行数据查询
 */

switch (\fGet('board', '')) {
    case 'activeAdventurers': //查询冒险次数
        $html->set('$rankType', 'activeAdventurers');
        $html->loadTpl('ranking/activeAdventurers/body.frame.html', 'body');
        # 查询每个周期内，每个用户的冒险次数（去重）
        $query = $db->getArr(
            'adventure_chars',
            array(
                "`sealed` = '1'",
                "`startTime` >= '{$periodTimespan['start']}'",
                "`endTime` <= '{$periodTimespan['end']}'"
            ),
            array(
                'COUNT(DISTINCT `adventureId`,`charId`) as `count`',
                '`charId`'
            ),
            $GLOBALS['meshal']['ranking']['tops']['activeAdventurers'],
            null,
            '`count`',
            'DESC',
            '`charId`'
        );

        $comp = array();
        if($query !== false) {
            $rank = 0;
            foreach ($query as $cur) {
                $rank++;
                $ownerId = \meshal\xChar::getOwnerId($cur['charId']);
                $comp[] = array(
                    '--char' => \meshal\xChar::renderTag($cur['charId']),
                    '--owner' => ($ownerId === false || is_null($ownerId)) ? '' : \xUser::renderTag($ownerId),
                    '--count' => $cur['count'],
                    '--rank' => $rank
                );
            }
            // fPrint($comp);
        }

        $html->set('$tops', $GLOBALS['meshal']['ranking']['tops']['activeAdventurers']);
        $html->set(
            '$list', 
            $html->duplicate(
                'ranking/activeAdventurers/dup.row.html',
                $comp
            )
        );
        
        break;

    case 'adventures': //查询冒险次数
        $html->set('$rankType', 'adventures');
        $html->loadTpl('ranking/adventures/body.frame.html', 'body');
        # 查询每个周期内，每个用户的冒险次数（去重）
        $query = $db->getArr(
            'adventure_chars',
            array(
                "`sealed` = '1'",
                "`startTime` >= '{$periodTimespan['start']}'",
                "`endTime` <= '{$periodTimespan['end']}'"
            ),
            array(
                'COUNT(DISTINCT `adventureId`,`uid`) as `count`',
                '`uid`'
            ),
            $GLOBALS['meshal']['ranking']['tops']['adventures'],
            null,
            '`count`',
            'DESC',
            '`uid`'
        );

        $comp = array();
        if($query !== false) {
            $rank = 0;
            foreach ($query as $cur) {
                $rank++;
                $comp[] = array(
                    '--user' => \xUser::renderTag($cur['uid']),
                    '--count' => $cur['count'],
                    '--rank' => $rank
                );
            }
            // fPrint($comp);
        }

        $html->set('$tops', $GLOBALS['meshal']['ranking']['tops']['adventures']);
        $html->set(
            '$list', 
            $html->duplicate(
                'ranking/adventures/dup.row.html',
                $comp
            )
        );
        
        break;

    default:
        $html->set('$rankType', 'adventures');
        $html->loadTpl('ranking/adventures/body.frame.html', 'body');
        # 查询每个周期内，每个用户的冒险次数（去重）
        $query = $db->getArr(
            'adventure_chars',
            array(
                "`sealed` = '1'",
                "`startTime` >= '{$periodTimespan['start']}'",
                "`endTime` <= '{$periodTimespan['end']}'"
            ),
            array(
                'COUNT(DISTINCT `adventureId`,`uid`) as `count`',
                '`uid`'
            ),
            $GLOBALS['meshal']['ranking']['tops']['adventures'],
            null,
            '`count`',
            'DESC',
            '`uid`'
        );

        $comp = array();
        if($query !== false) {
            $rank = 0;
            foreach ($query as $cur) {
                $rank++;
                $comp[] = array(
                    '--user' => \xUser::renderTag($cur['uid']),
                    '--count' => $cur['count'],
                    '--rank' => $rank
                );
            }
            // fPrint($comp);
        }

        $html->set('$tops', $GLOBALS['meshal']['ranking']['tops']['adventures']);
        $html->set(
            '$list', 
            $html->duplicate(
                'ranking/adventures/dup.row.html',
                $comp
            )
        );
        
        break;
}

//组装翻页器
$html->set(
    '$pager',
    $html->pager(
        \fGet('period', $lastPeriod),
        $lastPeriod,
        1,
        '?board='.\fGet('board', '').'&period={?$page?}&_back='.\fGet('_back')
    )
);

$html->set('$startEpoch', $periodEpochBegin);
$html->set('$endEpoch', $periodEpochEnd);
$html->set('$startTime', \fFormatTime($periodTimespan['start']));
$html->set('$endTime', \fFormatTime($periodTimespan['end']));

# 组装排行榜选择器
$typeMenu = array();
foreach ($GLOBALS['meshal']['ranking']['types'] as $typeName => $settings) {
    $typeMenu[] = array(
        '--url' => "?board={$typeName}",
        '--active' => $typeName == \fGet('board', '') ? 'nzSelectionActive' : '',
        '--name' => "{?common.rankingType.{$typeName}?}",
        '--icon' => $settings['icon']
    );
}
$html->set('$rankingMenu', $html->duplicate(
    'ranking/dup.filterRow.html',
    $typeMenu
));

$html->output();

\fDie();
?>