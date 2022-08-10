<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供加密/解密用途的类
################################################

class xCypher 
{
    /**
     * 用mcrypt对给定的内容用秘钥进行加密，返回加密后的内容
     * 这个方法与deMcrypt方法是互补成对的。
     * 
     * @param string $key
     * 用于加密的秘钥
     * 
     * @param string $data
     * 接受加密的内容
     * 
     * @param string $salt
     * 用于复杂化秘钥的盐值，默认为NULL时，取$GLOBALS['deploy']['securityKey']
     * 
     * @return string
     * 返回加密后的内容文本
     */
    function encByMycrypt($key, $data, $salt = NULL){
        $td = mcrypt_module_open("des", "", "ecb", "");//使用MCRYPT_DES算法,ecb模式
        $size = mcrypt_enc_get_iv_size($td);       //设置初始向量的大小
        $iv = mcrypt_create_iv($size,MCRYPT_RAND); //创建初始向量
    
        $key_size = mcrypt_enc_get_key_size($td);       //返回所支持的最大的密钥长度（以字节计算）

        if($salt === NULL) {
            $salt = $GLOBALS['deploy']['securityKey'];
        }
        $subkey = substr(md5(md5($key).$salt), 0,$key_size);//对key复杂处理，并设置长度
    
        mcrypt_generic_init($td, $subkey, $iv);
        $endata = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $endata;
    }

    /**
     * 用mcrypt对给定的加密内容用同样的秘钥解密，返回解密后的内容
     * 这个方法与enMcrypt方法是成对的
     * 
     * @param string $key
     * 用于解密的秘钥
     * 
     * @param string $endata
     * 接受解密的内容
     * 
     * @param string $salt
     * 用于复杂化秘钥的盐值，默认为NULL时，取$GLOBALS['deploy']['securityKey']
     * 
     * @return string
     * 返回解密后的内容文本
     */
    function decByMcrypt($key,$endata, $salt = NULL){
        $td = mcrypt_module_open("des", "", "ecb", "");//使用MCRYPT_DES算法,ecb模式
        $size = mcrypt_enc_get_iv_size($td);       //设置初始向量的大小
        $iv = mcrypt_create_iv($size,MCRYPT_RAND); //创建初始向量
        $key_size = mcrypt_enc_get_key_size($td);       //返回所支持的最大的密钥长度（以字节计算）
        
        if($salt === NULL) {
            $salt = $GLOBALS['deploy']['securityKey'];
        }
        $subkey = substr(md5(md5($key).$salt), 0,$key_size);//对key复杂处理，并设置长度

        mcrypt_generic_init($td, $subkey, $iv);
        $data = rtrim(mdecrypt_generic($td, $endata)).'\n';
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $data;
    }

    /**
     * 列出当前环境中mcrypt支持的加密算法和模式
     * 
     * @return array
     * 返回一个二维数组，元素'algorithms'包含mcrypt支持的加密算法；元素'modes'包含mycrypt支持的加密模式
     */
    function checkMcryptSupport()
    {
        return array(
            'algorithms' => mcrypt_list_algorithms(),
            'modes' => mcrypt_list_modes()
        );
    }

    /**
     * 使用openssl生成一对秘钥并返回
     * 
     * @param string $algorithm
     * 加密算法
     * 
     * @param int $length
     * 字节数 512 1024 2048 4096 等，此处长度与加密的字符串长度有关系
     * 
     * @param $type
     * 加密类型
     * 
     * @return array
     * private_key => string //私钥
     * public_key => string //公钥
     */
    public static function genCertOpenSSL (
        $algorithm = 'sha512',
        $length = 4096,
        $type = OPENSSL_KEYTYPE_RSA
    ) {
        $config = array(
            'digest_alg' => $algorithm,
            'private_key_bits' => $length,
            'private_key_type' => $type
        );

        $res = openssl_pkey_new($config);
        if ( $res == false ) return false;
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];
        
