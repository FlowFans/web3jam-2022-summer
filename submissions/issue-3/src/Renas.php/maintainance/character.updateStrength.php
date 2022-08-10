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
$char = new \meshal\xChar;

$user->challengeRole('admin');


$chars = $db->getArr(
    'characters',
    array()
);

foreach ($chars as $k => $ch) {

    $char->load($ch['id']);

    $strength = array();

    $strength['m'] = array_sum(range(0, $char->m->base)) * $GLOBALS['meshal']['character']['strength']['attr'];
    $strength['a'] = array_sum(range(0, $char->a->base)) * $GLOBALS['meshal']['character']['strength']['attr'];
    $strength['s'] = array_sum(range(0, $char->s->base)) * $GLOBALS['meshal']['character']['strength']['attr'];

    if($char->ip->base == 0) {
        $strength['t'] = array_sum(range(0, $char->t->base)) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['t'] = 0;
    }

    if($char->ie->base == 0) {
        $strength['e'] = array_sum(range(0, $char->e->base)) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['e'] = 0;
    }

    if($char->io->base == 0) {
        $strength['r'] = array_sum(range(0, $char->r->base)) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['r'] = 0;
    }

    $strength['pr'] = $char->pr->base * $GLOBALS['meshal']['character']['strength']['pr'];
    $strength['ms'] = $char->ms->base * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['ap'] = array_sum(range(0, $char->ap->base)) * $GLOBALS['meshal']['character']['strength']['ap'];

    $char->strength->set('base', array_sum($strength));

    $char->save();

    

    fEcho("Char[{$char->id}: {$char->name}] strength was set to {$char->strength->st}");
    fLog("Char[{$char->id}: {$char->name}] strength was set to {$char->strength->st}");
    fPrint($strength);
    fLog(fDump($strength));
    fEcho('<hr>');
}

fDie();
?>