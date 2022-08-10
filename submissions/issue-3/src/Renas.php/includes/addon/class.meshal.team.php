<?php
namespace meshal;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal能力的类
################################################


class xTeam
{
    function __construct()
    {
        global $db;
        // $this->db = new \xDatabase;
        $this->members = array(); //队伍的成员容器，每个元素一个xChar对象
        $this->count = 0; //队伍成员计数
    }

    /**
     * 向队伍添加角色id（但不加载对象）
     * 
     * @param int $charId
     */
    public function addId (
        int $charId
    ) {
        if(key_exists($charId, $this->members)) {
            \fLog("Character id={$charId} is already in the team");
            return false;
        }

        $this->members[$charId] = null;
        $this->count ++;
        return true;
    }

    /**
     * 实例化members中的每个角色
     */
    public function instantiate() {
        if(!empty($this->members)) {
            foreach($this->members as $charId => $char) {
                if(is_null($this->members[$charId])) {
                    $this->members[$charId] = new \meshal\xChar;
                    $stat = $this->members[$charId]->load($charId);

                    //如果加载失败，记录错误并返回false
                    if($stat === false) {
                        unset($this->members[$charId]);
                        \fLog("Error while instantiating character id={$charId} in the team");
                    }
                }
            }
            return true;
        } else {
            \fLog("Error: property members is empty");
            return false;
        }
    }

    /**
     * 向队伍添加多个角色对象
     * 
     * @param int ...$charId
     * 角色id，可变参数，可填写多个
     */
    public function add (
        ...$charIds
    ) {
        if(empty($charIds)) {
            return false;
        }

        foreach ($charIds as $k => $charId) {
            $this->addId($charId);
        }

        $stat = $this->instantiate();

        return $stat;
    }

    /**
     * 从队伍中移除多个角色
     * 
     * @param int ...$charId
     * 角色id，可变参数，可填写多个
     */
    public function remove (
        ...$charIds
     ) {
        if(empty($charIds)) {
            return false;
        }

        foreach ($charIds as $k => $charId) {
            if(!key_exists($charId, $this->members)) {
                \fLog("Character id={$charId} doesn't in the team");
                break;
            }
    
            $this->members[$charId] = null;
            unset($this->members[$charId]);
            $this->count --;
        }
        return true;
    }

    /**
     * 重设队伍中的成员信息
     */
    public function reset() {
        $this->members = array();
        $this->count = 0;
        return true;
    }

    /**
     * 获取某项属性最高的角色的属性
     * 
     * @param string $scoreName
     * 属性名（见xChar）
     * 
     * @param string $scoreProperty = 'current'
     * 属性的某个部分
     * 
     * @return array
     * 返回一个数组，包含了角色id、属性名、属性值
     */
    public function getMemberScoreHighest (
        string $scoreName,
        string $scoreProperty = 'current'
    ) {
        $return = array(
            'id' => null,
            'scoreName' => $scoreName,
            'scoreProperty' => $scoreProperty,
            'value' => -99999999
        );

        foreach ($this->members as $charId => $char) {
            if(!isset($char->$scoreName->$scoreProperty)) {
                \fLog("Error while looking for an inexistent score property: {$scoreName}->{$scoreProperty}");
                return false;
                break;
            }

            if($char->$scoreName->$scoreProperty > $return['value']) {
                $return['value'] = $char->$scoreName->$scoreProperty;
                $return['id'] = $charId;
            }
        }

        return $return;
    }

    /**
     * 获取某项属性最低的角色的属性
     * 
     * @param string $scoreName
     * 属性名（见xChar）
     * 
     * @param string $scoreProperty = 'current'
     * 属性的某个部分
     * 
     * @return array
     * 返回一个数组，包含了角色id、属性名、属性值
     */
    public function getMemberScoreLowest (
        string $scoreName,
        string $scoreProperty = 'current'
    ) {
        $return = array(
            'id' => null,
            'scoreName' => $scoreName,
            'scoreProperty' => $scoreProperty,
            'value' => 99999999
        );
        
        foreach ($this->members as $charId => $char) {
            if(!isset($char->$scoreName->$scoreProperty)) {
                \fLog("Error while looking for an inexistent score property: {$scoreName}->{$scoreProperty}");
                return false;
                break;
            }

            if($char->$scoreName->$scoreProperty < $return['value']) {
                $return['value'] = $char->$scoreName->$scoreProperty;
                $return['id'] = $charId;
            }
        }

        return $return;
    }

