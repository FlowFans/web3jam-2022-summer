<?php
namespace meshal\char;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供角色信息的类
################################################

class xAdapter {
    function __construct() {
        global $db;
        $this->db = $db;

        $this->version = null; //此角色的适配最低版本
        $this->id = null; //角色id
        $this->ownerId = null; //拥有者uid
        $this->creatorId = null; //创作者uid

        $this->stat = null; //角色当前状态

        $this->sortScore = 0; //角色排序得分
        $this->lastUpdate = 0; //角色最后更新的时间
        $this->recoverStart = 0; //角色开始恢复时间
        $this->portrait = null; //角色头像
        $this->name = null; //角色名称
        $this->bio = null; //角色小传

        $this->data = array();
    }

    public function load(
        int $charId
    ) {
        $query = $this->db->getArr(
            'characters',
            array(
                "`id` = '{$charId}'"
            ),
            null,
            1
        );

        if($query === false) {
            \fLog("Error: cannot fetch character({$charId})'s data");
            return false;
        }

        $this->id = $query[0]['id'];
        $this->stat = $query[0]['stat'];
        
        $this->ownerId = $query[0]['ownerId'];
        $this->creatorId = $query[0]['creatorId'];

        $this->sortScore = $query[0]['sortScore'];
        $this->version = $query[0]['version'];
        $this->lastUpdate = $query[0]['lastUpdate'];
        $this->recoverStart = $query[0]['recoverStart'];

        $this->name = \fDecode($query[0]['name']);
        $this->portrait = \fDecode($query[0]['portrait']);
        $this->bio = \fDecode($query[0]['bio']);

        $this->data = json_decode($query[0]['data'], true);
        return true;
    }

    /**
     * 保存角色
     * 
     * @return bool
     * 如果保存成功返回true，否则返回false
     */
    public function save() {
        global $db;

        if(is_null($this->id)) {
            \fLog("Error: no character id given");
            return false;
        }
        
        $check = $db->update(
            'characters',
            array(
                'stat' => $this->stat,
                'sortScore' => $this->sortScore,
                'lastUpdate' => time(),
                'recoverStart' => $this->recoverStart,
                'version' => $this->version,
                'ownerId' => $this->ownerId,
                'creatorId' => $this->creatorId,
                'name' => \fEncode($this->name),
                'portrait' => \fEncode($this->portrait),
                'bio' => \fEncode($this->bio),
                'data' => json_encode($this->data)
            ),
            array(
                "`id` = '{$this->id}'"
            ),
            1,
            false
        );

        if($check != false) {
            \fLog("Character(id={$this->id}) updated");
            return true;
        } else {
            \fLog("Failed to update character(id={$this->id})");
            return false;
        }
    }
}
?>