<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供Twitter分享的方法
################################################


/**
 * 组装并返回一个Twitter分享链接
 * 
 * @param string $text
 * 分享文字
 * 
 * @param string $url
 * 分享的链接url
 * 
 * @param array $hashtags
 * 分享时自动添加的#话题
 * 每个成员一个#话题
 * 
 * @param array $vars
 * 用于替换$text 和 $url中变量的数组
 * 键名是占位符，键值是替换的内容
 * 
 * @return string
 * 返回组装好的url
 */
function fTwitterShareUrl (
    string $text = '',
    string $url = '',
    array $hashtags = array(),
    array $vars = array()
) {
    
    $text = \fReplace($text, $vars);
    $url = \fReplace($url, $vars);
    
    return $GLOBALS['social']['twitter']['url']
        .'&text='.urlencode($text)
        // .'&original_referer='.urlencode($referer)
        .'&url='.urlencode($url)
        .'&hashtags='.implode(',', $hashtags)
    ;
}
?>