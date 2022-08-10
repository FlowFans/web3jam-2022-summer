<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供对字符串做处理的方法
################################################

/**
 * 将一个大整数（以字符串传递）根据设定的位数转换成浮点数
 * 
 * @param string $num
 * 待转换的数字（字符串形式）
 * 如果传递的不是字符串格式，那么返回原值；
 * 
 * @param int $precision
 * 显示的小数位精度
 * 默认为4
 * 
 * @param int $decimals
 * 实际的小数位精度
 * 如果为null，则取全局配置（或函数内预设值18）
 * 默认为null
 */
function fFloat(
	string $num,
	int $precision = 4,
	int $decimals = 0
) {
	$dec = strlen($num) < $decimals ? str_pad($num, $decimals, '0', STR_PAD_LEFT) : substr($num, -$decimals);
	$int = strlen($num) < $decimals ? 0 : substr($num, 0, strlen($num) - $decimals);

	//只取需要的小数位
	$dec = substr($dec, 0, $precision);

	$return = strlen($dec) == 0 ? "{$int}" : "{$int}.{$dec}";

	return $return;
}


/**
 * 将数值按照1024进制换算为计算机用的比特数，并附带kb,mb,gb...etc，最高为nb。
 * @param  integer  $bytes
 * 需要换算的数值
 * @param  integer $precision
 * 换算后给出的小数精度（默认为3）
 * @return string
 * 返回得到的结果如105 kb、65.31 mb
 */
function fFormatByte(
	int $bytes, 
	int $precision = 3
) {
	$notation = array(
		'bytes',
		'kb',
		'mb',
		'gb',
		'tb',
		'pb',
		'eb',
		'zb',
		'yb',
		'db',
		'nb',
	);

	$digits = floor(log($bytes, 2)/10);
	$unit = $digits <= count($notation)-1 ? $notation[$digits] : $notation[count($notation)-1];
	$num = round($bytes / pow(2, $digits*10),$precision);

	return "{$num} {$unit}";
}

/**
 * 将时间戳转为可读时间，可选择不同的输出样式
 * 
 * @param string|int $unixTime
 * 输入的unix格式的时间（秒）
 * 
 * @param string $outputFormat = 'hour'
 * 输出的格式控制
 * hour：最大时间单位为小时
 * day：最大时间单位为天
 * date：返回如”20200730“的日期格式
 * date/：返回如”2020/07/30“的日期格式
 * date-：返回如”2020-07-30“的日期格式
 * time-：返回如”01-25-29“的时间格式
 * 其他值：输出标准的年/月/日 时:分:秒格式（日期格式）
 * 
 * @return string
 * 输出文本格式的可读时间
 */
function fFormatTime(
	$unixTime,
	$outputFormat = null
) {
	switch ($outputFormat) {
		case 'hour':
			$hour = intval($unixTime / 3600);
			$min = intval(($unixTime % 3600) / 60);
			$sec = $unixTime % 60;

			return "{$hour}:{$min}:{$sec}";
			break;

		case 'full':
			$day = intval($unixTime / 86400);
			$hour = intval(($unixTime % 86400) / 3600);
			$min = intval(($unixTime % 3600) / 60);
			$sec = $unixTime % 60;

			$rDay = $day == 0 ? '' : "{$day} {?common.days?} ";

			switch ($day) {
				case 0:
					$rDay = '';
					break;
				
				case 1:
					$rDay = "{$day} {?common.day?} ";
					break;

				default:
					$rDay = "{$day} {?common.days?} ";
					break;
			}

			switch ($hour) {
				case 0:
					$rHour = $day == 0 ? '' : "{$hour} {?common.hour?} ";
					break;
				
				case 1:
					$rHour = "{$hour} {?common.hour?} ";
					break;

				default:
					$rHour = "{$hour} {?common.hours?} ";
					break;
			}

			switch ($min) {
				case 0:
					$rMin = $day + $hour == 0 ? '' : "{$min} {?common.minute?} ";
					break;
				
				case 1:
					$rMin = "{$min} {?common.minute?} ";
					break;

				default:
					$rMin = "{$min} {?common.minutes?} ";
					break;
			}

			switch ($sec) {
				case 0:
					$rSec = $day + $hour + $min == 0 ? '' : "{$sec} {?common.second?}";
					break;
				
				case 1:
					$rSec = "{$sec} {?common.second?}";
					break;

				default:
					$rSec = "{$sec} {?common.seconds?}";
					break;
			}

			return $rDay.$rHour.$rMin.$rSec;
			break;
		
		case 'day':
			$day = intval($unixTime / 86400);
			$hour = intval(($unixTime % 86400) / 3600);
			$min = intval(($unixTime % 3600) / 60);
			$sec = $unixTime % 60;

			return "$day {?common.days?} {$hour}:{$min}:{$sec}";
			break;

		case 'date':
			return date("Ymd", $unixTime);
			break;

		case 'date-':
			return date("Y-m-d", $unixTime);
			break;

		case 'date/':
			return date("Y/m/d", $unixTime);
			break;

		case 'time-':
			return date("H-i-s", $unixTime);
			break;

		default:
			return date("Y/m/d H:i:s", $unixTime).' '.$GLOBALS['deploy']['timeZone'];
			break;
	}
	
}

