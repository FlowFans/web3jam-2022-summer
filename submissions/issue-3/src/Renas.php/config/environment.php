<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这个脚本用于定义环境变量
################################################

//启用所有报错
error_reporting(E_ALL & ~E_NOTICE);

//系统维护状态
$GLOBALS['deploy']['maintainance'] = false;

# 初始化测试用的SESSION
$_SESSION['discordToken'] = '26nqsR2MKYsd4p12J4IY9gMjHtElet';
$_SESSION['discordUser'] = json_decode(json_encode(
    array(
        'id' => '728461113641140316',
        // 'id' => '851482027369693184',
        'username' => 'test',
        'avatar' => '917f7253d3242204b29d8a6bfa149400',
        'discriminator' => '3586',
        'public_flags' => 0,
        'flags' => 0,
        'banner' => '',
        'banner_color' => '#2e2e2e',
        'accent_color' => '3026478',
        'locale' => 'en-US',
        'mfa_enabled' => ''
    )
));

?>