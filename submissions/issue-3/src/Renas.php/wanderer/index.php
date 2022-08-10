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
$html->loadTpl('wanderer/body.frame.html', 'body');

$charCount = $db->getCount(
    'characters',
    array(
        "`ownerId` IS NULL"
    )
);

if($charCount > 0) {

    # 根据当前页取角色
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['character']['charactersPerPage'];

    $queryChars = $db->getArr(
        'characters',
        array(
            "`ownerId` IS NULL"
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

            #添加操作
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

            //招募
            $charRenderer->addCtrl(
                _ROOT."character/recruit/?id={$cur['id']}".\fBackUrl(),
                'button.characterController.recruit',
                'colorWhite1 bgOpaGreen1',
                'guest'
            );

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



$html->output();

\fDie();
?>