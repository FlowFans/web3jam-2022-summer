<?php
namespace user;

use xDatabase;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供用户物资的类
################################################

/**
 * 这是一个用户设施库存的类
 * 
 * 常用方法
 * 
 * 设施升级
 * $obj->upgrade(
 *  string $facilityName,
 *  int $level
 * )
 * 
 * 设施降级
 * $obj->demolish(
 *  string $facilityName,
 *  int $level
 * )
 * 
 * 查询指定设施的等级
 * $obj->getLevel(
 *  string $facilityName
 * )
 * 
 * 检查用户是否有比指定等级更高的设施
 * $obj->checkLevel(
 *  string $facilityName,
 *  int $level
 * )
 */
class xFacility {
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
     * 获取用户拥有某个设施的等级
     * 
     * @param string $facilityName
     * 物品的名称
     * 
     * @return int
     * 返回设施的等级，如果没有查询到则返回0
     */
    public function getLevel(
        string $facilityName
    ) {
        $query = $this->db->getArr(
            'user_facilities',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '{$facilityName}'"
            ),
            null,
            1
        );

        if($query === false) {
            return 0;
        } else {
            return $query[0]['level'];
        }
    }

    /**
     * 检查用户是否拥有指定超过指定等级的设施
     * 
     * @param string $facilityName
     * 设施名称
     * 
     * @param int $level
     * 检查等级
     * 
     * @return bool
     * 如果等级≥要求的数字，返回true，不足返回false
     */
    public function checkLevel(
        string $facilityName,
        int $level
    ) {
        if($this->getLevel($facilityName) >= $level) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 增加设施或提升设施等级
     * 
     * @param string $facilityName
     * 增加物品的名字
     * 
     * @param int $level
     * 增加设施的等级
     * 
     * @return int
     * 返回错误码
     * - 0：成功
     * - 1：设施在数据库中不存在
     * - 2：更新等级时出错
     */
    public function upgrade(
        string $facilityName,
        int $level = 1
    ) {
        //取设施数据
        $currentLevel = $this->getLevel($facilityName);
        $targetLevel = $currentLevel + $level;
        $facility = \meshal\xFacility::getData($facilityName, $targetLevel);

        if($facility === false) { //该等级的设施不存在，报错
            \fLog("Facility {$facilityName}({$targetLevel}) doesn't exist in library");
            return 1;
        }

        //取用户现有的设施记录
        $query = $this->db->getArr(
            'user_facilities',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '{$facilityName}'"
            ),
            null,
            1
        );

        if($query === false) { //没有该设施的信息，插入一条新的
            $check = $this->db->insert(
                'user_facilities',
                array(
                    'uid' => $this->parent->uid,
                    'name' => $facilityName,
                    'level' => $targetLevel,
                    'lastUpdate' => time()
                )
            );
            \fLog("Facility {$facilityName}({$targetLevel}) is added to user id={$this->parent->uid}'s campsite");
        } else { //有数据，更改等级
            $check = $this->db->update(
                'user_facilities',
                array(
                    'level' => $targetLevel,
                    'lastUpdate' => time()
                ),
                array(
                    "`uid` = '{$this->parent->uid}'",
                    "`name` = '{$facilityName}'"
                ),
                1
            );
            \fLog("User id={$this->parent->uid}'s facility {$facilityName} is upgraded to {$targetLevel}");
        }

        if($check === false) {
            \fLog("Error while updating facility level");
            return 2;
        }

        //将原来设施的user_efx数据删除
        $this->db->delete(
            'user_efx',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`type` = 'facility'",
                "`source` = '{$facilityName}.{$currentLevel}'"
            )
        );

        //向user_efx添加新等级设施的效果
        if(
            $facility !== false 
            && !empty($facility['data']['efx'])
        ) {
            foreach($facility['data']['efx'] as $k => $efx) {
                $this->db->insert(
                    'user_efx',
                    array(
                        'uid' => $this->parent->uid,
                        'type' => 'facility',
                        'source' => "{$facilityName}.{$facility['level']}",
                        'data' => json_encode($efx),
                        'expire' => null
                    )
                );
            }
        }

        return 0;
    }

    /**
     * 设施降级
     * 
     * @param string $facilityName
     * 降级设施的名字
     * 
     * @param int $level
     * 降低的等级
     * 
     * @return int
     * 返回的错误码
     * - 0：操作成功
     * - 1：用户没有该设施
     * - 2：更新设施等级失败
     */
    public function demolish(
        string $facilityName,
        int $level
    ) {
        //取设施数据
        $currentLevel = $this->getLevel($facilityName);
        if($currentLevel == 0) return 1;

        $targetLevel = $currentLevel - $level < 0 ? $targetLevel = 0 : $currentLevel - $level;

        //取用户现有的设施等级
        $query = $this->db->getArr(
            'user_facilities',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '{$facilityName}'"
            ),
            null,
            1
        );

        //检查是否有设施记录
        if($query === false) {
            \fLog("There's no {$facilityName} in user's campsite");
            return 1;
        }

        $check = $this->db->update(
            'user_facilities',
            array(
                'level' => $targetLevel,
                'lastUpdate' => time()
            ),
            array(
                "`uid` = '{$this->parent->uid}'",
                "`name` = '$facilityName'"
            ),
            1
        );

        if($check === false) {
            \fLog("Error while updating item level");
            return 2;
        }

        //将原来设施的user_efx数据删除
        $this->db->delete(
            'user_efx',
            array(
                "`uid` = '{$this->parent->uid}'",
                "`type` = 'facility'",
                "`source` = '{$facilityName}.{$currentLevel}'"
            )
        );

        //向user_efx添加新等级设施的效果
        $facility = \meshal\xFacility::getData($facilityName, $targetLevel);
        if(
            $facility !== false 
            && !empty($facility['data']['efx'])
        ) {
            foreach($facility['data']['efx'] as $k => $efx) {
                $this->db->insert(
                    'user_efx',
                    array(
                        'uid' => $this->parent->uid,
                        'type' => 'facility',
                        'source' => "{$facilityName}.{$facility['level']}",
                        'data' => json_encode($efx),
                        'expire' => null
                    )
                );
            }
        }

        \fLog("User id={$this->parent->uid}'s {$facilityName} is demolished to level {$targetLevel}");

        return 0;
    }
}