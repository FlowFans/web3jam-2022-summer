<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供短链接处理的类
################################################

/**
 * 说明
 * 
 */

/**
 * 常用方法
 */

class xUrl
{
    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * 传递一个完整url，生成并返回一个短链的参数
     * 
     * @param string $fullUrl
     * 传递的完整url
     * 
     * @return 
     * 返回一个短链参数（62进制）
     */
    public static function genUrl (
        string $fullUrl
    ) {
        global $db;

        //检查是否有$fullUrl相同的记录
        $encoded = \fEncode($fullUrl);

        $query = $db->getArr(
            'url',
            array(
                "`url` = '{$encoded}'"
            ),
            null,
            1
        );

        if($query !== false) { //有相同的记录，直接返回id
            \fLog("The URL ({$encoded}) already exists");
            return \fDecConvert($query[0]['id']);
        } 

        else { //否则生成一条新的纪录并返回id
            $id = $db->insert(
                'url',
                array(
                    'url' => \fEncode($fullUrl)
                )
            );

            if($id === false) {
                \fLog("Failed to create the new URL ({$fullUrl}) entry.");
                return false;
            } else {
                \fLog("The new URL ({$fullUrl}) entry created, id = {$id}");
                return \fDecConvert($id);
            }
        }
    }

    /**
     * 将调用本方法的页面完整URL生成短链
     * 
     * @return
     * 返回一个短链参数（62进制）
     */
    public static function genThis() {
        return self::genUrl($_SERVER['REQUEST_URI']);
    }

    /**
     * 根据给定的短链参数，返回完整url
     * 
     * @param string $shortCode
     * 传递的短链参数
     * 
     * @return
     * 返回完整的链接
     */
    public static function getUrl (
        $shortCode
    ) {
        if($shortCode == false || $shortCode == '') {
            return false;
        }

        global $db;

        $id = \fDecRevert($shortCode);

        $query = $db->getArr(
            'url',
            array(
                "`id` = '{$id}'"
            ),
            null,1
        );

        if($query === false) {
            return false;
        }

        return \fDecode($query[0]['url']);
    }

    /**
     * 根据短链参数跳转到指定的完整URL
     * 
     * @param string $shortCode
     * 传递的短链参数
     */
    public static function gotoUrl(
        string $shortCode
    ) {
        $fullUrl = self::getUrl($shortCode);
        if($fullUrl == false) {
            header("Location: "._ROOT.$GLOBALS['deploy']['defaultPage']);
        } else {
            header("Location: ".$fullUrl);
        }
        
        \fDie();
    }
}