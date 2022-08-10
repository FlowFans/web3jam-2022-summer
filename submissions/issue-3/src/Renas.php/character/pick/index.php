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
$dice = new \meshal\xDice;
$char = new \meshal\xChar($user->uid);
$name = new \meshal\xName;

$html->loadCss('css/meshal.css');

//初始化slot
if($db->getArr(
    'character_slot',
    array(
        "`uid` = '{$user->uid}'"
    ),
    null,
    1
) === false) {
    //没有记录，创建一条记录
    $db->insert(
        'character_slot',
        array(
            'uid' => $user->uid,
            'slot' => 0
        )
    );
}

//对上传的头像做预览
if($_POST['preview']) {
    $update = array();
    if(!empty($_FILES['portrait'])) {
        $upload = new \xUpload($_FILES['portrait']);

        if($upload->uploaded) {
            // $portraitFilename = \fGenGuid();
            $upload->file_new_name_body = \fGenGuid();
            $upload->image_resize = true;
            $upload->image_x = $GLOBALS['meshal']['portrait']['width'];
            $upload->image_y = $GLOBALS['meshal']['portrait']['height'];
            $upload->image_ratio_crop = true;
    
            $upload->process(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait']);
            $update['portrait'] = \fEncode($upload->file_dst_name);
    
            //删除原来的头像
            localFetch();
            if(
                file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait)
                && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait)
            ) {
                unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait);
            }
        }
    }

    if(trim($_POST['name']) !== '') $update['name'] = \fEncode(trim($_POST['name']));
    $update['bio'] = \fEncode($_POST['bio']);

    //更新stage数据库中的角色数据
    $db->update(
        'character_stage',
        $update,
        array(
            "`uid` = '{$user->uid}'"
        ),
        1
    );

    $html->loadTpl('character/pick/body.frame.html');
    localFetch();
    $html->output();
    \fDie();
}

//有提交则处理提交信息
elseif($_POST['submit']) {
    $update = array();
    if(!empty($_FILES['portrait'])) {
        $portraitFilename = '';
        $upload = new \xUpload($_FILES['portrait']);
        if($upload->uploaded) {
            // $portraitFilename = \fGenGuid();
            $upload->file_new_name_body = \fGenGuid();
            $upload->image_resize = true;
            $upload->image_x = $GLOBALS['meshal']['portrait']['width'];
            $upload->image_y = $GLOBALS['meshal']['portrait']['height'];
            $upload->image_ratio_crop = true;

            $upload->process(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait']);
            $portraitFilename = $upload->file_dst_name;

            //删除原来的头像
            localFetch();
            if(
                file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait)
                && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait)
            ) {
                unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait);
            }
        }
    }
    localFetch();
    $char->owner->uid = $user->uid;
    $char->creator->uid = $user->uid;
    if(trim($_POST['name']) !== '') $char->name = trim($_POST['name']);
    $char->bio = $_POST['bio'];
    if($portraitFilename) $char->portrait = $portraitFilename;
    
    $char->save();
    //删除stage的角色
    $db->delete(
        'character_stage',
        array("`uid` = '{$user->uid}'"),
        1
    );
    //记录招募事件
    $char->event(
        $user->uid,
        'pick'
    );

    //渲染重定向页面
    $html->set('$charName', $char->name, true);
    $html->redirect(
        _ROOT.'character/',
        'pageTitle.characterList',
        'redirect.message.character.recruited'
    );
}

//没有提交则显示默认内容
else {
    $html->loadTpl('character/pick/body.frame.html');
    localFetch();
    $html->output();
    \fDie();
}


/**
 * 从stage读取已经存在的角色数据
 */
function localFetch() {
    global $char;
    global $db;
    global $user;
    global $html;

    $fetch = $db->getArr(
        'character_stage',
        array(
            "`uid` = {$user->uid}"
        ),
        null,
        1
    );

    //如果有记录，读取记录
    if($fetch !== false) {
        $char->import($fetch[0]);
        $char->viewerUrl = '#';
        $html->set('$characterPreview', $char->render(null, false));
        $html->set('$name', $char->name, true);
        $html->set('$bio', $char->bio, true);
    }
    //如果没有记录，则返回chargen页
    else {
        $html->redirect(
            _ROOT.'character/spawn/',
            'pageTitle.characterGenerate',
            'redirect.message.character.pickFailed'
        );
        \fDie();
    }
}
?>