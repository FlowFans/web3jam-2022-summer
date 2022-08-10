<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

$html->loadTpl('editor/language/body.frame.html', 'body');

$html->set('$search', \fGet('search', ''));
$html->set('$prefix', \fGet('prefix', ''));

$user->challengeRole('admin', 'editor');

$select = array();

//做语言筛选
if($_GET['lang']) {
    $select[] = "`lang` = '{$_GET['lang']}'";
}

//做关键词和前缀筛选
$prefix = \fGet('prefix', '');
$search = \fGet('search', '');
if($prefix != '') {
    if($search != '') {
        $select[] = "`name` LIKE '{$prefix}.%{$search}%'";
    } else {
        $select[] = "`name` LIKE '{$prefix}.%'";
    }
} else {
    if($search != '') {
        $select[] = "`name` LIKE '%{$search}%'";
    }
}

$rowCount = $db->getCount(
    'languages',
    $select
);

if($rowCount > 0) {
    # 根据当前页取语言
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['editor']['defaultRowsPerPage'];

    $queryLanguages = $db->getArr(
        'languages',
        $select,
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['editor']['defaultRowsPerPage']}",
        null,
        'name',
        'ASC'
    );

    #组装语言列表
    if($queryLanguages !== false) {
        //拼装语言数据
        $comp = array();
        foreach ($queryLanguages as $cur) {
            $comp[] = array(
                '--lang' => $cur['lang'],
                '--content' => \fDecode($cur['content']),
                '--name' => $cur['name'],
                '--protected' => $cur['protected']
            );
        }
        
        $html->set(
            '$languageList', 
            $html->duplicate(
                'editor/language/dup.row.html',
                $comp
            )
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $rowCount,
            $GLOBALS['setting']['pager']['editor']['defaultRowsPerPage'],
            '?page={?$page?}&lang={?$lang?}&prefix={?$prefix?}&search={?$search?}'
        )
    );
} else {
    $html->set('$languageList', '');
    $html->set('$pager', '');
}

$html->set('$prefix', \fGet('prefix', ''));
$html->set('$lang', \fGet('lang', ''));

# 组装前缀选择器
$langCodeMenu = array();
foreach ($GLOBALS['setting']['languageCode'] as $lang => $settings) {
    $langCodeMenu[] = array(
        '--url' => $lang == $_GET['lang'] ? '?lang=' : "?lang={$lang}",
        '--active' => $lang == $_GET['lang'] ? 'nzSelectionActive' : '',
        '--name' => "{?{$settings['name']}?}"
    );
}
$html->set('$langCodeMenu', $html->duplicate(
    'editor/language/dup.filterRow.langCode.html',
    $langCodeMenu
));

# 组装前缀选择器
$typeMenu = array();
foreach ($GLOBALS['setting']['languagePrefix'] as $langPrefix => $settings) {
    $typeMenu[] = array(
        '--url' => $langPrefix == $_GET['prefix'] ? '?prefix=' : "?prefix={$langPrefix}",
        '--active' => $langPrefix == $_GET['prefix'] ? 'nzSelectionActive' : '',
        '--name' => "{?{$settings['name']}?}"
    );
}
$html->set('$typeMenu', $html->duplicate(
    'editor/language/dup.filterRow.prefix.html',
    $typeMenu
));

$html->output();
\fDie();



?>