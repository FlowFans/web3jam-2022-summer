
<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问

use meshal\char\xAdapter;

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
$user = new \xUser;
$target = new \user\xAdapter;

$user->challengeRole('admin');

$html->loadTpl('admin/addItemToDiscord.frame.html');

if($_POST['submit']) { //有提交
    $error = '';
    $userList = array();
    if(\fPost('discordId', '') != '') {
        $ids = \fLineToArray($_POST['discordId']);
    }

    if(!empty($ids)) {
        foreach ($ids as $k => $discordId) {
            $query = $db->getArr(
                'user_discord',
                array(
                    "`discordId` = '{$discordId}'"
                ),
                null,
                1
            );

            if($query !== false) {
                $userList[$discordId] = $query[0]['uid'];
            } else {
                $error .= $discordId.' not registered'.PHP_EOL;
                \fNotify(
                    'notify.admin.addItemToDiscord.failed',
                    'warn',
                    array(
                        '$discordId' => $discordId,
                        '$itemCode' => $_POST['itemCode'],
                        '$amount' => $_POST['amount']
                    )
                );
            }
        }
    }

    if(!empty($userList) && \fPost('itemCode', '') != '') {
        foreach ($userList as $discordId => $uid) {
            $targetCheck = $target->load($uid);
            $check = $target->inventory->add(
                $_POST['itemCode'],
                $_POST['amount']
            );

            if($check != 0) {
                \fNotify(
                    'notify.admin.addItemToDiscord.failed',
                    'warn',
                    array(
                        '$discordId' => $discordId,
                        '$itemCode' => $_POST['itemCode'],
                        '$amount' => $_POST['amount']
                    )
                );
                $error .= $discordId.' code='.$check.PHP_EOL;
            } else {
                \fNotify(
                    'notify.admin.addItemToDiscord.success',
                    'success',
                    array(
                        '$discordId' => $discordId,
                        '$itemCode' => $_POST['itemCode'],
                        '$amount' => $_POST['amount']
                    )
                );
                \fMsg(
                    $uid,
                    'reward',
                    'message.addItem',
                    array(
                        '$itemCode' => $_POST['itemCode'],
                        '$amount' => $_POST['amount']
                    )
                );
            }
        }
    }

    if($error != '') {
        $html->set('$showError', '');
        $html->set('$error', $error);
    } else {
        $html->set('$showError', 'hidden');
        $html->set('$error', '');
    }
} else {
    $html->set('$showError', 'hidden');
    $html->set('$error', '');
}

$html->output();
fDie();
?>