    /**
     * 寻找某项属性最高的n个角色
     * 
     * @param string $scoreName
     * 属性名（见xChar）
     * 
     * @param string $scoreProperty = 'current'
     * 属性的某个部分
     * 
     * @param int $fetches
     * 获取几个角色
     * 
     * @return array
     * 以数组形式返回角色Id列表，每个成员的键值是一个角色Id
     */
    public function getMemberByScoreDesc (
        string $scoreName,
        string $scoreProperty = 'current',
        int $fetches = 1
    ) {
        $sort = array();
        foreach ($this->members as $charId => $char) {
            if(!isset($char->$scoreName->$scoreProperty)) {
                \fLog("Error while looking for an inexistent score property: {$scoreName}->{$scoreProperty}");
                return false;
                break;
            }

            $sort[$charId] = $char->$scoreName->$scoreProperty;
        }

        arsort($sort);
        return array_keys(array_slice($sort, 0, $fetches, true));
    }

    /**
     * 寻找某项属性最低的n个角色
     * 
     * @param string $scoreName
     * 属性名（见xChar）
     * 
     * @param string $scoreProperty = 'current'
     * 属性的某个部分
     * 
     * @param int $fetches
     * 获取几个角色
     * 
     * @return array
     * 以数组形式返回角色Id列表，每个成员的键值是一个角色Id
     */
    public function getMemberByScoreAsc (
        string $scoreName,
        string $scoreProperty = 'current',
        int $fetches = 1
    ) {
        $sort = array();
        foreach ($this->members as $charId => $char) {
            if(!isset($char->$scoreName->$scoreProperty)) {
                \fLog("Error while looking for an inexistent score property: {$scoreName}->{$scoreProperty}");
                return false;
                break;
            }

            $sort[$charId] = $char->$scoreName->$scoreProperty;
        }

        asort($sort);
        return array_keys(array_slice($sort, 0, $fetches, true));
    }

    /**
     * 寻找属性小于某个值的角色
     * 
     * @param int $threshold
     * 比较的值
     * 
     * @param string $scoreName
     * 属性名（见xChar）
     * 
     * @param string $scoreProperty = 'current'
     * 属性的某个部分
     * 
     * @return array
     * 以数组形式返回角色Id列表，每个成员的键值是一个角色Id
     */
    public function getMemberByScoreLessThan (
        int $threshold,
        string $scoreName,
        string $scoreProperty = 'current'
    ) {
        $return = array();

        if(empty($this->members)) {
            \fLog("There's no members in the team");
            return $return;
        }

        foreach ($this->members as $charId => $char) {
            if($char->$scoreName->$scoreProperty < $threshold) {
                $return[] = $charId;
            }
        }
        return $return;
    }

    /**
     * 寻找属性大于某个值的角色
     * 
     * @param int $threshold
     * 比较的值
     * 
     * @param string $scoreName
     * 属性名（见xChar）
     * 
     * @param string $scoreProperty = 'current'
     * 属性的某个部分
     * 
     * @return array
     * 以数组形式返回角色Id列表，每个成员的键值是一个角色Id
     */
    public function getMemberByScoreGreaterThan (
        int $threshold,
        string $scoreName,
        string $scoreProperty = 'current'
    ) {
        $return = array();

        if(empty($this->members)) {
            \fLog("There's no members in the team");
            return $return;
        }

        foreach ($this->members as $charId => $char) {
            if($char->$scoreName->$scoreProperty > $threshold) {
                $return[] = $charId;
            }
        }
        return $return;
    }

    /**
     * 随机获取n个角色
     * 
     * @param int $fetches = 1
     * 获取几个角色
     * 
     * @return array
     * 以数组形式返回角色Id列表，每个成员的键值是一个角色Id
     */
    public function getRandMember (
        int $fetches = 1
    ) {
        $keys = array_keys($this->members);
        shuffle($keys);

        return array_slice($keys, 0, $fetches, true);
    }

    /**
     * 获取不去重的队伍成员owner uid
     * 
     * @return array
     * 键值是uid
     */
    public function getOwnerList () {
        $return = array();
        foreach ($this->members as $charId => $char) {
            $return[] = $char->owner->uid;
        }
        return $return;
    }

    /**
     * 获取去重的队伍成员owner uid
     * 
     * @return array
     * 键名和键值都是uid
     */
    public function getOwnerListDistinct () {
        $return = array();
        foreach ($this->members as $charId => $char) {
            $return[$char->owner->uid] = $char->owner->uid;
        }
        return $return;
    }

    /**
     * 数组形式导出队伍成员的charId
     * 
     * @return array
     * 返回数组，每个数组成员的键值是一个charId
     */
    public function export() {
        return array_keys($this->members);
    }

    /**
     * 保存队伍中的每个角色
     */
    public function save() {
        if(!empty($this->members)) {
            foreach($this->members as $charId => $char) {
                $char->save();
            }
        }
    }

    /**
     * 检查角色是否在队伍中
     * 
     * @param int $charId
     * 要查询的角色Id
     * 
     * @return bool
     */
    public function isCharInTeam(
        int $charId
    ) {
        if(isset($this->members[$charId])) {
            return true;
        } else {
            return false;
        }
    }

}
?>