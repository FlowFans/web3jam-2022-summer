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

$authorizeURL = $GLOBALS['social']['discord']['authorizeUrl'];
$tokenURL = $GLOBALS['social']['discord']['tokenUrl'];
$apiURLBase = $GLOBALS['social']['discord']['apiUrlBase'];
$revokeURL = $GLOBALS['social']['discord']['revokeUrl'];
$requestUri = $GLOBALS['deploy']['http'].$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

if(is_null(\fGet('code'))) {
    // 如果是外部闯入，那么从安全角度出发，都要清除Session中原本的discord token和用户信息
    unset($_SESSION['discordToken']);
    unset($_SESSION['discordUser']);


    // Start the login process by sending the user to Discord's authorization page
    $params = array(
        'client_id' => $GLOBALS['social']['discord']['clientId'],
        'redirect_uri' => $requestUri,
        'response_type' => 'code',
        'scope' => 'identify guilds'
    );
    
    // Redirect the user to Discord's authorization page
    \fSaveLogToFile();
    header("Location: {$GLOBALS['social']['discord']['authorizeUrl']}" . '?' . http_build_query($params));
    \fDie();
} else {
    // When Discord redirects the user back here, there will be a "code" and "state" parameter in the query string
    // Exchange the auth code for a token
    $token = localApiRequest($tokenURL, array(
        "grant_type" => "authorization_code",
        'client_id' => $GLOBALS['social']['discord']['clientId'],
        'client_secret' => $GLOBALS['social']['discord']['clientSecret'],
        'redirect_uri' => $requestUri,
        'code' => \fGet('code')
    ));
    $logout_token = $token->access_token;
    $_SESSION['discordToken'] = $token->access_token;

    $user = localApiRequest($apiURLBase);
    $_SESSION['discordUser'] = $user;

    $html->set('$userName', $user->username);
    $html->set('$userDiscriminator', $user->discriminator);
    $html->set('$redirectPageName', 'pageTitle.home');
    $html->redirect(
        _ROOT,
        'pageTitle.home',
        'redirect.message.discord.loggedIn'
    );
    \fDie();
}

function localApiRequest(
    $url, 
    $post=array(), 
    $headers=array()
) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  
    if($GLOBALS['deploy']['http'] == 'http://') {
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
  
    $response = curl_exec($ch);
  
  
    if(!empty($post)) {
        curl_setopt(
          $ch, 
          CURLOPT_POSTFIELDS, 
          http_build_query($post)
        );
    }
  
    $headers[] = 'Accept: application/json';
  
    if(\fSession('discordToken'))
      $headers[] = 'Authorization: Bearer ' . \fSession('discordToken');
  
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  
    $response = curl_exec($ch);
    return json_decode($response);
  }
?>