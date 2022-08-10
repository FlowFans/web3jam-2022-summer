<?php
ignore_user_abort(true);
set_time_limit(0);
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

# 执行这个脚本时不启动automator
define('_NOTAUTOMATOR', true);

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################

if(!$_GET['uid']) {
    \fLog("Error: no uid given");
    \fDie();
}

$discordId = \user\xDiscord::getDiscordId($_GET['uid']);
if($discordId === false) {
    \fLog("Error: user({$_GET['uid']}) doesn't have a discordId");
    \fDie();
}

$response = json_decode(
    \fCallAPI(
        'GET',
        $GLOBALS['deploy']['external']['emeraldId'],
        array(
            'discordId' => $discordId
        )
    ),
    true
);

if($response['success'] === false) {
    \fLog("Error: API responded a failure");
    \fDie();
}

if(is_null($response['res'])) {
    \fLog("Warning: user({$_GET['uid']}) who has discordId({$discordId}) hasn't bond emeraldId with wallet address");
    \fDie();
}

$check = \user\wallet\xFlow::updateMapping(
    $_GET['uid'],
    $response['res']
);

if($check > 2) {
    \fLog("Error: failed on mapping user({$_GET['uid']})'s address({$response['res']})");
}

\fDie();
?>