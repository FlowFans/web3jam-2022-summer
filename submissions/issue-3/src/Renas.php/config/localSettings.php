<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 站点部署设置相关参数
################################################

# 站点版本号
$GLOBALS['deploy']['buildVersion'] = '6'; //Build号
$GLOBALS['deploy']['version'] = '0.5.2'; //版本号
$GLOBALS['deploy']['versionCode'] = 'Alpha'; //版本词缀
$GLOBALS['deploy']['buildHash'] = '664796f26c891671df23e29f6ecf8cc9d92ebc96'; //用于区分不同版本静态内容的变量

# 基本设置
$GLOBALS['deploy']['siteRoot'] = 'http://10.0.0.153/renas/'; //站点根路径
$GLOBALS['deploy']['deployedDir'] = '/renas/'; //站点部署目录（相对于主站根目录）
$GLOBALS['deploy']['defaultPage'] = 'plaza/'; //站点默认起始页
$GLOBALS['deploy']['http'] = URL_HTTP; //HTTP协议设置（http或https）
$GLOBALS['deploy']['timeZone'] = 'UTC'; //时区设置
$GLOBALS['deploy']['lang'] = 'en'; //语言设置
$GLOBALS['deploy']['charset'] = 'UTF-8'; //站点编码设置
$GLOBALS['deploy']['redirectAwaits'] = 300; //重定向等待秒数

# 站点资料
$GLOBALS['deploy']['siteOwner'] = 'THiNG.FUND'; //站点所有者
$GLOBALS['deploy']['siteName'] = 'The Renascence'; //站点名称
$GLOBALS['deploy']['siteNameAbbr'] = 'RENAS'; //站点名称缩写
$GLOBALS['deploy']['adminName']	= 'THiNG.FUND Admin';
$GLOBALS['deploy']['adminEmail'] = 'admin@thing.fund';

# 站点皮肤
$GLOBALS['deploy']['siteLogo'] = 'siteLogo.png'; //站点logo
$GLOBALS['deploy']['skin'] = 'default'; //皮肤配置

# 账号限制
$GLOBALS['deploy']['accountLengMin'] = 4;
$GLOBALS['deploy']['accountLengMax'] = 32;

# 密码级别
$GLOBALS['deploy']['passwordLengMin'] = 6;
$GLOBALS['deploy']['passwordLengMax'] = 64;
$GLOBALS['deploy']['passwordComplicity'] = array(
	'upperChar',
	'lowerChar',
	'digitChar',
	'specialChar',
);
$GLOBALS['deploy']['passwordComplicityMin'] = 2;

# 加密与秘钥设置
$GLOBALS['deploy']['cypherSecret'] = 'necrozCif3rKeY'; //Only set once while deploying.
$GLOBALS['deploy']['securityKey'] = 'S3crE+N3cr02'; //Only set once while deploying.
$GLOBALS['deploy']['encryptLevel'] = 2;

# Mail Settings
$GLOBALS['deploy']['mail']['smtpHost'] = 'mail.necroz.com';
$GLOBALS['deploy']['mail']['username'] = 'webmaster@necroz.com';
$GLOBALS['deploy']['mail']['password'] = '';

# 文件下载限速，设为NULL时，不限速
$GLOBALS['deploy']['fileDownloadRate'] = NULL;

# 上传资源配置
$GLOBALS['deploy']['upload']['portrait'] = 'portraits/'; //角色头像
$GLOBALS['deploy']['upload']['itemImage'] = 'itemImages/'; //物品图片
$GLOBALS['deploy']['upload']['facilityImage'] = 'facilityImages/'; //设施图片
$GLOBALS['deploy']['upload']['adventureCover'] = 'adventureCover/'; //冒险图封面

# 导航菜单设置
# 每个$GLOBALS['deploy']['navbar']中的成员都是一个数组，键名被用于显示，键值为url
# 键名会自动根据语言渲染，键值不需要加_ROOT
$GLOBALS['deploy']['navbar']['plaza'] = array();
	$GLOBALS['deploy']['navbar']['plaza']['plaza'] = 'plaza/';
	$GLOBALS['deploy']['navbar']['plaza']['wanderer'] = 'wanderer/';
	$GLOBALS['deploy']['navbar']['plaza']['ranking'] = 'ranking/';
$GLOBALS['deploy']['navbar']['wilds'] = array();
	$GLOBALS['deploy']['navbar']['wilds']['adventure'] = 'adventure/';
$GLOBALS['deploy']['navbar']['base'] = array();
	$GLOBALS['deploy']['navbar']['base']['myCharacters'] = 'character/';
	$GLOBALS['deploy']['navbar']['base']['facility'] = 'facility/';
	$GLOBALS['deploy']['navbar']['base']['warehouse'] = 'warehouse/';
$GLOBALS['deploy']['navbar']['staking'] = array();
	$GLOBALS['deploy']['navbar']['staking']['adventure'] = 'staking/adventure/';
$GLOBALS['deploy']['navbar']['editor'] = array();
	$GLOBALS['deploy']['navbar']['editor']['editor.feature'] = 'editor/feature/';
	$GLOBALS['deploy']['navbar']['editor']['editor.item'] = 'editor/item/';
	$GLOBALS['deploy']['navbar']['editor']['editor.facility'] = 'editor/facility/';
	$GLOBALS['deploy']['navbar']['editor']['editor.encounter'] = 'editor/encounter/';
	$GLOBALS['deploy']['navbar']['editor']['editor.adventure'] = 'editor/adventure/';
	$GLOBALS['deploy']['navbar']['editor']['editor.language'] = 'editor/language/';
?>