/**
 * 给定的字符串中，将连续的多个空格合并为1个空格
 * 
 * @param string $string
 * 待处理的字符串
 * 
 * @return string $string
 * 返回合并连续空格后的字符串
 */
function fMergeSpace(
	string $string
) {
	return preg_replace('/\s(?=\s)/','\1',$string);
}

/**
 * 处理命令格式，命令多按照“命令 参数1 参数2 参数n……”的格式，以空格分隔。
 * 
 * @param string $string
 * 用作格式化为命令的字符串。
 * 
 * @param string $delimiter = ' '
 * 用作分隔参数和命令的符号
 * 
 * @param integer $params = null
 * 指定这个命令支持几个参数（命令本身也作为1个参数，即0号参数）
 * 如果设为默认值NULL，则有多少个分隔符，就会拆分成多少个参数
 * 
 * @param boolean $mergeSpace = false
 * 是否要预先合并连续空格
 * 默认为FALSE时，不合并多个连续的空格
 * 设为TRUE时，会在处理命令之前先把整个字符串中的多个连续空格合并为1个空格
 * 
 * @return array
 * 返回以数组形式构成的命令和参数组，数组的第0个元素通常都用于命令本身，其他元素则是参数
 */
function fFormatCommand(
	string $command,
	string $delimiter = ' ',
	int $params = NULL,
	bool $mergeSpace = FALSE
) {
	#合并连续空格
	if($mergeSpace === TRUE) {
		$command = fMergeSpace($command);
	}


	#根据分隔符拆分成数组并返回
	if($params === NULL) {
		return explode($delimiter, $command);
	} else {
		return explode($delimiter, $command, $params);
	}
}

/**
 * 对字符串执行指定次数替换
 * 
 * @param mixed $search
 * 查找目标值
 * 
 * @param mixed $replace
 * 替换值
 * 
 * @param mixed $subject
 * 执行替换的字符串／数组
 * 
 * @param int $limit
 * 允许替换的次数，默认为-1，不限次数
 * 
 * @return mixed
 */
function fStrReplace(
	$search,
	$replace,
	$subject,
	$limit = -1
) {
    if(is_array($search)){
        foreach($search as $k=>$v){
            $search[$k] = '`'. preg_quote($search[$k], '`'). '`';
        }
    }else{
        $search = '`'. preg_quote($search, '`'). '`';
    }
    return preg_replace($search, $replace, $subject, $limit);
}

/**
 * 对文本中的占位符替换成$replaceSet中的变量。
 * 这个方法支持递归，因此当递归层数超出框架设置，就会报错，并且直接返回不作处理的结果。
 * 
 * @param  string  $source
 * 需要被替换的文本
 *
 * @param array $replaceSet 
 * 替换词库，每个元素的键名是留待替换的占位符名称，键值是被替换的内容。
 * 比如：array('name' => 'something') 会把 "你好，{?name?}" 替换为 "你好，something"
 *
 * @param string $pattern
 * 正则匹配规则，如果为null则会自动设为默认pattern
 * 
 * @param boolean $recursive 
 * 是否进行递归替换，默认TRUE为进行递归，设为FALSE则不做递归。
 * 
 * @param  integer $recursion
 * 用于计算当前递归层数，无需设置。
 * 
 * @return string
 * 返回替换后的字符串。
 */
