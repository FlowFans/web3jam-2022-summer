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

$html = new \xHtml;
$user = new \xUser;
$char = new \meshal\xChar;

$html->loadCss('css/meshal.css');


if($_POST['preview']) {
    // $char->load($_POST['id']);
    localFetch();
    if(!empty($_FILES['portrait'])) {
        $upload = new \xUpload($_FILES['portrait']);

        if($upload->uploaded) {
            $upload->file_new_name_body = \fGenGuid();
            $upload->image_resize = true;
            $upload->image_x = $GLOBALS['meshal']['portrait']['width'];
            $upload->image_y = $GLOBALS['meshal']['portrait']['height'];
            $upload->image_ratio_crop = true;
    
            $upload->process(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait']);
            $portrait = $upload->file_dst_name;
        }
    }
    
    if(trim($_POST['name']) !== '') $char->name = trim($_POST['name']);
    $char->bio = $_POST['bio'];
    if($portrait) $char->portrait = $portrait;

    //更新character_edit数据库中的角色数据
    $query = $db->getArr(
        'character_edit',
        array(
            "`charId` = '{$char->id}'"
        )
    );
    $tmp = $char->export();
    $tmp['charId'] = $tmp['id'];
    unset($tmp['id']);
    if($query === false) {
        $db->insert(
            'character_edit',
            $tmp
        );
    } else {
        //把原来的临时肖像删除
        if(
            $portrait
            && file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($query[0]['portrait']))
        ) {
            unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($query[0]['portrait']));
        }
        

        $db->update(
            'character_edit',
            $tmp,
            array(
                "`charId` = '{$char->id}'"
            ),
            1
        );
    }

    $html->loadTpl('character/edit/body.frame.html');
    localFetch();
    $html->output();
    \fDie();
} 

elseif(
    $_POST['submit']
) {
    $origChar = new \meshal\xChar;
    $origChar->load($_GET['id']);

    //更新用户提交的数据
    localFetch();
    
    //处理上传的肖像
    if(!empty($_FILES['portrait'])) {
        $upload = new \xUpload($_FILES['portrait']);

        if($upload->uploaded) {
            $upload->file_new_name_body = \fGenGuid();
            $upload->image_resize = true;
            $upload->image_x = $GLOBALS['meshal']['portrait']['width'];
            $upload->image_y = $GLOBALS['meshal']['portrait']['height'];
            $upload->image_ratio_crop = true;
    
            $upload->process(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait']);

            //把原来的肖像删除
            if(
                file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait)
                && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait)
            ) {
                unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$char->portrait);
            }

            $char->portrait = $upload->file_dst_name;
        }
    }

    $html->set('$charName', $origChar->name, true);

    $char->bio = $_POST['bio'];
    if(trim($_POST['name']) !== '') $char->name = trim($_POST['name']);

    if($char->save() === false) {
        $origChar->save(); //回档
        $html->redirectBack(
            'redirect.message.character.invalidEdit'
        );
    } else {
        //成功则删除原有character_edit记录，并跳转
        $db->delete(
            'character_edit',
            array(
                "`charId` = '{$char->id}'"
            ),
            1
        );

        //做event记录各项改动（如果有改动的话）
        $event = array();
        if($char->name != $origChar->name) $event['name'] = array(
            'old' => $origChar->name,
            'new' => $char->name
        );
        if($char->bio != $origChar->bio) $event['bio'] = array(
            'old' => $origChar->bio,
            'new' => $char->bio
        );
        if($char->portrait != $origChar->portrait) $event['portrait'] = array(
            'old' => $origChar->portrait,
            'new' => $char->portrait
        );

        $char->event(
            $user->uid,
            'edit',
            $event
        );

        //重定向
        $html->redirectBack(
            'redirect.message.character.editSubmitted'
        );
        \fDie();
    }
}

else {
    //预处理：删除之前上传但未被使用的头像
    $query = $db->getArr(
        'character_edit',
        array(
            "`charId` = '{$_GET['id']}'"
        ),
        null,
        1
    );
    if($query !== false) {
        //删除之前上传但未被使用的头像
        if(
            file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($query[0]['portrait']))
            && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($query[0]['portrait']))
        ) {
            unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].\fDecode($query[0]['portrait']));
        }
        //删除之前未被使用的编辑
        $db->delete(
            'character_edit',
            array(
                "`charId` = '{$_GET['id']}'"
            ),
            1
        );
    }

    //默认显示角色和编辑表单
    $html->loadTpl('character/edit/body.frame.html');
    localFetch();
    $html->output();
    \fDie();
}

/**
 * 取角色和用户数据并进行预渲染
 * 如果character_edit中有临时数据，则会用临时数据覆盖获取到的角色数据
 */
function localFetch() {
    global $char;
    global $user;
    global $db;
    global $html;

    $char->load($_GET['id']);

    //检查这个角色是否可被编辑
    if(
        $char->owner->uid !== $user->uid
        || !$user->uid
        || !$char->owner->uid
    ) {
        //如果条件不符合，重定向回角色列表
        $html->redirect(
            _ROOT.'character/',
            'pageTitle.myCharacters',
            'redirect.message.character.invalidEdit'
        );
        \fDie();
    }

    //检查character_edit中是否有之前的编辑
    $query = $db->getArr(
        'character_edit',
        array(
            "`charId` = '{$char->id}'"
        ),
        null,
        1
    );
    // fPrint($query);
    if($query !== false) { //如果有的话，用之前编辑的内容覆盖已有的
        //组装成可识别的数据
        $import = $char->export();
        if(!is_null($query[0]['portrait'])) $import['portrait'] = $query[0]['portrait'];
        if(!is_null($query[0]['name'])) $import['name'] = $query[0]['name'];
        if(!is_null($query[0]['bio'])) $import['bio'] = $query[0]['bio'];
        if(!is_null($query[0]['version'])) $import['version'] = $query[0]['version'];

        //将这些数据为$char赋值
        $char->import($import);
    }

    //对confirmCode做加密，$GLOBALS['deploy']['securityKey']作为salt
    // $html->set('$confirmCode', md5($user->uid.'edit'.$GLOBALS['deploy']['securityKey'].$char->id));

    $html->set('$backUrl', \fGet('_back'));
    $html->set('$name', $char->name, true);
    $html->set('$bio', $char->bio, true);
    $html->set('$characterPreview', $char->render());
    $html->set('$charId', $_GET['id']);
}
?>