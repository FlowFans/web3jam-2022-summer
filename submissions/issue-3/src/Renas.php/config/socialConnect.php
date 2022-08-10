<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 社交平台设置相关参数
################################################

# 登录方法设置
$GLOBALS['setting']['login']['default'] = 'discord';
$GLOBALS['setting']['login']['discord'] = true;

# Discord Link
$GLOBALS['social']['discord']['invitation'] = 'https://discord.gg/thingfund';

# Discord OAuth2 Settings
$GLOBALS['social']['discord']['clientId'] = '943592580367663104';
$GLOBALS['social']['discord']['clientSecret'] = 'TIJRM8VVAesR8MqKWRYsMcwPjE8bh6pR';
$GLOBALS['social']['discord']['authorizeUrl'] = 'https://discord.com/api/oauth2/authorize';
$GLOBALS['social']['discord']['tokenUrl'] = 'https://discord.com/api/oauth2/token';
$GLOBALS['social']['discord']['apiUrlBase'] = 'https://discord.com/api/users/@me';
$GLOBALS['social']['discord']['revokeUrl'] = 'https://discord.com/api/oauth2/token/revoke';

# Twitter Share Settings
$GLOBALS['social']['twitter']['url'] = 'https://twitter.com/intent/tweet?';
$GLOBALS['social']['twitter']['hashtag']['character'] = array('onflow');
$GLOBALS['social']['twitter']['hashtag']['adventure'] = array('onflow', 'cocreation');

?>