function fReplace(
	$source, 
	array $replaceSet, 
	bool $recursive = TRUE, 
	int $recursion = 0
) {
	#对递归层数做检查，如果超过递归层数限制，抛错并返回不做处理的$source
	if($recursion >= $GLOBALS['setting']['fReplace']['maxRecursive']) {
		fLog("too many recurring: {$recursive} > {$GLOBALS['setting']['fReplace']['maxRecursive']}", 1, true);
		return $source;
	}
	
	#匹配所有占位符名称，不允许有空格，输出结果到$match, $match[0]为包括{?...?}符号的匹配内容，$match[1]则为符号内的内容，实际有用的只有$match[1]
	preg_match_all('~\{\?([a-zA-Z0-9\-_\.\!\$]*?)\?\}~mU', $source, $match, PREG_PATTERN_ORDER, 0);
	// preg_match_all('~\{\?(.*?)\?\}~mU', $source, $match, PREG_PATTERN_ORDER, 0);
	#去除重复
	$match[1] = array_flip(array_flip($match[1]));

	#如果有占位符存在于文本中，那么遍历所有$match[1]中的成员，组成替换列表
	if(!empty($match[1])) {
		/**
		 * $pairs数组用来储存需要替换的占位符及其对应的替换结果，格式为：
		 * array(
		 * 		'{?占位符名称?}' => '替换的结果'
		 * )
		 */
		$pairs = array();

		#遍历每一个$match[1]中的占位符，如果$replaceSet中有对应的替换值，就将其放进$pairs中
		foreach ($match[1] as $k => $v) {
			if(isset($replaceSet[$v])) {
				switch (TRUE) {
					#对数值或字符串做替换
					case is_numeric($replaceSet[$v]):
						$pairs["{?{$v}?}"] = $replaceSet[$v];
						break;

					case is_string($replaceSet[$v]):
						$pairs["{?{$v}?}"] = $replaceSet[$v];
						break;
					
					#否则判断为数据类型不合法。
					default:
						fLog("{$v} is ".gettype($v).", expecting numeric or string", 1, true);
						break;
				}
			}
		}
	}

	#如果占位符没有替换的值，那么直接返回不处理的文本，否则做递归替换。
	if(empty($pairs) || $recursive == FALSE) {
		return $source;
	} else {
		$parsed = strtr($source, $pairs);
		$parsed = fReplace($parsed, $replaceSet, TRUE, $recursion+1);
		return $parsed;
	}
}

/**
 * 转化一个多行文本到数组，使每个非空行成为数组的元素
 * 
 * @param string $string
 * 要处理的多行文本
 * 
 * @param bool $escapeSpace
 * 如果为true，则会删除多余的空格。为false时不对空格做额外处理。
 * 默认为true
 */
function fLineToArray (
	string $string,
	bool $escapeSpace = true
) {
	$string = preg_replace('~[\n\r]+~', PHP_EOL, $string);
	$return = explode(PHP_EOL, $string);

	//是否对多余的空格做处理
	if($escapeSpace === true) {
		foreach ($return as $k => $s) {
			$replaced = trim($s);
			if($replaced == '') {
				unset($return[$k]);
			} else {
				$return[$k] = trim($s);
			}
		}
	}
	
	return array_filter($return);
}

/**
 * 将一段ini格式的字符串转化成一个数组
 * 处理过程中，会忽略起始为“;”或“#”的注释内容
 * 处理过程中，会忽略断行（没有“=”的行）
 * 数组支持从ini获取键名与键值（以及多维数组）
 *
 * @param string $string 
 * 被处理的字符串
 *
 * @return array
 * 返回被转化的数组
 */
