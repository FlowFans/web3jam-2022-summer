
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
$user = new \xUser;
$target = new \xUser;

$user->challengeRole('admin');

$html->loadTpl('admin/addCPtoDiscord.frame.html');

if($_POST['submit']) { //有提交
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
                    'notify.admin.addCPtoDiscord.failed',
                    'warn',
                    array(
                        '$discordId' => $discordId,
                        '$amount' => $_POST['amount']
                    )
                );
            }
        }
    }

    if(!empty($userList)) {
        foreach ($userList as $discordId => $uid) {
            $check = $db->update(
                'users',
                array(
                    'cp' => "`cp` + ".\fPost('cp', 0)
                ),
                array(
                    "`uid` = '{$uid}'"
                ),
                1,false
            );

            if($check == false|| \fPost('cp', 0) == 0) {
                \fNotify(
                    'notify.admin.addCPtoDiscord.failed',
                    'warn',
                    array(
                        '$discordId' => $discordId
                    )
                );
            } else {
                \fNotify(
                    'notify.admin.addCPtoDiscord.success',
                    'success',
                    array(
                        '$discordId' => $discordId,
                        '$amount' => \fPost('cp', 0)
                    )
                );
                \fMsg(
                    $uid,
                    'reward',
                    'message.addCP',
                    array(
                        '$amount' => \fPost('cp', 0)
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