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

$html = new \xHtml;
$user = new \xUser(false);

$html->loadCss('css/meshal.css');

if(!isset($_GET['id'])) {
    $html->redirect(
        _ROOT.'adventure/',
        'pageTitle.adventure',
        'redirect.message.adventure.error'
    );
    \fDie();
}

$adventure = new \meshal\xAdventure;

if($adventure->load($_GET['id']) == false) { //冒险记录不存在，报错
    $html->redirect(
        _ROOT.'adventure/',
        'pageTitle.adventure',
        'redirect.message.adventure.error'
    );
    \fDie();
}

$html->set('$adventureId', $adventure->id);
$html->set('$adventureName', "{?adventureName.{$adventure->templateName}?}");
$html->set('$coverImage',
    (
        is_null($adventure->coverImage) || $adventure->coverImage == ''
        || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$adventure->coverImage)
    )
        ? $GLOBALS['deploy']['siteRoot']."{?!dirImg?}adventureCover.default.jpg"
        : $GLOBALS['deploy']['siteRoot'].DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$adventure->coverImage
);
$html->set('$description', "{?adventureDesc.{$adventure->templateName}?}");

$html->headInject('adventure/detail/head.meta.html');

if($adventure->sealed == 1) {
    $log = $adventure->renderLog();
    if($log === false) {
        $html->set('$log', '{?common.adventure.logObsolete?}');
    } else {
        $html->set('$log', $log);
        $adventure->addCtrl(
            \fTwitterShareUrl(
                $html->dbLang('twitter.shareAdventure'),
                "{$GLOBALS['deploy']['siteRoot']}a/?id={$adventure->id}",
                $GLOBALS['social']['twitter']['hashtag']['adventure'],
                array('$adventureName' => $html->dbLang("adventureName.{$adventure->templateName}"))
            ),
            'button.adventureController.tweet',
            'colorWhite1 bgOpaBlue2',
            'any',
            '_blank'
        );
    }
} else {
    $html->set('$log', '{?common.adventure.notSealed?}');
}

$html->set('$adventure', $adventure->render(true, $user, '', true));

$html->loadTpl(
    'adventure/detail/body.frame.html'
);

$html->output();
\fDie();
?>