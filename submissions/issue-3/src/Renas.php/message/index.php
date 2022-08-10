<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('message/body.frame.html', 'body');

$html->set('$messageList', '{?common.noMessages?}');

$where = array(
    "`uid` = '{$user->uid}'"
);

if(\fGet('type', '') != '') {
    $where[] = "`type` = '{$_GET['type']}'";
}

$msgCount = $db->getCount(
    'messages',
    $where
);

if(\fGet('action', '') == 'delete' && $_GET['id']) {
    $db->delete(
        'messages',
        array(
            "`id` = '{$_GET['id']}'"
        ),
        1
    );
}

# 组装消息类型选择器
$typeMenu = array();
foreach ($GLOBALS['meshal']['messageType'] as $typeName => $settings) {
    $typeMenu[] = array(
        '--type' => $typeName == $_GET['type'] ? '' : $typeName,
        '--active' => $typeName == $_GET['type'] ? 'nzSelectionActive' : '',
        '--name' => "{?{$settings['name']}?}"
    );
}

$html->set('$typeMenu', $html->duplicate(
    'message/dup.filterRow.type.html',
    $typeMenu
));

if($msgCount > 0) {
    # 根据当前页取消息
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['message']['messagesPerPage'];
    $html->set('$page', \fGet('page', 1));
    $queryMsg = $db->getArr(
        'messages',
        $where,
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['message']['messagesPerPage']}",
        null,
        'timestamp',
        'DESC'
    );

    #组装消息列表
    if($queryMsg !== false) {
        //拼装语言数据
        $comp = array();
        foreach ($queryMsg as $cur) {
            $comp[] = array(
                '--timestamp' => date('Y-m-d H:i:s',$cur['timestamp']),
                '--unread' => $cur['unread'] == 1 ? 'nzMessageCard-unread' : '',
                '--message' => localRender($cur['message'], json_decode(\fDecode($cur['data']), true)),
                '--messageId' => $cur['id']
            );

            if($cur['unread'] == 1) { //既然加载到了，就标记为已读
                $db->update(
                    'messages',
                    array(
                        'unread' => 0
                    ),
                    array(
                        "`id` = '{$cur['id']}'"
                    ),
                    1
                );
            }
        }
        
        $html->set(
            '$messageList', 
            $html->duplicate(
                'message/dup.row.html',
                $comp
            )
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $msgCount,
            $GLOBALS['setting']['pager']['message']['messagesPerPage'],
            '?page={?$page?}&type='.$_GET['type']
        )
    );
} else {
    $html->set('$messageList', '{?common.noMessages?}');
    $html->set('$pager', '');
}


$html->output();

\fDie();

function localRender(
    string $message,
    array $vars,
    bool $recursive = true,
    int $recursion = 0,
    array $compare = array()
) {
    global $db;
    global $html;

    \fLog("Rendering recursion: {$recursion}");
    #对递归层数做检查，如果超过递归层数限制，抛错并结束递归
    if($recursion >= $GLOBALS['setting']['fReplace']['maxRecursive']) {
        \fLog("too many recurring: {$recursive} > {$GLOBALS['setting']['fReplace']['maxRecursive']}", 1, true);
        return $message;
    }

    #匹配所有占位符名称，不允许有空格，输出结果到$match, $match[0]为包括{?...?}符号的匹配内容，$match[1]则为符号内的内容，实际有用的只有$match[1]
    preg_match_all('~\{\?([a-zA-Z0-9\-_\.\!\$]*?)\?\}~mU', $message, $match, PREG_PATTERN_ORDER, 0);
    #去除重复
    $match[1] = array_flip(array_flip($match[1]));

    // fPrint($match);
    $pairs = array();
    foreach ($match[1] as $k => $placeholder) {
        //从$vars中取值填补
        if(array_key_exists($placeholder, $vars)) {
            $pairs[$match[0][$k]] = $vars[$placeholder];
        } 
        //从数据库中取语言内容填补
        else {
            //从languages数据表中取对应的记录
            $query = $db->getArr(
                'languages',
                array(
                    "`name` = '{$placeholder}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            //如果没有取到，则试着从languages表中取默认语言的同名记录
            if(
                $query === false
                && $html->langCode !== $GLOBALS['deploy']['lang']
            ) {
                $query = $db->getArr(
                    'languages',
                    array(
                        "`name` = '{$placeholder}'",
                        "`lang` = '{$GLOBALS['deploy']['lang']}'"
                    ),
                    null,
                    1
                );
            }
            if($query !== false) {
                $pairs[$match[0][$k]] = \fDecode($query[0]['content']);
            }
        }
    }

    //对$message的内容做一次替换处理
    $message = strtr($message, $pairs);

    //对处理过的$message做一次正则提取
    preg_match_all('~\{\?([a-zA-Z0-9\-_\.\!\$]*?)\?\}~mU', $message, $check, PREG_PATTERN_ORDER, 0);
    if(
        empty($check[0]) //如果没有占位符则跳出递归
        || $check[0] === $compare //如果有占位符，但和上一次修改后的检查结果一致，则结束递归
    ) {
        // fPrint($message);
        return $message;
    } 
    //有增加新的变量，再进行一次递归
    else {
        $message = localRender($message, $vars, true, $recursion+1, $check[0]);
    }

    return $message;
}
?>