        if(!is_dir(_ROOT.DIR_CERT)) {
            mkdir(_ROOT.DIR_CERT, 0700, true);
        }

        chmod(_ROOT.DIR_CERT, 0700);

        if(file_exists(_ROOT.DIR_CERT."cert_public.key")) {
            chmod(_ROOT.DIR_CERT."cert_public.key", 0777);
            unlink(_ROOT.DIR_CERT."cert_public.key");
        }
        file_put_contents(_ROOT.DIR_CERT."cert_public.key", $public_key);
        chmod(_ROOT.DIR_CERT."cert_public.key", 0644);
        
        if(file_exists(_ROOT.DIR_CERT."cert_private.pem")) {
            chmod(_ROOT.DIR_CERT."cert_private.pem", 0777);
            unlink(_ROOT.DIR_CERT."cert_private.pem");
        }
        file_put_contents(_ROOT.DIR_CERT."cert_private.pem", $private_key);
        chmod(_ROOT.DIR_CERT."cert_private.pem", 0600);

        // opnessl_pkey_free($res);
    }

    /**
     * 用OpenSSL的公钥加密信息
     * 
     * @param string $string
     * 要加密的的文本
     * 
     * @return string
     * 返回加密后的文本
     */
    public static function encryptByPub (
        $string
    ) {
        $return = null;
        $sslPublic = file_get_contents(_ROOT.DIR_CERT."cert_public.key");
        $keyPublic = openssl_pkey_get_public($sslPublic);//这个函数可用来判断公钥是否是可用的

        if( !$keyPublic) {
            \fLog("Error: certs error");
            return $return;
        }
        
        openssl_public_encrypt($string, $data, $keyPublic);//公钥加密
        fPrint($data);
        $return = base64_encode($data);
        return $return;
    }

    /**
     * 用OpenSSL的私钥加密信息
     * 
     * @param string $string
     * 要加密的的文本
     * 
     * @return string
     * 返回加密后的文本
     */
    public static function encryptByPriv (
        $string
    ) {
        $return = null;
        $sslPrivate = file_get_contents(_ROOT.DIR_CERT."cert_private.pem");
        $keyPrivate = openssl_pkey_get_private($sslPrivate);//这个函数可用来判断私钥是否是可用的

        if( !$keyPrivate) {
            \fLog("Error: certs error");
            return $return;
        }
        
        openssl_private_encrypt($string, $data, $keyPrivate);//私钥加密
        $return = base64_encode($data);
        return $return;
    }

    /**
     * 用OpenSSL的私钥解密信息
     * 
     * @param string $string
     * 要解密的的文本
     * 
     * @return string
     * 返回解密后的文本
     */
    public static function decryptByPriv (
        $string
    ) {
        $return = null;
        $sslPrivate = file_get_contents(_ROOT.DIR_CERT."cert_private.pem");
        $keyPrivate = openssl_pkey_get_private($sslPrivate);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id

        if( !$keyPrivate) {
            \fLog("Error: certs error");
            return $return;
        }
        
        openssl_private_decrypt(base64_decode($string),$return,$keyPrivate);//私钥解密
        return $return;
    }

    /**
     * 用OpenSSL的公钥解密信息
     * 
     * @param string $string
     * 要解密的的文本
     * 
     * @return string
     * 返回解密后的文本
     */
    public static function decryptByPub (
        $string
    ) {
        $return = null;
        $sslPublic = file_get_contents(_ROOT.DIR_CERT."cert_public.key");
        $keyPublic = openssl_pkey_get_public($sslPublic);//这个函数可用来判断公钥是否是可用的，可用返回资源id Resource id

        if( !$keyPublic) {
            \fLog("Error: certs error");
            return $return;
        }
        
        openssl_public_decrypt(base64_decode($string),$return,$keyPublic);//公钥解密
        return $return;
    }
}

?>