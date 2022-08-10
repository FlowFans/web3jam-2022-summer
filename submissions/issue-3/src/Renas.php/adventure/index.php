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
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('adventure/body.frame.html', 'body');

//从数据库中取记录，并基于冒险id去重
$adventureCount = $db->getCount(
    'adventure_chars',
    array(
        "`uid` = '{$user->uid}'"
    ),
    'adventureId',
    null,
    null,
    true
);

$charCount = $db->getCount(
    'characters',
    array(
        "`ownerId` = '{$user->uid}'"
    )
);

if($charCount > 0) { //如果用户有角色，渲染新冒险入口
    $html->set('$newAdventure', $html->readTpl('adventure/body.frame.new.html'));
    $html->set('$newAdventure.url', _ROOT.'adventure/new/');
} else { //如果用户没有角色，引导用户去创建角色
    $html->set('$newAdventure', $html->readTpl('guide/charEmpty.html'));
}


if($adventureCount > 0) {

    # 根据当前页取角色
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['adventure']['adventuresPerPage'];

    $queryAdventures = $db->getArr(
        'adventure_chars',
        array(
            "`uid` = '{$user->uid}'"
        ),
        array('adventureId'),
        "{$rowStart},{$GLOBALS['setting']['pager']['adventure']['adventuresPerPage']}",
        MYSQLI_NUM,
        'endTime',
        'DESC',
        null,
        true
    );


    #组装冒险记录列表
    if($queryAdventures !== false) {
        $adventures = '';
        $adventure = new \meshal\xAdventure;
        //拼装冒险数据
        foreach ($queryAdventures as $cur) {
            $adventure->load($cur['adventureId']);
            
            //如果冒险结束了，渲染查看log按钮
            if($adventure->sealed == 1) {
                $adventure->addCtrl(
                    _ROOT.'a/?id='.$adventure->id,
                    'button.adventure.viewLog',
                    null,array('any')
                );

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

            $adventures .= $adventure->render();
        }
        $html->set('$adventureList', $adventures);
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $adventureCount,
            $GLOBALS['setting']['pager']['adventure']['adventuresPerPage'],
            '?page={?$page?}'
        )
    );
} else {
    $html->set('$adventureList', '');
    $html->set('$pager', '');
}



$html->output();

\fDie();
?>