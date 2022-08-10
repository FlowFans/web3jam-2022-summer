<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供与cache相关的方法
################################################

/**
 * 将变量存入缓存$GLOBALS['cache']['var']中，如果缓存中已经存在，那么覆盖旧的值
 * 
 * @param  string|array $varInput
 * 这个参数支持字符串或数组，如果是数组，将忽略$varValue参数。
 * 如果是字符串，那么这个字符串会作为缓存中的键名
 * 如果是数组，那么忽略$varValue参数，并且将这个数组与缓存合并。
 * 
 * @param  mixed $varValue
 * 这个参数支持任何格式，它将作为值存入缓存。
 * 
 * @return boolean
 * 如果存入成功，返回TRUE，否则返回FALSE。
 */
function fSet(
	$varInput, 
	$varValue = NULL
) {
	switch (TRUE) {
		#如果$varInput是数组，那么忽略$varValue，将$varInput与已有的缓存合并
		case is_array($varInput):
			$GLOBALS['cache']['var'] = array_merge($GLOBALS['cache']['var'], $varInput);
			return TRUE;
			break;

		#如果$varInput是字符串，那么在cache中添加一个成员，键名为$varInput，键值为$varValue
		case is_string($varInput):
			$GLOBALS['cache']['var'][$varInput] = $varValue;
			return TRUE;
			break;

		default:
			fLog('invalid data type: $varInput', 1, true);
			return FALSE;
			break;
	}
}

/**
 * 从缓存$GLOBALS['cache']['var']中取键值，如果缓存中不存在，那么返回给定的默认值
 * 
 * @param string $varName
 * 缓存中的键名
 * 
 * @param mixed $default
 * 如果缓存中不存在目标元素，那么就返回这个默认值
 * 
 * @return mixed
 * 返回从缓存中取到的值（或默认值）。
 */
function fVar(
	string $varName,
	$default = NULL
) {
	if(isset($GLOBALS['cache']['var'][$varName])) {
		return $GLOBALS['cache']['var'][$varName];
	} else {
		return $default;
	}
}

/**
 * 将一个预定义好的提示消息推入缓存$GLOBALS['cache']['notification']中
 * @param string $notification
 * 提示消息的语言配置，可查询语言文件。
 *
 * @param string $notifLevel
 * 这个消息的提示类型，它决定了消息的显示样式
 * 通常使用的类型：
 * 'normal'：一般消息
 * 'warn'：警告消息（一般错误提示）
 * 'success'：成功消息
 * 'fatal'：致命错误消息
 * 
 * @param array $vars
 * 用于替换消息内变量的数组，键名是要替换的变量名，键值是替换的结果
 */
function fNotify(
	string $notification,
	string $type = 'normal',
	array $vars = array()
) {
	$GLOBALS['cache']['notify'][] = array(
		'lang' => $notification,
		'vars' => $vars,
		'type' => $type
	);
}
?>