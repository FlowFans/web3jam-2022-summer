<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 这里储存了数据库访问的配置信息，由站点管理员进行维护
################################################

# MySQL服务器URL
$GLOBALS['deploy']['db']['host']
= "localhost";

# MySQL服务器端口
$GLOBALS['deploy']['db']['port']
= 3307;

# MySQL服务器Socket（不适用socket连接则设为NULL）
$GLOBALS['deploy']['db']['socket']
// = NULL;
= '/run/mysqld/mysqld10.sock';

# MySQL数据库名称
$GLOBALS['deploy']['db']['dbname']
= "thingfund";

# MySQL数据库访问用户名
$GLOBALS['deploy']['db']['username']
= "root";

# MySQL数据库访问密码
$GLOBALS['deploy']['db']['password']
= "N@sMariaD8";

# MySQL数据表前缀
$GLOBALS['deploy']['db']['prefix']
= "renas_";


?>