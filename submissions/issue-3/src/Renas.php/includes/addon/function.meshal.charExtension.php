<?php
namespace meshal\char;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供对角色排序的算法
################################################

/**
 * 更新角色的排序得分
 * @param int $charId
 * 角色Id
 */
function updateSort (
    int $charId
) {
    // $db = new \xDatabase;
    global $db;
    
    $char = $db->getArr(
        'characters',
        array(
            "`id` = '{$charId}'"
        ),
        null,
        1
    );

    if($char === false) {
        return false;
    }

    $char = $char[0];

    /**
     * 计算排序
     * 
     * 排序算法为
     *  f(like) * f(bio) * f(portrait) * f(version) * f(adventures) + f(time)
     */

    # 计算Likes
    $likes = $db->getCount(
        'character_like',
        array(
            "`charId` = '{$charId}'",
            "`cancelled` != '1'",
            "`uid` != '{$char['ownerId']}'",
            "`uid` != '{$char['creatorId']}'"
        )
    );
    $scoreLike = log($likes + 1, $GLOBALS['setting']['character']['sort']['like']);

    # 计算Bio
    $scoreBio = log(strlen($char['bio']) + 1, $GLOBALS['setting']['character']['sort']['bio']);
    
    # 计算头像
    $scorePortrait = (is_null($char['portrait']) || $char['portrait'] == '') ? 1 : $GLOBALS['setting']['character']['sort']['portrait'];
    
    # 计算版本
    $scoreVersion = \fCheckVersion($char['version'], $GLOBALS['meshal']['version']['character']) < 0 ? 1 : $GLOBALS['setting']['character']['sort']['version'];
    
    # 计算近期参与的冒险
    $adventures = $db->getSum(
        'adventure_chars',
        '`startTime`',
        array(
            "`charId` = '{$charId}'"
        ),
        $GLOBALS['setting']['character']['sort']['adventureCount'],
        '`endTime`',
        'DESC'
    );
    $scoreAdventures = log(\fDiv($adventures, $GLOBALS['setting']['character']['sort']['adventureCount']) + 1, $GLOBALS['setting']['character']['sort']['adventures']) + 1;
    
    # 计算角色最后更新时间
    $scoreTime = log($char['lastUpdate'], $GLOBALS['setting']['character']['sort']['time']);

    $score = 
        $scoreLike
        * $scoreBio
        * $scorePortrait
        * $scoreVersion
        * $scoreAdventures
        + $scoreTime
    ;

    /**
     * 写入数据库
     */
    $db->update(
        'characters',
        array(
            'sortScore' => $score
        ),
        array(
            "`id` = {$charId}"
        ),
        1
    );
    $return = array(
        'score' => array(
            'total' => $score,
            'like' => $scoreLike,
            'bio' => $scoreBio,
            'portrait' => $scorePortrait,
            'time' => $scoreTime,
            'adventures' => $scoreAdventures
        ),
        'factor' => array(
            'like' => $likes,
            'bio' => strlen($char['bio']),
            'portrait' => (is_null($char['portrait']) || $char['portrait'] == '') ? 0 : 1,
            'timestamp' => $char['lastUpdate'],
            'adventures' => $adventures
        ),
        'info' => array(
            'name' => \fDecode($char['name']),
            'bio' => \fDecode($char['bio'])
        )
    );
    return $return;
}

/**
 * 给定属性的增量，计算出增加的实力
 * 
 * @param string $scoreName
 * 属性代码
 * 
 * @param int|float $from
 * 属性提升前的数值
 * 
 * @param int|float $to
 * 属性提升后的数值
 * 
 * @return int
 * 返回提升的实力
 */
function calcStrength (
    string $scoreName,
    $from,
    $to
) {
    switch ($scoreName) {
        case 'm':
            return (
                array_sum(range(0, $to))
                - array_sum(range(0, $from))
            ) * $GLOBALS['meshal']['character']['strength']['attr'];
            break;
        
        case 'a':
            return (
                array_sum(range(0, $to))
                - array_sum(range(0, $from))
            ) * $GLOBALS['meshal']['character']['strength']['attr'];
            break;

        case 's':
            return (
                array_sum(range(0, $to))
                - array_sum(range(0, $from))
            ) * $GLOBALS['meshal']['character']['strength']['attr'];
            break;

        case 't':
            return (
                array_sum(range(0, $to))
                - array_sum(range(0, $from))
            ) * $GLOBALS['meshal']['character']['strength']['protect'];
            break;
        
        case 'e':
            return (
                array_sum(range(0, $to))
                - array_sum(range(0, $from))
            ) * $GLOBALS['meshal']['character']['strength']['protect'];
            break;

        case 'r':
            return (
                array_sum(range(0, $to))
                - array_sum(range(0, $from))
            ) * $GLOBALS['meshal']['character']['strength']['protect'];
            break;

        case 'pr':
            return (
                $to - $from
            ) * $GLOBALS['meshal']['character']['strength']['pr'];
            break;

        case 'ms':
            return (
                $to - $from
            ) * $GLOBALS['meshal']['character']['strength']['ms'];
            break;

        case 'pr':
            return (
                array_sum(range(0, $to))
                - array_sum(range(0, $from))
            ) * $GLOBALS['meshal']['character']['strength']['ap'];
            break;

        case 'cc':
            return (
                $to - $from
            ) * $GLOBALS['meshal']['character']['strength']['cc'];
            break;
        
        default:
            return 0;
            break;
    }
}
?>