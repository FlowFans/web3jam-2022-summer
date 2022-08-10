<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供一些通用的方法
################################################

/**
 * 获取$_SERVER['QUERY_STRING']中的数据
 * @param bool $explode
 * 如果为false，直接返回整个字符串
 * 如果为true，则将&作为分隔符，返回一个数组
 * 默认为false
 * 
 * @return string|array 
 * 根据$explode参数返回字符串或数组
 */
function fQuery(
	bool $explode = false
) {
	if($explode === false) {
		return $_SERVER['QUERY_STRING'];
	} else {
		return explode('&', $_SERVER['QUERY_STRING']);
	}
}

/**
 * a shortcut function for fetching a var from $_GET, if there's no defined value, returns as the given default value.
 * @param  string  $varName 
 * name of variable in $_GET
 * 
 * @param  mixed  $default 
 * default value if there's no specific variable in $_GET
 * 
 * @param  boolean $set  
 * trigger that allows setting the variable in $_GET as the given default value if it not exists.
 * 
 * @return mixed
 */
function fGet(
	string $varName, 
	$default = NULL, 
	$set = FALSE
) {
	if(!isset($_GET[$varName])) {
		$return = $default;
		if($set === TRUE) {
			$_GET[$varName] = $default;
		}
	}
	elseif(is_array($_GET[$varName])) {
		$return = $_GET[$varName];
	}
	else {
		$return = htmlentities($_GET[$varName], ENT_QUOTES, $GLOBALS['deploy']['charset']);
	}
	return $return;
}


/**
 * a shortcut function for fetching a var from $_POST, if there's no defined value, returns as the given default value.
 * @param  string  $varName 
 * name of variable in $_POST
 * 
 * @param  mixed  $default 
 * default value if there's no specific variable in $_POST
 * 
 * @param  boolean $set  
 * trigger that allows setting the variable in $_POST as the given default value if it not exists.
 * 
 * @return mixed
 */
function fPost(
	string $varName, 
	$default = NULL, 
	$set = FALSE
) {
	if(!isset($_POST[$varName])) {
		$return = $default;
		if($set === TRUE) {
			$_POST[$varName] = $default;
		}
	}
	elseif(is_array($_POST[$varName])) {
		$return = $_POST[$varName];
	}
	else {
		$return = htmlentities($_POST[$varName], ENT_QUOTES, $GLOBALS['deploy']['charset']);
	}
	return $return;
}

function fSession(
	string $varName, 
	$default = NULL, 
	$set = FALSE
) {
	if(!isset($_SESSION[$varName])) {
		$return = $default;
		if($set === TRUE) {
			$_SESSION[$varName] = $default;
		}
	}
	elseif(is_array($_SESSION[$varName])) {
		$return = $_SESSION[$varName];
	}
	else {
		$return = htmlentities($_SESSION[$varName], ENT_QUOTES, $GLOBALS['deploy']['charset']);
	}
	return $return;
}

/**
 * 对给定文本进行预处理，然后将其打印出来
 */
function fEcho(
    $string
) {
    #替代特殊的格式符
    $output = str_replace(
        array( //要替换的内容
            "\\n"
        ), 
        array( //替换为
            "\n"
        ), 
        $string
    );

    echo($output);
}

/**
 * 用在一个方法/函数中，查看调用这个方法/函数的上一级方法/函数名
 * 
 * @return string
 */
function fLastCallFunc () {
	return debug_backtrace()[2]['function'];
}
?>