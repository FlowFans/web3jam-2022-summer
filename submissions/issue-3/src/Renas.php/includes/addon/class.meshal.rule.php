<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal能力的类
################################################


class xRule
{
    /**
     * 从数据库中获取规则的资料
     * 
     * @param string $term
     * 查询的规则关键词类型
     * 
     * @param string $lang
     * 查询的语言代码，如果为空则使用全局设置
     * 
     * @return mixed
     * 如果没有查到，返回false；
     * 如果查到了规则，以数组返回包含该规则的数据
     */
    public static function getData (
        string $term,
        string $lang = null
    ) {
        //语言设置
        if(is_null($lang)) {
            $langCode = $GLOBALS['deploy']['lang'];
        } else {
            $langCode = $lang;
        }

        // $db = new \xDatabase;
        global $db;
        $arr = $db->getArr(
            'languages',
            array(
                "`lang` = '{$langCode}'",
                "`name` = '{$term}'"
            ),
            NULL,
            1
        );
        if($arr === false) {
            \fLog("{$langCode}.{$term} doesn't exist in rule library");
            return false;
        }

        $return = array(
            'fullname' => "meshal.rule.{$term}",
            'name' => $term,
            'content' => \fDecode($arr[0]['content'], true)
        );
        return $return;
    }
}
?>