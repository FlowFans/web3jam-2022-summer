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

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadTpl('profile/wallet/body.frame.html');

$comp = array();
foreach ($GLOBALS['deploy']['network'] as $networkName => $networkCfg) { //遍历所有配置
    $tmp = array();
    $tmp['--networkName'] = $networkName;
    $tmp['--walletAddress'] = $user->wallet[$networkName]->address === false ? '{?common.none?}' : $user->wallet[$networkName]->address;
    $tmp['--hideBrowser'] = $user->wallet[$networkName]->address === false ? 'hidden' : '';
    $tmp['--manageUrl'] = $networkCfg['url']['manageWallet'];
    $tmp['--browserUrl'] = $networkCfg['browser']['account'];
    $comp[] = $tmp;
}
$html->set(
    '$walletList',
    $html->duplicate(
        'profile/wallet/dup.row.html',
        $comp
    )
);

$html->output();
\fDie();

?>