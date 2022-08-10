<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供用户信息的类
################################################


/**
 * 用户数据结构
 * $obj->uid //用户uid string
 * $obj->discord = stdClass obj(
 *  id => string //用户的discordId
 *  username => string //用户的discord名字
 *  avatar => string //用户的discord头像码（不是url）
 *  discriminator => string //用户的discord防重名编号
 *  public_flags
 *  banner
 *  banner_color
 *  accent_color
 *  locale => string //discord语言设置
 *  mfa_enabled
 * )
 * $obj->role = array( //用户的角色
 *  
 * )
 * 
 */
class xUser
{
    function __construct (
        bool $loginRequired = true
    ) {
        global $db;
        $this->db = $db;
        $this->html = new \xHtml;

        $this->uid = false;
        $this->discord = new \user\xDiscord($this);
        $this->wallet = array();

        if(!empty($GLOBALS['deploy']['network'])) { //遍历所有network配置，生成对应类的wallet对象
            foreach($GLOBALS['deploy']['network'] as $n => $cfg) {
                $cls = $cfg['class']['wallet'];
                $this->wallet[$n] = new $cls($this);
            }
        }

        $this->username = '';
        $this->avatar = '';
        $this->cp = '0';
        $this->charSlot = 0;
        $this->discord = false;
        $this->role = array();

        $this->inventory = new \user\xInventory($this);
        $this->facility = new \user\xFacility($this);
        $this->efx = new \user\xEfx($this);

        $this->unreadMessages = 0;

        if($this->checkSession()['discord'] === true) {
            $this->discord = $_SESSION['discordUser'];
            $this->signInDiscord($this->discord->id);
            // $this->fetch();
        }

        if(
            $loginRequired === true
            && $this->uid === false
        ) {
            $this->html->redirect(
                _ROOT."login.php",
                'pageTitle.signIn',
                'redirect.message.signInRequired'
            );
            \fDie();
        }

        $this->faucet();
        $this->fetch();
        $GLOBALS['cache']['logUser'] = $this->uid;

        $this->maintainanceCheck();
    }

    /**
     * 这是当新用户创建时进行的系列用户数据初始化操作
     */
    public function initialise() {
        foreach($GLOBALS['meshal']['initialisation']['facilities'] as $facilityName => $facilityLevel) {
            $this->facility->upgrade(
                $facilityName,
                $facilityLevel
            );
        }
    }

    /**
     * 保存用户数据
     */
    public function save() {
        $this->db->update(
            'users',
            array(
                'username' => \fEncode($this->username),
                'avatar' => \fEncode($this->avatar),
                'cp' => $this->cp
            ),
            array(
                "`uid` = '{$this->uid}'"
            ),
            1
        );

        foreach($this->wallet as $network => $parser) {
            $parser->save();
        }
    }

    /**
     * 重新加载用户数据
     * 
     * @return bool
     * 失败返回false
     */
    public function fetch() {
        if($this->uid === false) {
            \fLog("A uid is required to fetch the data");
            return false;
        }

        $query = $this->db->getArr(
            'users',
            array(
                "`uid` = '{$this->uid}'"
            ),
            null,
            1
        );

        //如果没有查到则返回false
        if($query === false) {
            \fLog("Didn't fetch the user(uid={$this->uid}) registration record in database");
            $this->uid = false; //安全起见，把uid清空
            $this->cp = '0';
            $this->charSlot = 0;
            return false;
        }

        //加载用户数据
        $this->username = \fDecode($query[0]['username']); //用户名
        if(
            //用户头像
            $query[0]['avatar'] !== '' 
            && !is_null($query[0]['avatar'])
        ) {
            $this->avatar = \fDecode($query[0]['avatar']);
        } else {
            //默认头像
            $this->avatar = "{?!dirCommonImg?}defaultAvatar.png";
        }
        $this->cp = $query[0]['cp']; //cp

        //加载或重取用户的钱包地址数据
        foreach($this->wallet as $network => $parser) {
            $parser->update();
        }

        //加载用户的角色槽位数
        $querySlot = $this->db->getArr(
            'character_slot',
            array(
                "`uid` = '{$this->uid}'"
            ),
            null,
            1
        );
        if($querySlot === false) {
            $this->charSlot = $GLOBALS['meshal']['character']['initialSlot'] + $this->efx->modifier['survivorSlots'];
        } else {
            $this->charSlot = $GLOBALS['meshal']['character']['initialSlot'] + $this->efx->modifier['survivorSlots'] + $querySlot[0]['slot'];
        }

        //加载用户消息计数
        $this->unreadMessages = $this->db->getCount(
            'messages',
            array(
                "`uid` = '{$this->uid}'",
                "`unread` = '1'"
            )
        );

        //处理与用户有关的冒险
        $adventure = new \meshal\xAdventure;
        $adventures = $this->db->getArr(
            'adventure_chars',
            array(
                "`uid` = '{$this->uid}'",
                "`sealed` = 0"
            ),
            'adventureId',
            null,null,null,null,null,true
        );
        if($adventures !== false) {
            foreach($adventures as $k => $data) {
                $adventure->load($data['adventureId']);
                $adventure->start();
            }
        }
        


        //设置与用户信息相关的显示变量
        \fSet('$username', $this->username); //用户名
        \fSet('$useravatar', $this->avatar); //头像
        \fSet('$cp', fRound($this->cp, 4)); //CP积分
        \fSet('$unreadMessages', $this->unreadMessages); //未读消息数量

        if($this->unreadMessages > 0) {
            \fSet('$navbar.userInfo.unreadMessageIndicator', $this->html->readTpl('navbar/userInfo.unreadMessage.html'));
            \fSet('$noUnreads', '');
        } else {
            \fSet('$navbar.userInfo.unreadMessageIndicator', '');
            \fSet('$noUnreads', 'hidden');
        }

        \fSet('$signInOrOut', 'logout'); //登出链接
        \fSet('$-signInOrOut', '{?button.signOut?}'); //登出链接文本
        \fSet('$navbar.userInfo', $this->html->readTpl('navbar/userInfo.html')); //用户信息模板
        

        //组装用户权限$this->group
        $query = $this->db->getArr(
            'user_role',
            array(
                "`uid` = '{$this->uid}'"
            ),
            'role'
        );
        
        if($query !== false) {
            foreach ($query as $k => $v) {
                $this->role[$v['role']] = $v['role'];
            }
        }

        $this->updateWallet();
        $this->efx->load();
    }

