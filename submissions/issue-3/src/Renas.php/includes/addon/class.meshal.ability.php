<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal能力的类
################################################


class xAbility
{
    /**
     * 从数据库中获取能力的资料
     * 
     * @param string $abilityName
     * 查询的能力名称
     * 
     * @return mixed
     * 如果没有查到，返回false；
     * 如果查到了能力，以数组返回包含该能力的数据
     */
    public static function getData(
        string $abilityName
    ) {
        // $db = new \xDatabase;
        global $db;
        $arr = $db->getArr(
            'abilities',
            array(
                "`name` = '{$abilityName}'"
            ),
            NULL,
            1
        );

        if($arr === false) {
            \fLog("{$abilityName} doesn't exist in library");
            return false;
        }

        $return = array(
            'fullname' => "meshal.ability.{$abilityName}",
            'name' => $abilityName,
            'level' => $arr[0]['level'],
            'attr' => $arr[0]['attr'],
            'description' => $arr[0]['description'],
            'data' => json_decode($arr[0]['data'], true),
            'grade' => $arr[0]['grade']
        );
        return $return;
    }
}
?>