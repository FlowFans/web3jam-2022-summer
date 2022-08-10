<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供加密/解密用途的类
################################################

/**
 * 0~99 xAPI返回码
 *  0：成功
 *  1：appId错误，不是有效的请求来源
 *  2：该授权已被撤销
 *  3：$_SERVER['REMOTE_ADDR']和记录不一致
 *  4：缺少参数
 *  5：token错误
 * 
 * 100开始：各接口分别定义
 */

class xAPI
{
    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * __call magic method.
     */
    public function __call($name, $arguments)
    {
        $this->respond(404, false, array(), array('HTTP/1.1 404 Not Found'));
    }
 
    /**
     * Get URI elements.
     * 
     * @return array
     */
    protected static function getUriSegments()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode( '/', $uri );
 
        \fLog(\fDump($uri));
        return $uri;
    }
 
    /**
     * Get querystring params.
     * 
     * @return array
     */
    public static function listenGet()
    {
        \fLog(\fDump($_GET));
        parse_str($_SERVER['QUERY_STRING'], $return);
        
        \fLog(\fDump($return));
        return $return;
    }

    /**
     * 监听POST请求
     * 
     * @return array
     * 以数组形式返回请求的所有POST参数
     */
    public static function listenPost(...$params) {
        \fLog(\fDump($_POST));
        //检查参数要求并组装返回的参数
        $return = array();
        if(!empty($params)) {
            foreach ($params as $k => $p) {
                if(!$_POST[$p]) self::respond(4, false); //要求的参数不存在，直接中断并返回错误码
                $return[$p] = $_POST[$p]; //组装返回的参数
            }
        }
        \fLog(\fDump($return));
        return $return;
    }

    /**
     * 监听Raw data
     * 
     * @return array
     * 以数组形式返回raw data
     */
    public static function listenRaw() {
        $raw = file_get_contents("php://input");
        \fLog($raw);

        $return = json_decode($raw, true);
        \fLog(\fDump($return));
        
        return $return;
    }
 
    /**
     * Send API output.
     *
     * @param mixed  $data
     * @param string $httpHeader
     */
    public static function respond(
        int $code,
        bool $success,
        $data = array(),
        $httpHeaders = array('Content-Type: application/json', 'HTTP/1.1 200 OK')
    ) {
        header_remove('Set-Cookie');
 
        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }
 
        $return = json_encode(array(
            'success' => $success,
            'code' => $code,
            'data' => $data
        ));
        \fLog(\fDump($return));
        \fDie($return);
    }

    /**
     * 检查是否是一个合法来源的请求，如果不合法则直接中断
     */
    public static function checkAuth() {
        global $db;

        $varGet = self::listenGet();
        $appId = $varGet['appId'];
        $token = $varGet['token'];

        $query = $db->getArr(
            'api_auth',
            array(
                "`appId` = '{$appId}'"
            ),
            null,
            1
        );
        
        switch (TRUE) {
            case $query === false: //没有查找到对应的appId，视作非法请求
                self::respond(1, false);
                break;

            case $query[0]['token'] != $token: //token不一致
                self::respond(5, false);
                break;
            
            case $query[0]['valid'] != 1: //该授权已被撤销
                self::respond(2, false);
                break;

            case (
                $query[0]['remoteAddressRestricted'] == 1
                && $query[0]['remoteAddress'] != \fEncode($_SERVER['REMOTE_ADDR'])
            ): //请求来源和注册记录不一致
                self::respond(3, false);
                break;
            
            default:
                break;
        }
    }

    /**
     * 生成一个新的API授权记录
     * 
     * @return array|bool
     * 如果生成失败返回false
     * 生成成功返回数组 array(
     *  appId => appId,
     *  remoteAddress => App来源的地址
     *  token => token秘钥
     * )
     */
    public static function genAuth(
        $remoteAddr = null
    ) {
        global $db;

        $query = $db->insert( //插入一条空数据
            'api_auth',
            array(
                'remoteAddress' => is_null($remoteAddr) ? null : \fEncode($remoteAddr),
                'remoteAddressRestricted' => is_null($remoteAddr) ? 0 : 1,
                'token' => '',
                'valid' => 0
            )
        );

        if($query === false) {
            \fLog("Error: failed on inserting new auth record");
            return false;
        }

        //计算token
        $token = md5(sha1(
            time()
            .\fEncode(is_null($remoteAddr) ? \fGenGuid() : $remoteAddr)
            .$GLOBALS['deploy']['securityKey']
            .'generatesToken'
            .$query
        ));

        $db->update( //将token更新到记录中
            'api_auth',
            array(
                'token' => $token,
                'valid' => 1
            ),
            array(
                "`appId` = '{$query}'"
            ),
            1
        );

        return array(
            'appId' => $query,
            'remoteAddress' => $remoteAddr,
            'remoteAddressRestricted' => is_null($remoteAddr) ? 0 : 1,
            'token' => $token
        );
    }

    /**
     * 撤销一个appId的授权
     * 
     * @param int $appId
     * 
     * @return bool
     */
    public static function revokeAuth(
        int $appId
    ) {
        global $db;

        $check = $db->update(
            'api_auth',
            array(
                'valid' => 0
            ),
            array(
                "`appId` = '{$appId}'"
            ),
            1
        );

        if($check == 0) {
            \fLog("Error: failed on revoking API auth({$appId})");
            return false;
        }
        return true;
    }
}
?>