    /**
     * 用discord作为登录凭证
     */
    public function signInDiscord(
        string $discordId,
        bool $autoSignup = true
    ) {
        //从数据库查询对应的uid
        $query = $this->db->getArr(
            'user_discord',
            array(
                "`discordId` = '{$discordId}'"
            ),
            null,
            1
        );

        if($query === false) {
            if($autoSignup === true) {
                //创建一个用户
                $newUserId = $this->db->insert(
                    'users',
                    array(
                        'username' => \fEncode("{$this->discord->username}#{$this->discord->discriminator}"),
                        'avatar' => \fEncode("https://cdn.discordapp.com/avatars/{$this->discord->id}/{$this->discord->avatar}.webp")
                    )
                );

                $this->initialise();

                //写入关联的discordId
                $this->db->insert(
                    'user_discord',
                    array(
                        'uid' => $newUserId,
                        'discordId' => $this->discord->id
                    )
                );
                \fLog("The discordId {$discordId} doesn't associate with any user, a new user has been created (uid = {$newUserId})");
                $this->uid = $newUserId;
                return true;
            } else {
                //如果不需要自动创建而只是检查，那么在没有查到用户时就返回false
                \fLog("The discordId {$discordId} doesn't associate with any user");
                return false;
            }
        } else {
            $this->uid = $query[0]['uid'];
            $this->updateWallet();
            return true;
        }
    }

    /**
     * 对SESSION做检查，并返回检查结果
     */
    public function checkSession() {
        $status = array();

        //检查discord
        if(!$_SESSION['discordUser']) {
            \fLog('User\'s discord info is not in $_SESSION');
            $status['discord'] = false;
        } else {
            $status['discord'] = true;
        }

        return $status;
    }

    /**
     * 检查用户的角色，如果检查未通过则无法访问（出现跳转页）
     * 使用这个方法时，如果用户的角色满足传递的任意一个角色，就算通过
     * 
     * @param string ...$roles
     * 需要检查的角色名称
     */
    public function challengeRole(
        ...$roles
    ) {
        if(is_null($roles) || empty($roles)) {
            \fLog('Invalid param given for role challenging.');
            return;
        }

        if($this->checkRole($roles, false) === false) {
            $this->html->redirect(
                _ROOT,
                'pageTitle.home',
                'redirect.message.authFailed'
            );
            \fDie();
        }
    }

    /**
     * 检查用户的角色，如果检查未通过则无法访问（出现跳转页）
     * 使用这个方法时，用户的角色必须满足传递的所有角色才算通过
     * 
     * @param string ...$roles
     * 需要检查的角色名称，可用数组查多个（每个元素的键值是一个角色名）；也可给字符串查一个。
     */
    public function challengeRoles(
        ...$roles
    ) {
        if(is_null($roles) || empty($roles)) {
            \fLog('Invalid param given for role challenging.');
            return;
        }

        if($this->checkRole($roles, true) === false) {
            $this->html->redirect(
                _ROOT,
                'pageTitle.home',
                'redirect.message.authFailed'
            );
            \fDie();
        }
    }

    /**
     * 进行站点维护状态检查
     */
    private function maintainanceCheck() {
        if(
            $GLOBALS['deploy']['maintainance'] === true
            && $this->checkRole(array('admin')) === false
        ) {
            $this->html->output('maintainance');
            \fDie();
        }
    }

