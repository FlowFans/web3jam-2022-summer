<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供随机名字生成的类
################################################

class xName
{
    function __construct()
    {
        global $db;
        $this->db = $db;
        // $this->db = new \xDatabase;

        //名字组件注册表，如果试图生成没有被规定的组件，那么会从全量中随机
        $this->registry = array(
            'given' => 'given',
            'sur' => 'sur'
        );
    }

    /**
     * 根据给定的名字组件，生成名字
     * @param string $lang
     * 选择什么语言的名字
     * 
     * @param string ...$parts
     * 要生成哪些部分构成的名字，如果注册表中没有指定的部分类型，则从全量中随机
     * 
     * @return string
     * 返回一个拼装好的随机名字
     */
    public function gen(
        $lang,
        ...$parts
    ) {
        $arr = array();
        foreach ($parts as $k => $part) {
            $arr[] = $this->fetch($lang, $this->registry[$part]);
        }

        return implode(' ', $arr);
    }

    /**
     * 从数据库中随机取名字
     * @param string $lang
     * 选择什么语言的名字
     * 
     * @param string $type
     * 要生成什么部分的名字，如果注册表中没有指定的部分类型，则从全量中随机
     * 
     * @return string
     * 返回一个名字
     */
    public function fetch(
        string $lang,
        string $type = null
    ) {
        if(
            !$this->registry[$type]
            || $type === null
        ) {
            $query = $this->db->getArr(
                'names',
                array(
                    "`lang` = '{$lang}'"
                ),
                null,
                1,
                null,
                null,
                'RAND'
            );
        } else {
            $query = $this->db->getArr(
                'names',
                array(
                    "`type` = '{$type}'",
                    "`lang` = '{$lang}'"
                ),
                null,
                1,
                null,
                null,
                'RAND'
            );
        }

        return $query[0]['name'];
    }
}

?>