<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal冒险的类
################################################

class xOwnership {
    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * 设置一条权重记录
     * 
     * @param int $shares
     * 修改份额的数量
     * 
     * @param int $uid
     * 修改份额的用户id
     * 
     * @param string $type
     * 修改份额的content类型
     * 
     * @param string $content
     * 修改份额的content标识符
     */
    public static function set(
        int $shares,
        int $uid,
        string $type,
        string $content
    ) {
        global $db;
        $query = $db->getArr(
            'ownership',
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'"
            ),
            null,1
        );

        if($query === false) { //如果没有记录则创建
            $db->insert(
                'ownership',
                array(
                    'uid' => $uid,
                    'type' => $type,
                    'content' => $content,
                    'shares' => $shares
                )
            );
        }

        else { //有记录则直接设置新的权重
            $db->update(
                'ownership',
                array(
                    'shares' => $shares
                ),
                array(
                    "`uid` = '{$uid}'",
                    "`type` = '{$type}'",
                    "`content` = '{$content}'"
                ),
                1
            );
        }
    }

    /**
     * 向一条记录修改权重
     * 
     * @param int $shares
     * 修改份额的数量
     * 
     * @param int $uid
     * 修改份额的用户id
     * 
     * @param string $type
     * 修改份额的content类型
     * 
     * @param string $content
     * 修改份额的content标识符
     */
    public static function mod (
        int $shares,
        int $uid,
        string $type,
        string $content
    ) {
        global $db;
        $query = $db->getArr(
            'ownership',
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'"
            ),
            null,1
        );

        if($query === false) { //没有记录则创建
            $db->insert(
                'ownership',
                array(
                    'uid' => $uid,
                    'type' => $type,
                    'content' => $content,
                    'shares' => $shares
                )
            );
        } 
        
        else { //有记录则更新
            $db->update(
                'ownership',
                array(
                    'shares' => $query[0]['shares'] + $shares
                ),
                array(
                    "`uid` = '{$uid}'",
                    "`type` = '{$type}'",
                    "`content` = '{$content}'"
                ),
                1
            );
        }
    }

    /**
     * 向一条记录添加权重
     * 
     * @param int $shares
     * 修改份额的数量
     * 
     * @param int $uid
     * 修改份额的用户id
     * 
     * @param string $type
     * 修改份额的content类型
     * 
     * @param string $content
     * 修改份额的content标识符
     */
    public static function add (
        int $shares,
        int $uid,
        string $type,
        string $content
    ) {
        global $db;
        $query = $db->getArr(
            'ownership',
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'"
            ),
            null,1
        );

        if($query === false) { //没有记录则创建
            $db->insert(
                'ownership',
                array(
                    'uid' => $uid,
                    'type' => $type,
                    'content' => $content,
                    'shares' => $shares
                )
            );
        } 
        
        else { //有记录则更新
            $db->update(
                'ownership',
                array(
                    'shares' => $query[0]['shares'] + $shares
                ),
                array(
                    "`uid` = '{$uid}'",
                    "`type` = '{$type}'",
                    "`content` = '{$content}'"
                ),
                1
            );
        }
    }

    /**
     * 向一条记录减少权重
     * 
     * @param int $shares
     * 修改份额的数量
     * 
     * @param int $uid
     * 修改份额的用户id
     * 
     * @param string $type
     * 修改份额的content类型
     * 
     * @param string $content
     * 修改份额的content标识符
     */
    public static function sub (
        int $shares,
        int $uid,
        string $type,
        string $content
    ) {
        global $db;
        $query = $db->getArr(
            'ownership',
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'"
            ),
            null,1
        );

        if($query === false) { //没有记录则创建
            $db->insert(
                'ownership',
                array(
                    'uid' => $uid,
                    'type' => $type,
                    'content' => $content,
                    'shares' => $shares
                )
            );
        } 
        
        else { //有记录则更新
            $db->update(
                'ownership',
                array(
                    'shares' => $query[0]['shares'] - $shares
                ),
                array(
                    "`uid` = '{$uid}'",
                    "`type` = '{$type}'",
                    "`content` = '{$content}'"
                ),
                1
            );
        }
    }

    /**
     * 统计一个内容的所有权份额的总量
     * 
     * @param string $type
     * 查询的content类型
     * 
     * @param string $content
     * 查询的content标识符
     */
    public static function sum (
        string $type,
        string $content
    ) {
        global $db;
        return $db->getSum(
            'ownership',
            '`shares`',
            array(
                "`type` = '{$type}'",
                "`content` = '{$content}'"
            )
        );
    }

    /**
     * 获取一个用户在某个内容上的份额
     * 
     * @param int $uid
     * 查询的用户id
     * 
     * @param string $type
     * 查询的content类型
     * 
     * @param string $content
     * 查询的content标识符
     * 
     * @return int
     * 返回份额数量
     */
    public static function getShares (
        int $uid,
        string $type,
        string $content
    ) {
        global $db;
        $query = $db->getArr(
            'ownership',
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'"
            ),
            null,1
        );

        if($query === false) {
            return 0;
        } else {
            return $query[0]['shares'];
        }
    }

    /**
     * 获取还未入账的shares
     * 
     * @param int $uid
     * 查询的用户id
     * 
     * @param string $type
     * 查询的content类型
     * 
     * @param string $content
     * 查询的content标识符
     * 
     * @return int
     * 返回份额数量
     */
    public static function getStaking (
        int $uid,
        string $type,
        string $content
    ) {
        global $db;
        return $db->getSum(
            'epoch_staking',
            '`shares`',
            array(
                "`uid` = '{$uid}'",
                "`type` = '{$type}'",
                "`content` = '{$content}'",
                "`sealed` = '0'"
            )
        );
    }

    /**
     * 获取还未入账的shares（全部）
     * 
     * @param string $type
     * 查询的content类型
     * 
     * @param string $content
     * 查询的content标识符
     * 
     * @return int
     * 返回份额数量
     */
    public static function getStakingAll (
        string $type,
        string $content
    ) {
        global $db;
        return $db->getSum(
            'epoch_staking',
            '`shares`',
            array(
                "`type` = '{$type}'",
                "`content` = '{$content}'",
                "`sealed` = '0'"
            )
        );
    }

    /**
     * 计算一个用户在总体份额中的比重
     * 
     * @param int $uid
     * 查询的用户id
     * 
     * @param string $type
     * 查询的content类型
     * 
     * @param string $content
     * 查询的content标识符
     * 
     * @return
     * 返回浮点数的比例
     */
    public static function getPortion (
        int $uid,
        string $type,
        string $content
    ) {
        return \fDiv(
            self::getShares($uid, $type, $content),
            self::sum($type, $content)
        );
    }

    /**
     * 获取一个内容的所有权人数
     * 
     * @param string $type
     * 查询的content类型
     * 
     * @param string $content
     * 查询的content标识符
     * 
     * @param int $minShares
     * 最小shares要求
     * 
     * @return int
     */
    public static function count (
        string $type,
        string $content,
        int $minShares = 0
    ) {
        global $db;
        return $db->getCount(
            'ownership',
            array(
                "`type` = '{$type}'",
                "`content` = '{$content}'",
                "`shares` > {$minShares}"
            )
        );
    }

    /**
     * 获取shares排名前n位的uid
     * 
     * @param string $type
     * 查询的content类型
     * 
     * @param string $content
     * 查询的content标识符
     * 
     * @param int $tops = 3
     * 取排名前几位
     * 
     * @return array
     * 返回一个数组，每个成员是一个所有权记录
     */
    public static function getTopOwners (
        string $type,
        string $content,
        int $tops = 3
    ) {
        global $db;
        return $db->getArr(
            'ownership',
            array(
                "`type` = '{$type}'",
                "`content` = '{$content}'",
                "`shares` > 0"
            ),
            null,
            $tops,
            null,
            '`shares`',
            'DESC'
        );
    }

    public static function stake (
        int $uid,
        string $type,
        string $content,
        $amount = 0
    ) {
        global $db;
        
    }
}

?>