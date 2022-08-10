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
$char = new \meshal\xChar;

$html->loadCss('css/meshal.css');



if($_POST['submit']) { //有post提交，进行放逐
    //加载角色
    $char->load($_POST['id']);

    if(//检查这个角色是不是用户的
        $char->owner->uid !== $user->uid
        || is_null($char->id)
    ) {
        $html->redirectBack(
            'redirect.message.character.expelFailed'
        );
        \fDie();
    }

    if(//检查这个角色的stat是否为null(只有在营地休息的角色才可以放逐)
        !is_null($char->stat) 
    ) {
        $html->redirectBack(
            'redirect.message.character.expelFailed'
        );
        \fDie();
    }

    $char->owner->uid = null; //解除和用户的关系
    $stat = $char->save();

    if($stat == false){ //没有修改成功
        $html->redirectBack(
            'redirect.message.character.expelFailed'
        );
        \fDie();
    } else { //解绑成功
        //在interaction表中记录此次放逐
        $char->event(
            $user->uid,
            'expel'
        );

        $html->set('$charName', \fDecode($db->getArr(
            'characters',
            array(
                "`id` = '{$_POST['id']}'"
            ),
            null,
            1
        )[0]['name']), true);
        $html->redirectBack(
            'redirect.message.character.expelled'
        );
        \fDie();
    }
} 

elseif(!$_GET['id']) { //没有设置id
    $html->redirect(
        _ROOT.'character/',
        'redirect.message.character.expelFailed'
    );
    \fDie();
}

else { //默认显示角色并提供选项
    $html->loadTpl('character/expel/body.frame.html');
    $char->load($_GET['id']);

    if(//检查这个角色是不是用户的
        $char->owner->uid !== $user->uid
        || is_null($char->id)
    ) {
        $html->redirectBack(
            'redirect.message.character.expelFailed'
        );
        \fDie();
    }

    $html->set('$characterPreview',$char->render());
    $html->set('$charId', $_GET['id']);
    $html->set('$charName', $char->name, true);

    $html->output();
    \fDie();
}

?>