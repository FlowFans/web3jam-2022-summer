<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这个脚本用于在所有脚本前进行加载操作。
################################################

#配置项目录
#DIR_CFG
define("DIR_CFG", 'config/');

#启动SESSION
session_start();

#记录本次脚本执行的开始时间戳，之后可用 fExecTime() 来计算脚本执行的时间。
$GLOBALS['debug']['debugTimeStart']
= microtime(TRUE);

################################################
#设置常用路径（常量）
################################################

#依赖目录
#DIR_INC
define("DIR_INC", "includes/");

#存储日志的目录
#DIR_LOG
define("DIR_LOG", 'logs/');

#API目录
#DIR_API
define("DIR_API", 'api/');

#主题包根目录
#DIR_SKIN
define("DIR_SKIN", 'themes/');

#公共图素根目录
#DIR_IMG
define("DIR_COMMONIMAGE", 'images/');

#公共脚本目录
#DIR_SCRIPT
define("DIR_SCRIPT", 'scripts/');

#储存上传文件的目录
#DIR_UPLOAD
define("DIR_UPLOAD", 'uploads/');

#自动化脚本的目录
#DIR_AUTO
define("DIR_AUTO", 'automators/');

#异步脚本目录
#DIR_ASYNC
define("DIR_ASYNC", 'async/');

################################################
#设置 $GLOBALS['cache'] 全局缓存变量
################################################

#用于存储为 fVar() 服务的变量缓存
$GLOBALS['cache']['debugVar']
= array();

#用于存储提示信息的缓存
$GLOBALS['cache']['notify']
= array();

#用于存储执行日志的缓存
$GLOBALS['cache']['logs']
= array();

#执行日志关联的用户id（没有用户则为null）
$GLOBALS['cache']['logUser']
= null;

#用于存储变量的缓存
$GLOBALS['cache']['var']
= array(
	'DIR_ROOT' => _ROOT,
	'DIR_SKIN' => _ROOT.DIR_SKIN,
	'USER_SKIN' => 'meshal/'
);


################################################
#设置URL常量
################################################

#http协议
#URL_HTTP
define("URL_HTTP", 
	(( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) 
	? 'https://' 
	: 'http://'
);

#获取本站域名
#URL_DOMAIN
define("URL_DOMAIN",$_SERVER['HTTP_HOST']);

#获取当前脚本所在的目录
#URL_CURRENTDIR
define("URL_CURRENTDIR",$_SERVER['REQUEST_URI']);

################################################
#加载必要的方法与类
################################################

#加载核心依赖
require_once _ROOT.DIR_INC.'core/function.common.php';
require_once _ROOT.DIR_INC.'core/function.debug.php';
require_once _ROOT.DIR_INC.'core/function.cache.php';
require_once _ROOT.DIR_INC.'core/function.string.php';
require_once _ROOT.DIR_INC.'core/function.array.php';
require_once _ROOT.DIR_INC.'core/function.file.php';
require_once _ROOT.DIR_INC.'core/function.bcExtension.php';
require_once _ROOT.DIR_INC.'core/function.network.php';
require_once _ROOT.DIR_INC.'core/class.database.php';
require_once _ROOT.DIR_INC.'core/class.html.php';
require_once _ROOT.DIR_INC.'core/class.upload.php';
require_once _ROOT.DIR_INC.'core/class.math.php';
require_once _ROOT.DIR_INC.'core/class.user.php';
require_once _ROOT.DIR_INC.'core/class.userAdapter.php';
require_once _ROOT.DIR_INC.'core/class.parsedown.php';
require_once _ROOT.DIR_INC.'core/class.cypher.php';
require_once _ROOT.DIR_INC.'core/class.url.php';
require_once _ROOT.DIR_INC.'core/class.api.php';

#加载特色依赖
require_once _ROOT.DIR_INC.'addon/function.epoch.php';
require_once _ROOT.DIR_INC.'addon/function.stake.php';
require_once _ROOT.DIR_INC.'addon/class.ownership.php';
require_once _ROOT.DIR_INC.'addon/class.user.discord.php';
require_once _ROOT.DIR_INC.'addon/class.user.inventory.php';
require_once _ROOT.DIR_INC.'addon/class.user.facility.php';
require_once _ROOT.DIR_INC.'addon/class.user.effects.php';
require_once _ROOT.DIR_INC.'addon/class.user.checker.php';
require_once _ROOT.DIR_INC.'addon/class.event.php';
require_once _ROOT.DIR_INC.'addon/function.message.php';
require_once _ROOT.DIR_INC.'addon/function.twitter.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.dice.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.rule.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.attack.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.char.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.charAdapter.php';
require_once _ROOT.DIR_INC.'addon/function.meshal.charExtension.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.char.score.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.char.features.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.char.abilities.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.char.inventory.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.char.checker.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.team.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.name.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.feature.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.ability.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.item.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.item.usage.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.facility.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.adventure.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.adventure.checker.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.adventure.executor.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.adventure.encounter.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.adventure.logger.php';
require_once _ROOT.DIR_INC.'addon/class.meshal.adventure.logRenderer.php';

#加载钱包处理器
require_once _ROOT.DIR_INC.'addon/wallets/flow.php';

#加载地址验证器
require_once _ROOT.DIR_INC.'addon/validators/flow.php';

#加载交易处理器
require_once _ROOT.DIR_INC.'addon/transactors/flow.php';

#加载第三方库
require_once _ROOT.DIR_INC.'vendor/keccak/Keccak.php';

################################################
#加载配置项
################################################

#加载框架设置
require_once _ROOT.DIR_CFG.'framework.php';

#加载Debug设置
require_once _ROOT.DIR_CFG.'debug.php';

#加载站点设置
require_once _ROOT.DIR_CFG.'localSettings.php';

#加载数据库设置
require_once _ROOT.DIR_CFG.'database.php';

#加载社交平台配置
require_once _ROOT.DIR_CFG.'socialConnect.php';

#加载语言配置
require_once _ROOT.DIR_CFG.'languageSettings.php';

#加载角色设置
require_once _ROOT.DIR_CFG.'roles.php';

#加载Meshal配置
require_once _ROOT.DIR_CFG.'meshal.php';

#加载CP经济配置
require_once _ROOT.DIR_CFG.'cpSettings.php';

#加载环境配置
require_once _ROOT.DIR_CFG.'environment.php';

#加载外部API配置
require_once _ROOT.DIR_CFG.'externalAPI.php';

#加载对应不同区块链网络的配置
require_once _ROOT.DIR_CFG.'network.php';

#设置时区
date_default_timezone_set($GLOBALS['deploy']['timeZone']);

#创建logger文件
require_once _ROOT.DIR_INC.'logger.php';

#如果没有定义常量 _NOREQUESTLOG 和 _NOLOG，那么会将页面请求写入log。
#在被频繁调取的接口（比如聊天拉数据）中，应当定义 _NOREQUESTLOG 和 _NOLOG，以免产生无用日志。
if (
	!defined('_NOREQUESTLOG')
	&& !defined('_NOLOG')
) {
	\fLog($_SERVER['SCRIPT_FILENAME'].' was requested.',0,TRUE);
}

#创建全局的数据库访问对象
$db = new \xDatabase;

#进行自动任务
if(!defined('_NOTAUTOMATOR')) require_once _ROOT.'_automator.php';
?>