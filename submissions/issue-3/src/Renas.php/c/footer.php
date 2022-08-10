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
$GLOBALS['debug']['log'] = FALSE; //在本脚本中，临时关闭debug日志记录，以避免产生无意义的数据库查询记录。

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser(false);
$char = new meshal\xChar;

$html->loadCss('css/embeded.css');
$html->loadCss('css/meshal.css');
$html->loadTpl('sheet/sheet.footer.html');

// $user->challengeRole('admin', 'editor');

if($_GET['action'] == 'like') {

    $char->load($_GET['id']);

    $check = $db->getArr(
        'character_like',
        array(
            "`uid` = '{$user->uid}'",
            "`charId` = '{$_GET['id']}'"
        ),
        null,
        1
    );

    if($check === false) { //没有记录，创建新记录
        $db->insert(
            'character_like',
            array(
                'uid' => $user->uid,
                'charId' => $_GET['id'],
                'timestamp' => time()
            )
        );

        /**
         * 只有新的like才会发消息
         */

        //如果创作者不是自己，发消息
        if($char->creator->uid != $user->uid) {
            \fMsg( //给创作者发消息
                $char->creator->uid,
                'like',
                'message.liked.character.creator',
                array(
                    '$liker.username' => $user->username,
                    '$characterName' => $char->name
                )
            );
        }

        //如果创作者和拥有者不是同一人，且拥有者不是自己，给拥有者发消息
        if(
            $char->owner->uid !== $char->creator->uid
            && $char->owner->uid != $user->uid
        ) {
            \fMsg(
                $char->owner->uid,
                'like',
                'message.liked.character.owner',
                array(
                    '$liker.username' => $user->username,
                    '$characterName' => $char->name
                )
            );
        }
    } else { //有记录
        if($check[0]['cancelled'] == 0) { //这是一次取消like
            $db->update(
                'character_like',
                array(
                    'cancelled' => 1,
                    'timestamp' => time()
                ),
                array(
                    "`uid` = '{$user->uid}'",
                    "`charId` = '{$_GET['id']}'",
                ),
                1
            );
        } else { //这是一次like（以前取消过，但这次又赞了
            $db->update( 
                'character_like',
                array(
                    'cancelled' => 0,
                    'timestamp' => time()
                ),
                array(
                    "`uid` = '{$user->uid}'",
                    "`charId` = '{$_GET['id']}'",
                ),
                1
            );   
        }
    }

    \meshal\char\updateSort($char->id);
}

/**
 * 处理显示数据
 */
if(is_numeric($_GET['id'])) {
    $char->load($_GET['id']);
    $html->set('$hidden', '');

    //如果未登录则不可见like
    if(!is_numeric($user->uid)) {
        $html->set('$hidden', 'hidden');
    }

    //创作者数据
    if(is_null($char->creator->username)) {
        $html->set('--creatorName', '{?common.none?}');
    } else {
        $html->set('--creatorName', $char->creator->username);
    }

    //拥有者数据
    if(is_null($char->owner->username)) {
        $html->set('--ownerName', '{?common.none?}');
    } else {
        $html->set('--ownerName', $char->owner->username);
    }

    //处理like数据
    $html->set('$likes', $db->getCount(
        'character_like',
        array(
            "`charId` = '{$char->id}'",
            "`cancelled` != '1'"
        )
    ));

    $liked = $db->getArr(
        'character_like',
        array(
            "`charId` = '{$char->id}'",
            "`uid` = '{$user->uid}'"
        ),
        null,
        1
    );

    $html->set('$url', _ROOT.'c/footer.php?action=like&id='.$_GET['id']);

    if(
        $liked === false 
        || $liked[0]['cancelled'] == 1
    ) { //没有like过 或 like被cancel了
        $html->set('$likeIcon', 'like');
    } else { //like过
        $html->set('$likeIcon', 'liked');
    }
} else { //没加载到数据
    $html->set('$url', '');
    $html->set('$likeIcon', '');
    $html->set('--ownerName', '{?common.none?}');
    $html->set('--creatorName', '{?common.none?}');
    $html->set('$likes', '');
    $html->set('$hidden', 'hidden');
}



$html->output('embed');
\fDie();

?>