function fIniToArray(
	string $string
) {
	if(empty($string)) {
		return false;
	}

	$lines = explode("\n", $string);
	$return = array();
	$inside_section = false;

	foreach($lines as $line) {
		
		$line = trim($line);

		if(!$line || $line[0] == "#" || $line[0] == ";") continue;
		
		if($line[0] == "[" && $endIdx = strpos($line, "]"))
		{
			$inside_section = substr($line, 1, $endIdx-1);
			continue;
		}

		if(!strpos($line, '=')) continue;

		$tmp = explode("=", $line, 2);

		if($inside_section) {
			
			$key = rtrim($tmp[0]);
			$value = ltrim($tmp[1]);

			if(preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
				$value = mb_substr($value, 1, mb_strlen($value) - 2);
			}

			$t = preg_match("^\[(.*?)\]^", $key, $matches);
			if(!empty($matches) && isset($matches[0])) {

				$arr_name = preg_replace('#\[(.*?)\]#is', '', $key);

				if(!isset($return[$inside_section][$arr_name]) || !is_array($return[$inside_section][$arr_name])) {
					$return[$inside_section][$arr_name] = array();
				}

				if(isset($matches[1]) && !empty($matches[1])) {
					$return[$inside_section][$arr_name][$matches[1]] = $value;
				} else {
					$return[$inside_section][$arr_name][] = $value;
				}

			} else {
				$return[$inside_section][trim($tmp[0])] = $value;
			}            

		} else {
			
			$return[trim($tmp[0])] = ltrim($tmp[1]);

		}
	}
	return $return;
}

/**
 * 将一个字符串拆分成多维数组
 * 
 * @param string|array $delimiter
 * 用于拆分字符串的分隔符。如果是数组，则每个元素的键值对应一级分隔符。
 * 
 * @param string $string
 * 被拆分的字符串
 * 
 * @param int $recursion = 0
 * 递归计数器，无需设置
 * 
 * @return array
 * 返回一个拆分后的数组
 */
function fStrToMultiArray(
	$delimiter,
	string $string,
	int $recursion = 0
) {
	if(!is_array($delimiter)) $delimiter = array($delimiter);

	if($recursion > count($delimiter) - 1) return $string;

	$exploded = explode($delimiter[$recursion], $string);
	if(count($exploded) == 1) return $string; //如果只有1个成员，就表示不能被拆分，因此直接返回原字符串

	
	foreach($exploded as $k => &$ele) {
		$ele = \fStrToMultiArray($delimiter, $ele, $recursion + 1);
	}

	return $exploded;
}

/**
 * 将多维数组转化成字符串
 * 
 * @param string|array $delimiter
 * 用于粘结字符串的分隔符。如果是数组，则每个元素的键值对应一级分隔符。
 * 这个参数的元素数量同时也决定了最多会递归几级的多维数组，每级数组的分隔符必须保持不同。
 * 
 * @param array $array
 * 被转化的数组
 * 
 * @param int $recursion = 0
 * 递归计数器，无需设置
 * 
 * @return string
 * 返回处理后的字符串
 */
function fMultiArrayToStr(
	$delimiter,
	array $array,
	int $recursion = 0
) {
	if(!is_array($delimiter)) $delimiter = array($delimiter);
	
	if($recursion >= count($delimiter)) return $array;

	foreach($array as $k => $var) {
		if(is_array($var)) { //如果成员是数组，则递归处理该数组
			$array[$k] = \fMultiArrayToStr($delimiter, $var, $recursion + 1);
		}
	}
	return implode($delimiter[$recursion], $array);
}

/**
 * 生成全局唯一ID
 * 
 * @param string $namespace
 * 生成唯一ID时使用的前缀
 * 默认为空白字符串。
 */
function fGenGuid(
	string $namespace = ''
) {  
	$guid = '';
	$uid = uniqid("", true);
	$data = $namespace;
	$data .= $_SERVER['REQUEST_TIME'];
	$data .= $_SERVER['HTTP_USER_AGENT'];
	// $data .= $_SERVER['LOCAL_ADDR'];
	// $data .= $_SERVER['LOCAL_PORT'];
	$data .= $_SERVER['REMOTE_ADDR'];
	$data .= $_SERVER['REMOTE_PORT'];
	$hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
	$guid = '' . 
		substr($hash, 0, 8) .
		'-' .
		substr($hash, 8, 4) .
		'-' .
		substr($hash, 12, 4) .
		'-' .
		substr($hash, 16, 4) .
		'-' .
		substr($hash, 20, 12) .
		'';
	return $guid;
}

