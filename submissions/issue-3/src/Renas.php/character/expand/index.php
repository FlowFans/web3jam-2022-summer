<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################

// $db = new xDatabase;
$html = new \xHtml;
$user = new \xUser;

//初始化slot
$query = $db->getArr(
    'character_slot',
    array(
        "`uid` = {$user->uid}"
    ),
    null,
    1
);
if($query === false) {
    //没有记录，创建一条记录
    $db->insert(
        'character_slot',
        array(
            'uid' => $user->uid,
            'slot' => 0
        )
    );
    $rawSlots = $GLOBALS['meshal']['character']['initialSlot'];
    $sumSlots = $rawSlots + $user->efx->modifier['survivorSlots'];
} else {
    $rawSlots = $query[0]['slot'] + $GLOBALS['meshal']['character']['initialSlot'];
    $sumSlots = $rawSlots + $user->efx->modifier['survivorSlots'];
}

//检查请求的有效性
if($_GET['confirm'] !== md5($user->uid.'expand'.$GLOBALS['deploy']['securityKey'].$rawSlots)) {
    header("Location: "._ROOT.\fDecode(\fGet('_back', '')));
    \fDie();
}

//检查用户是否有足够的$cp
if(
    bccomp($user->cp, $rawSlots * $GLOBALS['cp']['character']['expand']) == -1
) {
    $html->redirectBack(
        'redirect.message.character.expandInsufficientCP'
    );
    \fDie();
}

//扣除用户的cp
// $user->cp -= $rawSlots * $GLOBALS['cp']['character']['expand'];
$user->cp = \fSub(
    $user->cp, 
    $rawSlots * $GLOBALS['cp']['character']['expand'],
    $GLOBALS['cp']['decimal']
);
$user->save();
$user->fetch();

//更新数据
$db->update(
    'character_slot',
    array(
        'slot' => '`slot` + 1'
    ),
    array(
        "`uid` = '{$user->uid}'"
    ),
    1,
    false
);

$html->set('$slots', $sumSlots + 1);

$html->redirectBack(
    'redirect.message.character.expandSuccess'
);
\fDie();

?>