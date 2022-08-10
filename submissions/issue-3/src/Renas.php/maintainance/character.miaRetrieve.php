<?php
################################################
# 初始化开始
# 这个脚本用于处理冒险状态错误的角色
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################

$db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$user->challengeRole('admin');

$arr = $db->getArr( //获取在冒险中的角色
    'characters',
    array(
        "`stat` = 'adventure'"
    ),
    '`id`'
);

$outputSuccess = array();
$outputError = array();
if($arr !== false) {
    foreach ($arr as $k => $char) {
        $check = $db->getCount( //统计有没有在没结束的冒险中
            'adventure_chars',
            array(
                "`charId` = '{$char['id']}'",
                "`sealed` = '0'"
            )
        );

        if($check == 0) { //如果没有未结束的冒险，那么将这个角色设为空闲
            $result = $db->update(
                'characters',
                array(
                    'stat' => null
                ),
                array(
                    "`id` = '{$char['id']}'"
                ),
                1
            );

            if($result == 0) {
                $outputError[] = $char['id'].PHP_EOL;
            } else {
                $outputSuccess[] = $char['id'].PHP_EOL;
            }
        }
    }
}

fPrint($outputError);
fPrint($outputSuccess);

\fDie();

?>