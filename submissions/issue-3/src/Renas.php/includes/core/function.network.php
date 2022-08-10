<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 提供与网络相关操作的方法
################################################

/**
 *  An example CORS-compliant method.  It will allow any GET, POST, or OPTIONS requests from any
 *  origin.
 *
 *  In a production environment, you probably want to be more restrictive, but this gives you
 *  the general idea of what is involved.  For the nitty-gritty low-down, read:
 *
 *  - https://developer.mozilla.org/en/HTTP_access_control
 *  - https://fetch.spec.whatwg.org/#http-cors-protocol
 *
 */
function fCors() {
    
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
        exit(0);
    }
}

/**
 * 远程请求API
 * 
 * @param string $method
 * 请求方法，比如：POST, PUT, GET
 * 
 * @param string $url
 * 请求URL
 * 
 * @param array $data = false
 * 传递给远程API的参数，格式为：
 * array("param" => "value") ==> index.php?param=value
 * 
 * @param string $username = null
 * 如果远程API需要验证身份，用户名
 * 
 * @param string $password = null
 * 如果远程API需要验证身份，密码
 */
function fCallAPI(
    $method,
    $url,
    $data = false,
    $username = null,
    $password = null
) {
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            break;

        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;

        default:
            if($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);


    if(!is_null($username) && !is_null($password)) {
        curl_setopt($curl, CURLOPT_USERPWD, "{$username}:{$password}");
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}


/**
 * 发送一个射后不管的HTTP请求
 * 
 * @param string $url 
 * 请求的URL（包含传递的GET查询请求的参数）
 * 
 * @param array $postData
 * POST请求的参数(以json格式发送)
 * 
 * @return bool
 */
function fAsync(
    string $url,
    array $postData = array(),
    $checkSSL = true
) {
    $cmd = "curl -L -X POST -H 'Content-Type: application/json'";
    $cmd.= " -d '" . json_encode($postData) . "' '" . $url . "'";
  
    if (!$checkSSL){
      $cmd.= "'  --insecure"; // this can speed things up, though it's not secure
    }
    $cmd .= " > /dev/null 2>&1 &"; // don't wait for response

    \fLog("Firing async request: ".$cmd);
    exec($cmd, $output, $exit);
    return $exit == 0;
}
?>