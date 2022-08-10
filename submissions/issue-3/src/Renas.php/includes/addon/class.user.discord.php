<?php
namespace user;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供用户discord绑定关系的类
################################################

class xDiscord {
    function __construct(
        object &$parent
    ) {
        //检查接入的对象类型
        if(
            get_class($parent) != 'user\xAdapter'
            && get_class($parent) != 'xUser'
        ) {
            \fLog("Wrong integration of parent object class");
        }

        $this->parent = &$parent;
        $this->db = &$this->parent->db;
    }

    /**
     * 传递discordId，返回uid
     * 
     * @param $discordId
     * 传递的discordId
     * 
     * @return int|false
     * 返回这个discordId对应的uid
     * 如果没有找到，返回false
     */
    public static function getUid(
        $discordId
    ) {
        global $db;

        $query = $db->getArr(
            'user_discord',
            array(
                "`discordId` = '{$discordId}'"
            ),
            null,1
        );

        if($query === false) {
            \fLog("Error: discordId({$discordId}) doesn't exist or not bond with any uid");
            return false;
        }

        return $query[0]['uid'];
    }

    /**
     * 传递uid，返回discordId
     * 
     * @param $uid
     * 传递的uid
     * 
     * @return int|false
     * 返回这个uid对应的discordId
     * 如果没有找到，返回false
     */
    public static function getDiscordId(
        int $uid
    ) {
        global $db;

        $query = $db->getArr(
            'user_discord',
            array(
                "`uid` = '{$uid}'"
            ),
            null,1
        );

        if($query === false) {
            \fLog("Error: user({$uid}) has no discordId mapped");
            return false;
        }

        return $query[0]['discordId'];
    }
}

?>