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

//只允许特定用户组访问
$user->challengeRole('admin', 'editor');


//这是一次提交
if($_POST['submit']) {
    \fLog('Received $_POST data:');
    \fLog(\fDump($_POST), 1, false);
    \fLog('Received $_GET data:');
    \fLog(\fDump($_GET), 1, false);
    /**
     * 此处的判断比较复杂，因此会使用多个if/else nesting
     * 首先需要判断是否是同名的占位符
     * 最后再根据这两者的组合判断执行对应的修改
     * 
     * 无原始名 && 无原始语言：是一次新增
     * 有原始名
     *      原始名 == 提交名：这是一次内容更新
     *      原始名 != 提交名：这是一次改名操作
     */
    
    if( //无原始名 && 无原始语言：是一次新增
        ($_POST['originalLang'] == '' || is_null($_POST['originalLang']))
        &&
        ($_POST['originalName'] == '' || is_null($_POST['originalName']))
    ) {
        if(localFetch($_POST['lang'], $_POST['name']) !== false) { //不允许用新建的方式覆盖一个已经存在的记录
            \fLog("Error: unable to override an existing language record by creating a new one with the same name ({$_POST['lang']}.{$_POST['name']})");
            \fNotify(
                'notify.editor.language.nameExists',
                'warn'
            );

            $html->loadTpl('editor/language/body.editor.html');
            $html->set('$originalLang', $_POST['originalLang']);
            $html->set('$originalName', $_POST['originalName']);
            $html->set('$name', $_POST['name']);
            $html->set('$content', $_POST['content']);
            $html->set('$langDisabled', '');
            localAssembler($_POST['lang']);
            $html->output();
            \fDie();
        }

        $stat = $db->insert(
            'languages',
            array(
                'name' => $_POST['name'],
                'lang' => $_POST['lang'],
                'content' => \fEncode($_POST['content'])
            )
        );
        if($stat === false) { //写入错误
            \fLog('Error while insert new data');
            \fNotify(
                'notify.editor.language.dbError',
                'fatal'
            );

            $html->loadTpl('editor/language/body.editor.html');
            $html->set('$originalLang', $_POST['originalLang']);
            $html->set('$originalName', $_POST['originalName']);
            $html->set('$name', $_POST['name']);
            $html->set('$content', $_POST['content']);
            $html->set('$langDisabled', '');
            localAssembler($_POST['originalLang']);
            $html->output();
            \fDie();
        } else { //重定向回列表页
            $html->set('$newLang', $_POST['lang']);
            $html->set('$newName', $_POST['name']);
            $html->redirect(
                'index.php',
                'pageTitle.editor.language',
                'redirect.message.editor.language.newEntry'
            );
            \fDie();
        }

    }

    if( //有原始名
        $_POST['originalName'] !== '' 
        && !is_null($_POST['originalName'])
    ) {
        if($_POST['originalName'] == $_POST['name']) { //原始名 == 提交名：是一次内容更新
            $stat = $db->update(
                'languages',
                array(
                    'content' => \fEncode($_POST['content'])
                ),
                array(
                    "`name` = '{$_POST['name']}'",
                    "`lang` = '{$_POST['originalLang']}'"
                ),
                1
            );

            if($stat === false) { //写入错误
                \fLog("Error: error while updating {$_POST['originalLang']}.{$_POST['name']}");
                \fNotify(
                    'notify.editor.language.dbError',
                    'fatal'
                );

                $html->loadTpl('editor/language/body.editor.html');
                $html->set('$originalLang', $_POST['originalLang']);
                $html->set('$originalName', $_POST['originalName']);
                $html->set('$name', $_POST['name']);
                $html->set('$content', $_POST['content']);
                $html->set('$langDisabled', 'disabled');
                localAssembler($_POST['originalLang']);
                $html->output();
                \fDie();
            } else {
                $html->redirect(
                    'index.php',
                    'pageTitle.editor.language',
                    'redirect.message.editor.language.successUpdate'
                );
                \fDie();
            }
        }

        else { //原始名 != 提交名：是一次改名
            if(localFetch($_POST['originalLang'], $_POST['name'])) { //如果修改的名字已经被占用，报错
                \fLog("Error: error while updating {$_POST['originalLang']}.{$_POST['name']}");
                \fNotify(
                    'notify.editor.language.changeNameFailed',
                    'warn'
                );

                $html->loadTpl('editor/language/body.editor.html');

                $html->set('$targetLang', $_POST['originalLang']);
                $html->set('$targetName', $_POST['name']);

                $html->set('$originalLang', $_POST['originalLang']);
                $html->set('$originalName', $_POST['originalName']);
                $html->set('$name', $_POST['name']);
                $html->set('$content', $_POST['content']);
                $html->set('$langDisabled', 'disabled');
                localAssembler($_POST['originalLang']);
                $html->output();
                \fDie();
            }

            else { //写入数据
                $stat = $db->update(
                    'languages',
                    array(
                        'name' => $_POST['name'],
                        'content' => \fEncode($_POST['content'])
                    ),
                    array(
                        "`name` = '{$_POST['originalName']}'",
                        "`lang` = '{$_POST['originalLang']}'"
                    ),
                    1
                );

                if($stat === false) { //写入错误，报错
                    \fLog("Error: error while updating {$_POST['originalLang']}.{$_POST['originalName']}");
                    \fNotify(
                        'notify.editor.language.dbError',
                        'fatal'
                    );

                    $html->loadTpl('editor/language/body.editor.html');
                    $html->set('$originalLang', $_POST['originalLang']);
                    $html->set('$originalName', $_POST['originalName']);
                    $html->set('$name', $_POST['name']);
                    $html->set('$content', $_POST['content']);
                    $html->set('$langDisabled', 'disabled');
                    localAssembler($_POST['originalLang']);
                    $html->output();
                    \fDie();
                } else {
                    $html->set('$lang', $_POST['originalLang']);
                    $html->set('$originalName', $_POST['originalName']);
                    $html->set('$newName', $_POST['name']);
                    $html->redirect(
                        'index.php',
                        'pageTitle.editor.language',
                        'redirect.message.editor.language.successRenamed'
                    );
                    \fDie();
                }
            }
        }
    }
}


