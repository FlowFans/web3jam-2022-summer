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
$user = new \xUser(false);

$html->loadCss('css/meshal.css');
$html->loadTpl('plaza/body.index.html', 'body');

$charCount = $db->getCount(
    'characters',
    array(
        "`ownerId` IS NOT NULL"
    )
);

if($charCount > 0) {

    # 根据当前页取角色
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['character']['charactersPerPage'];

    $queryChars = $db->getArr(
        'characters',
        array(
            "`ownerId` IS NOT NULL"
        ),
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['character']['charactersPerPage']}",
        MYSQLI_NUM,
        'sortScore',
        'DESC'
    );

    #组装角色列表
    if($queryChars !== false) {
        $characterList = '';
        $charRenderer = new meshal\xChar;
        //拼装角色数据
        foreach ($queryChars as $cur) {
            $charRenderer->load($cur['id']);
            # 添加操作
            // 分享
            $charRenderer->addCtrl(
                \fTwitterShareUrl(
                    $html->dbLang('twitter.shareCharacter'),
                    "{$GLOBALS['deploy']['siteRoot']}c/?id={$charRenderer->id}",
                    $GLOBALS['social']['twitter']['hashtag']['character'],
                    array('$charName' => $charRenderer->name)
                ),
                'button.characterController.tweet',
                'colorWhite1 bgOpaBlue2',
                'any',
                '_blank'
            );

            // 编辑
            $charRenderer->addCtrl(
                _ROOT.'c/?id={?--charId?}'.\fBackUrl(),
                'button.characterController.manage'
            );

            // 编辑
            $charRenderer->addCtrl(
                _ROOT.'character/edit/?id={?--charId?}'.\fBackUrl(),
                'button.characterController.edit'
            );

            // 放逐
            $charRenderer->addCtrl( 
                _ROOT.'character/expel/?id={?--charId?}'.\fBackUrl(),
                'button.characterController.expel',
                'colorWhite1 bgOpaRed1',
                array('owner'),
                null,
                false,
                array('adventure')
            );
            
            // 版本升级
            if(\fCheckVersion($charRenderer->version, $GLOBALS['meshal']['version']['character']) == -1) {
                $charRenderer->addCtrl(
                    _ROOT.'character/upgrade/?id={?--charId?}'.\fBackUrl(),
                    'button.characterController.upgrade',
                    'colorWhite1 bgOpaGreen1 nzButtonBreath',
                    null,
                    null,
                    true
                );
            }

            $characterList .= $charRenderer->render(null, true, $user);
        }

        $html->set('$characterList',$characterList);
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $charCount,
            $GLOBALS['setting']['pager']['character']['charactersPerPage'],
            '?page={?$page?}&_back='.\fGet('_back')
        )
    );
} else {
    $html->set('$characterList', '');
    $html->set('$pager', '');
}

//获取用户数据，组装引导内容
if($user->discord !== false) {
    $myChars = $db->getCount(
        'characters',
        array(
            "`ownerId` = '{$user->uid}'"
        )
    );
}

if($user->discord === false || $myChars == 0) { //用户未登录或没有拥有角色
    //创建newcomerGuides
    $html->set('$newcomerGuides', $html->readTpl('guide/charEmpty.html'));
} else {
    $html->set('$newcomerGuides', '');
}

$html->set('$eventList', xEvent::renderList());

$html->output();

\fDie();
?>