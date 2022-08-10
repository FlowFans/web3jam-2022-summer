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
$html = new \xHtml;

// This should logout you
localLogout($revokeURL, array(
    'token' => array_key_exists('discordToken', $_SESSION) ? $_SESSION['discordToken'] : null,
    'token_type_hint' => 'access_token',
    'client_id' => $GLOBALS['social']['discord']['clientId'],
    'client_secret' => $GLOBALS['social']['discord']['clientSecret'],
));

session_unset();

// $html->set('$redirectPageName', 'pageTitle.home');

$html->redirect(
    _ROOT,
    'pageTitle.home',
    'redirect.message.discord.loggedOut'
);
\fDie();

function localLogout($url, $data=array()) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
        CURLOPT_POSTFIELDS => http_build_query($data),
    ));
    $response = curl_exec($ch);
    \fLog('Discord access_token revoked: '.\fDump(json_decode($response, true)));
    return json_decode($response);
}
?>