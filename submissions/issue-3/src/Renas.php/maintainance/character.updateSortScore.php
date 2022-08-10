<?php
################################################
# 初始化开始
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

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$user->challengeRole('admin');


$chars = $db->getArr(
    'characters',
    array(),
    '`id`'
);

foreach ($chars as $k => $char) {
    $score = \meshal\char\updateSort($char['id']);

    $charName = fDecode($char['name']);
    fEcho("Char[{$char['id']}: {$charName}] sortScore was set to {$score['score']['total']}");
    fLog("Char[{$char['id']}: {$charName}] sortScore was set to {$score['score']['total']}");
    fPrint($score);
    flog(fDump($score));
    fEcho('<hr>');
    $db->clearStack();
}
fDie();
?>