/**
 * 提取文本中的元素类型
 * 
 * @param string $string
 * 用于提取的文本
 * 
 * @return array
 * 返回一个包含分析结果的数组
 */
function fStringElements(
	string $string
) {
	$dict = array(
		'upperChar' => '/[A-Z]/',
		'lowerChar' => '/[a-z]/',
		'digitChar' => '/[0-9]/',
		'chineseChar' => '/[\x{4e00}-\x{9fa5}]/u',
		'doubleChar' => '/[^\x{00}-\x{ff}]/u',
		'specialChar' => '/[~!@#$%^&*()\\\-_=+{};:<,.>?\'\"`]/',
		'underline' => '/_/',
		'space' => '/\s/',
	);

	$valid = array();

	foreach ($dict as $k => $v) {
		preg_match_all($v, $string, $result);
		$valid['counts'][$k] = count($result[0]);
	}

	foreach ($valid['counts'] as $k => $v) {
		$valid['contains'][$k] = $v > 0 ? 1 : 0;
	}

	return $valid;
}

/**
 * 进行版本号比较
 * 版本号必须是以“.”分隔
 * 
 * @param string $version
 * 用于比较的版本号，格式通常是“0.1.2”
 * 
 * @param string $benchmark
 * 被比较的版本号，格式通常是“0.1.2”
 * 
 * @return int
 * 如果版本小于被比较的，返回 -1；
 * 如果版本大于被比较的，返回 1；
 * 如果版本相等，返回0。
 */
function fCheckVersion(
	$version,
	$benchmark
) {
	$ver = explode('.', $version);
	$bench = explode('.', $benchmark);

	foreach ($ver as $seq => $val) {
		if(bccomp($val, $bench[$seq]) == -1) return -1;
		if(bccomp($val, $bench[$seq]) == 1) return 1;
	}
	return 0;
}

/**
 * Encrypt given string
 *
 * @param string $string 
 * 	source string being encrypted
 *
 * @param integer $method 
 * 	which encrypting method to be used
 * 	set to NULL will use $GLOBALS['deploy']['encryptLevel']
 *
 * @param string $saltKey 
 * 	salt string that will make encrypt string more complex
 * 	set to NULL will use $GLOBALS['deploy']['securityKey']
 *
 * @return string
 * 	returns encrypted string
 */
function fEncrypt(
	string $string, 
	int $method = NULL, 
	string $saltKey = NULL
) {
	$method = is_null($method) ? $GLOBALS['deploy']['encryptLevel'] : $method;
	$salt = is_null($saltKey) ? $GLOBALS['deploy']['securityKey'] : $saltKey;

	switch ($method) {
		case '0':
			$return = hash("sha256", $salt.$string);
			break;
		
		case '1':
			$return = hash("sha256",md5($salt.$string));
			break;

		case '2':
			$return = hash("sha512",hash("sha256",$salt.$string));
			break;

		default:
			$return = hash("sha256", $salt.$string);
			break;
	}

	return $return;
}

/**
 * 将传递的字符串先进行html转义，再进行base64编码处理。
 * 这个函数可以对一些写入数据库，或将被公开展示的文本进行预处理，防止脚本注入。
 * 
 * @param string $string
 * 被处理的文本
 * 
 * @return string
 * 返回处理后的文本
 */
function fEncode (
	$string
) {
	return base64_encode(htmlentities($string));
}

/**
 * 将传递的字符串进行base64解码处理，如果需要，也可以对html转义符重新编码回正常的字符
 * 
 * @param string $string
 * 被处理的文本
 * 
 * @param bool $htmlEntityDecode
 * 是否要做html转义处理，为true时将做转义。
 * 默认为false
 * 
 * @return string
 * 返回解码后的文本
 */