//这是一次展示
else {
    //指定name参数的展示
    if($_GET['name']) {
        //取数据
        $query = localFetch($_GET['lang'], $_GET['name']);

        //数据为空则报错
        if($query === false) {
            $html->redirect(
                'index.php',
                'pageTitle.editor.language',
                'redirect.message.editor.language.error'
            );
            \fDie();
        }
    
        $html->loadTpl('editor/language/body.editor.html');
        $html->set('$originalLang', \fGet('lang', $GLOBALS['deploy']['lang']));
        $html->set('$originalName', $_GET['name']);
        $html->set('$name', $_GET['name']);
        $html->set('$content', \fDecode($query['content']));
        $html->set('$langDisabled', 'disabled');
        localAssembler(\fGet('lang', $GLOBALS['deploy']['lang']));
        $html->output();
        \fDie();
    }
    //一个空白新建页面
    else {
        $html->loadTpl('editor/language/body.editor.html');
        $html->set('$originalLang', '');
        $html->set('$originalName', '');
        $html->set('$name', '');
        $html->set('$content', '');
        $html->set('$langDisabled', '');
        localAssembler(\fGet('lang', $GLOBALS['deploy']['lang']));
        $html->output();
        \fDie();
    }
}



/**
 * 从数据库中获取1条语言数据
 * 如果有查到符合条件的数据，那么返回这一条数据（数组形式）；
 * 如果没有查到符合条件的数据，那么返回false
 */
function localFetch(
    string $lang,
    string $name
) {
    global $db;

    $query = $db->getArr(
        'languages',
        array(
            "`name` = '{$name}'",
            "`lang` = '{$lang}'"
        ),
        null,
        1
    );

    if($query === false) {
        return false;
    } else {
        return $query[0];
    }
}

//一些dup模块的标准化组装
function localAssembler($currentLang='') {
    global $html;

    // 组装语言代码下拉列表
    $langs = array();
    foreach ($GLOBALS['setting']['languageCode'] as $langOption => $settings) {
        $langs[] = array(
            '--lang' => $langOption,
            '--langCode' => "{?{$settings['name']}?}",
            '--selected' => $langOption == $currentLang ? 'selected' : ''
        );
    }

    $html->set(
        '$langCodeOptions',
        $html->duplicate(
            'editor/language/dup.option.langCode.html',
            $langs
        )
    );
}
?>