    /**
     * 为用户做cp水龙头的操作（赠送cp）
     */
    public function faucet() {
        if($this->uid === false) return; //不对未登录的用户做操作

        # 这段代码用于给用户赠送cp积分
        $query = $this->db->getArr(
            'user_faucet',
            array(
                "`uid` = '{$this->uid}'"
            ),
            null,
            1
        );
        if($query !== false) {//有查到claim记录
            if(bccomp($query[0]['claimed'], $GLOBALS['cp']['faucet']) == -1) { //没有全部领完则补足差额
                $this->cp = \fAdd(
                    $this->cp, 
                    \fSub(
                        $GLOBALS['cp']['faucet'],
                        $query[0]['claimed'],
                        $GLOBALS['cp']['decimal']
                    ),
                    $GLOBALS['cp']['decimal']
                );
                $this->save();
                $this->db->update(
                    'user_faucet',
                    array(
                        'claimed' => $GLOBALS['cp']['faucet']
                    ),
                    array(
                        "`uid` = {$this->uid}"
                    ),
                    1
                );
            }
        } else {//没有查到claim记录，给全额
            $this->cp = \fAdd(
                $this->cp,
                $GLOBALS['cp']['faucet'],
                $GLOBALS['cp']['decimal']
            );
            $this->save();
            $this->db->insert(
                'user_faucet',
                array(
                    'uid' => $this->uid,
                    'claimed' => $GLOBALS['cp']['faucet']
                )
            );
        }
    }
    
    /**
     * 检查用户是否有要求的角色，这个方法支持2种判断：AND|OR
     * 
     * @param array $roles
     * 用数组的方式传递要检查的角色，每个键值一个角色名
     * 
     * @param bool $allMatch
     * 如果为true，则做AND判断（必须拥有全部指定的角色才算通过）
     * 如果为false，则做OR判断（只要有任意一个指定的角色就算通过）
     * 默认为 false
     * 
     * @return bool
     * 如果检查通过，则返回true；反之返回false
     */
    public function checkRole(
        array $roles,
        bool $allMatch = false
    ) {
        if($allMatch === true) {
            //全部都满足才算检查通过
            foreach ($roles as $k => $r) {
                if(!isset($this->role[$r])) return false;
            }
            return true;
        } else {
            //任意一个满足即可
            foreach ($roles as $k => $r) {
                if(isset($this->role[$r])) return true;
            }
            return false;
        }
    }

    /**
     * 更新所有Wallet对象的属性
     */
    public function updateWallet() {
        if(!empty($GLOBALS['deploy']['network'])) { //遍历所有network配置，生成对应类的wallet对象
            foreach($GLOBALS['deploy']['network'] as $n => $cfg) {
                $this->wallet[$n]->load();
            }
        }
    }

    /**
     * 对这个用户做html渲染(tag版)
     * @param int $uid
     * 
     * @param string $target = ''
     * ###这个还没实现
     */
    public static function renderTag(
        int $uid,
        string $target = ''
    ) {
        global $db;

        $renderer = new \xHtml;
        $query = $db->getArr(
            'users',
            array(
                "`uid` = '{$uid}'"
            ),
            null,
            1
        );

        //查无此用户则返回false并记录错误
        if($query === false) {
            \fLog("The user({uid=$uid}) doesn't exist in database");
            return false;
        }

        //角色查看器URL
        // $renderer->set('--viewerUrl', "{?!dirRoot?}c/?id={$id}");
        $renderer->set('--target', $target);

        //基本数据
        $renderer->set('--userId', $uid);
        $renderer->set('--userName', \fDecode($query[0]['username']));

        $renderer->loadTpl('user/tag.html');

        return $renderer->render(
            'body'
        );
    }

    /**
     * 这是self::addCP()的alias
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|string $amount
     * 修改数量
     * 
     * @return int
     * 返回状态码
     * - 0：成功
     * - 1：用户记录不存在
     */
    public static function modCP (
        int $uid,
        $amount
    ) {
        return self::addCP($uid, $amount);
    }

    /**
     * 向指定uid的用户添加cp
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|string $amount
     * 修改数量
     * 
     * @return int
     * 返回状态码
     * - 0：成功
     * - 1：用户记录不存在
     */
    public static function addCP (
        int $uid,
        $amount
    ) {
        global $db;

        $query = $db->getArr(
            'users',
            array(
                "`uid` = '{$uid}'"
            ),
            null,
            1
        );

        if($query === false) {
            \fLog("User({$uid}) doesn't exist.");
            return 1;
        }

        $db->update(
            'users',
            array(
                'cp' => \fAdd($query[0]['cp'], $amount)
            ),
            array(
                "`uid` = '{$uid}'"
            ),
            1
        );
        return 0;
    }

    /**
     * 向指定uid的用户减少cp
     * 
     * @param int $uid
     * 用户uid
     * 
     * @param int|string $amount
     * 修改数量
     * 
     * @return int
     * 返回状态码
     * - 0：成功
     * - 1：用户记录不存在
     */
    public static function subCP (
        int $uid,
        $amount
    ) {
        global $db;

        $query = $db->getArr(
            'users',
            array(
                "`uid` = '{$uid}'"
            ),
            null,
            1
        );

        if($query === false) {
            \fLog("User({$uid}) doesn't exist.");
            return 1;
        }

        $db->update(
            'users',
            array(
                'cp' => \fSub($query[0]['cp'], $amount)
            ),
            array(
                "`uid` = '{$uid}'"
            ),
            1
        );
        return 0;
    }
}
?>