function fDecode (
	$string,
	bool $htmlEntityDecode = true
) {
	if($htmlEntityDecode === true) {
		return html_entity_decode(base64_decode($string));
	} else {
		return base64_decode($string);
	}
}

/**
 * 获得使用此函数的页面的上层目录路径
 * 
 * @return string
 * 比如：https://thing.fund/renas/my/test.php 使用本函数，将得到 /renas/my/
 */
function fSelfDir () {
	$arr = explode('/', $_SERVER['PHP_SELF'], -1);
	return implode('/', $arr).'/';
} 

/**
 * 生成_back参数
 * 注意，如果这个参数是页面请求的唯一参数，需要在使用它的路径末尾包含“?”作为参数前导符
 * 
 * @param string $url = null
 * 返回上一级的路径
 * 
 * @param bool $nest = true
 * 是否嵌套上一级路径的_back
 * $url == null时，本参数失效，会总是嵌套上一层路径的_back
 * 
 * @return string
 * 返回符合_back参数标准的encoded url
 */
function fBackUrl (
	string $backUrl = null,
	bool $nest = true
) {
	if($backUrl == '' || is_null($backUrl)) {
		return '&_back='.\fEncode(
			\fStrReplace(
				$GLOBALS['deploy']['deployedDir'],
				'',
				$_SERVER['REQUEST_URI'],
				1
			)
		);
	}

	if(
		$nest === true
		&& \fGet('_back', '') != ''
	) {
		$urlComp = explode('?', $backUrl);
		if(isset($urlComp[1])) {
			$urlComp[1] .= '&_back='.\fGet('_back', '');
		} else {
			$urlComp[1] = '_back='.\fGet('_back', '');
		}

		$backUrl = implode('?', $urlComp);
	}

	return '&_back='.\fEncode($backUrl);
}

/**
 * Converts Decimal number into any Radix String(max 62 available chars)
 *
 * @param integer $input 
 * 	Decimal number being converted
 *
 * @param string $toRadix 
 * 	Available character set
 * 	HINT: 8(OCT), 10(DEC), 16(HEX), 36(0-9 & A-Z), 62(0-9 & A-Z & a-z)
 *
 * @return string
 * 	returns converted string that based on given radix.
 */
function fDecConvert(
	$input,
	$toRadix = 62
) {
	$MIN_RADIX = 2;
	$MAX_RADIX = 62;	
	$num62 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	if ($toRadix < $MIN_RADIX || $toRadix > $MAX_RADIX) {
		$toRadix = 2;
	}
	if ($toRadix == 10) {
		return $input;
	}

	// -Long.MIN_VALUE when its Binary, length=65
	$buf = array();
	$charPos = 64;
	$isNegative = $input < 0; //(bccomp($input, 0) < 0); if (!$isNegative) {
	$input = -$input; // bcsub(0, $input); }
		while (bccomp($input, -$toRadix) <= 0) {
			$buf[$charPos--] = $num62[-bcmod($input, $toRadix)];
			$input = bcdiv($input, $toRadix);
		}
		$buf[$charPos] = $num62[-$input];

	if ($isNegative) {
			$buf[--$charPos] = '-';
	}
		$return = '';
		for ($i = $charPos; $i < 65; $i++) {
			$return .= $buf[$i];
		}
		return $return;
}


/**
 * Converts a String to a Decimal number
 * This function works with nzDecB62()
 *
 * @param string $string 
 * 	String being coverted
 *
 * @param integer $fromRadix 
 * 	Available character set to decode the given string
 * 	HINT: 8(OCT), 10(DEC), 16(HEX), 36(0-9 & A-Z), 62(0-9 & A-Z & a-z)
 *
 * @return integer
 * 	returns converted Decimal value
 */
function fDecRevert(
	$string,
	$fromRadix = 62
) { 
	$num62 =  '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$return = 0;
	$digitValue = 0;
	$len = strlen($string) - 1;

	for ($t = 0; $t <= $len; $t++) {
		$digitValue = strpos($num62, $string[$t]);
		$return = bcadd(bcmul($return, $fromRadix), $digitValue);
	}
	
	return $return;
}
?>