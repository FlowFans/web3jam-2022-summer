<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供HTML渲染的类
################################################

/**
 * 说明
 * 
 * 这个类在渲染HTML时，会取多个数据来源并合并成一个替换占位符用的数组。合并时的顺序如下，后者会覆盖前者中同键名的键值
 *  + array $this->var
 *  + array $GLOBAL['cache']['var']
 *  + array $this->lang
 * 因此，在某个未实例化本类的脚本中（比如另一个类中）要控制输出HTML中的变量，那么可以通过修改$GLOBAL['cache']['var']（比如用\fSet()）将变量传递进来。
 */

/**
 * 常用属性
 * $this->langCode //目前渲染器使用的语言代码
 */

/**
 * 常用方法
 * 
 * 设置一个变量
 * $obj->set(
 *  string $varName, //变量名称
 *  mixed $varValue //变量值
 * );
 * 
 * 加载一个模板
 * $obj->loadTpl(
 *  string $tplName, //模板文件名称
 *  string $snippetGroup, //加载代码片段的分组
 *  integer $position //加载代码片段在分组中的位置
 * );
 * 
 * 组装一个翻页器（通常给分页查询使用）
 * $obj->pager(
 *  int $currentPage, //当前翻页器所在的页码
 *  int $totalRows, //总共有多少条目
 *  int $rowsPerPage, //每页显示多少条目
 *  string $baseUrl, //每个分页的基础URL，URL中通常要带上转义符{?$page?}，比如'view.php?page={?$page?}'
 *  int $offset=3 //显示偏移当前页码±多少页的页码
 * );
 * 
 * 加载语言配置
 * $obj->loadLang(
 *  string $langFile //语言文件名称
 * );
 * 
 * 渲染输出一般html
 * $obj->output();
 * 
 * 渲染输出redirect
 * $obj->redirect(
 *  string $targetUrl, //跳转目标url
 *  string $targetName, //跳转目标的名称（需要在语言包中预定义）
 *  string $message //跳转时的提示文字（需要在语言包中预定义）
 * )
 */

class xHtml
{
    /**
     * @param string $lang
     * 初始化时，可以设置语言代码
     * 
     * @param string $skin
     * 初始化时，可以设置主题代码
     */
    function __construct(
        $lang = NULL,
        $skin = NULL
    ) {
        // $this->db = new \xDatabase;
        global $db;
        $this->db = $db;

        //存储渲染好的html代码
        $this->renderHtml = '';

        //语言代码设置
        if(is_null($lang)) {
            $this->langCode = $GLOBALS['deploy']['lang'];
        } else {
            $this->langCode = $lang;
        }
        
        //皮肤路径设置
        switch (TRUE) {
            case (is_null($skin)):
                $this->dirSkin = _ROOT.DIR_SKIN.$GLOBALS['deploy']['skin'].'/';
                $this->relDirSkin = DIR_SKIN.$GLOBALS['deploy']['skin'].'/';
                break;
            
            case (is_dir(_ROOT.DIR_SKIN.$skin)):
                $this->dirSkin = _ROOT.DIR_SKIN.$GLOBALS['deploy']['skin'].'/';
                $this->relDirSkin = DIR_SKIN.$GLOBALS['deploy']['skin'].'/';
                break;

            default:
                $this->dirSkin = _ROOT.DIR_SKIN.$skin.'/';
                $this->relDirSkin = DIR_SKIN.$skin.'/';
                break;
        }

        //存储准备用于渲染的html代码片段，每个元素存储1个片段，元素的顺序就是片段的顺序
        $this->snippets = array(
            'header' => array(), //html头
            'navbar' => array(), //导航栏
            'body' => array(), //html主体
            'notify' => array(), //通知
            'footer' => array(), //html脚
            'redirect' => array() //重定向用的html内容
        );

        //用于注入到头部的代码片段
        $this->headInjection = '';

        //用于替换html内容的变量数组
        $this->var = array();

        //用于替换html内容中的语言变量
        // $this->lang = array();

        //用于存储css样式调用，会在$this->render()时渲染
        $this->css = array();

        //用于存储script调用，会在$this->render()时渲染
        $this->script = array();

        //初始化常用变量
        $this->set('!buildVersion', $GLOBALS['deploy']['buildVersion']); //内部使用的build号
        $this->set('!charset', $GLOBALS['deploy']['charset']); //设定渲染的charset
        $this->set('!timezone', $GLOBALS['deploy']['timeZone']); //设定时区

        $this->set('!dirRoot', _ROOT); //根目录

        if(\fGet('_back', '') != '') { //返回URL
            $this->backUrl = _ROOT.\fDecode($_GET['_back']);
            $this->set('!back', $this->backUrl);
        } else {
            $this->set('!back', _ROOT);
            $this->backUrl = _ROOT;
        }

        $this->set('!dirSkin', $this->dirSkin); //当前皮肤主题的目录
        $this->set('!relDirSkin', $this->relDirSkin); //当前皮肤主题的目录(无_ROOT)

        $this->set('!dirScript', $this->dirSkin.'_scripts/'); //皮肤专属脚本目录
        $this->set('!relDirScript', $this->relDirSkin.'_scripts/'); //皮肤专属脚本目录

        $this->set('!dirImg', $this->dirSkin.'_images/'); //皮肤专属图片素材目录
        $this->set('!relDirImg', $this->relDirSkin.'_images/'); //皮肤专属图片素材目录

        $this->set('!dirCommonImg', _ROOT.DIR_COMMONIMAGE); //公共图片素材目录

        $this->set('!siteRoot', isset($GLOBALS['deploy']['siteRoot']) ? $GLOBALS['deploy']['siteRoot'] : ''); //站点根路径
        $this->set('!siteVersion', (isset($GLOBALS['deploy']['version']) ? $GLOBALS['deploy']['version'] : '').(isset($GLOBALS['deploy']['versionCode']) ? ' '.$GLOBALS['deploy']['versionCode'] : '')); //站点版本号
        $this->set('!siteName', isset($GLOBALS['deploy']['siteName']) ? $GLOBALS['deploy']['siteName'] : ''); //站点完整名称
        $this->set('!siteNameAbbr', isset($GLOBALS['deploy']['siteNameAbbr']) ? $GLOBALS['deploy']['siteNameAbbr'] : ''); //站点名称缩写
        $this->set('!copyrightYear', date('Y')); //版权所有作用的当前年份
        $this->set('!siteOwner', isset($GLOBALS['deploy']['siteOwner']) ? $GLOBALS['deploy']['siteOwner'] : ''); //站点所有者
        $this->set('!siteLogo', isset($GLOBALS['deploy']['siteLogo']) ? $GLOBALS['deploy']['siteLogo'] : 'defaultSiteLogo.png'); //站点logo
        
        $this->set('!discordInvitation', $GLOBALS['social']['discord']['invitation']); //discord邀请链接

        //初始化默认变量
        $this->set('$useravatar', _ROOT.DIR_COMMONIMAGE.'defaultAvatar.png'); //默认头像
        $this->set('$username', '{?common.defaultUsername?}'); //默认用户名
        $this->set('$signInOrOut', 'login');//默认登录链接
        $this->set('$-signInOrOut', '{?button.signIn?}'); //默认登录链接的文本
        $this->set('$navbar.userInfo', $this->readTpl('navbar/signIn.html')); //默认角色信息容器和登录入口模板页
    }

    /**
     * 读取并加载一个模板
     * 
     * @param string $tplName
     * 加载的模板文件名（不用带上相对路径），会根据皮肤设置指定目录
     * 
     * @param string|null $snippetGroup
     * 加载到哪一组代码片段中，如果为null，那么就会加载到body组中。
     * 可以定义诸如："header", "footer", "notification"等
     * 默认为null
     * 
     * @param integer $position
     * 加载到指定的位置，如果为null那么加载到所在分组的末尾。
     * 如果为-1，那么加载到所在分组的最前。
     * 默认为null
     */
    public function loadTpl(
        $tplName,
        $snippetGroup = NULL,
        $position = NULL
    ) {
        //决定snippet分组，默认为'body'
        if(is_null($snippetGroup)) {
            $group = 'body';
        } else {
            $group = $snippetGroup;
        }

        if(is_null($position)) {
            $this->snippets[$group][] = $this->readTpl($tplName);
        } else {
            \fArrayInsert(
                $this->snippets[$group],
                $position,
                $this->readTpl($tplName)
            );
        }
    }

    /**
     * 读取并注入一个头部html代码
     * 常用于诸如js代码到头部
     * 
     * @param string $tplName
     * 加载的代码文件名（不用带上相对路径），会根据皮肤设置指定目录
     */
    public function headInject(
        string $tplName
    ) {
        $this->headInjection .= $this->readTpl($tplName);
    }

    /**
     * 在$this->css中加入需要加载的css样式
     * 
     * @param string $cssFile
     * 需要加载的css文件名
     * 
     * @param integer $position
     * 加载到指定的位置，如果为null那么加载到css列表的末尾。
     * 如果为-1，那么加载到最前。
     * 默认为null
     */
    public function loadCss(
        $cssFile,
        $position = NULL
    ) {
        if(file_exists($this->dirSkin.'/'.$cssFile)) {
            if(is_null($position)) {
                $this->css[] = $cssFile.'?v='.$GLOBALS['deploy']['buildHash'];
            } else {
                \fArrayInsert(
                    $this->css,
                    $position,
                    $cssFile.'?v='.$GLOBALS['deploy']['buildHash']
                );
            }
        } else {
            \fLog("css file doesn't exist: ".$cssFile);
            return false;
        }
    }

    /**
     * 在$this->script中加入需要加载的script样式
     * 请注意：这个方法加载的script是在皮肤目录下的
     * 
     * @param string $scriptFile
     * 需要加载的script文件名
     * 
     * @param integer $position
     * 加载到指定的位置，如果为null那么加载到script列表的末尾。
     * 如果为-1，那么加载到最前。
     * 默认为null
     */
    public function loadScript(
        $scriptFile,
        $position = NULL
    ) {
        if(file_exists($this->dirSkin.'scripts/'.$scriptFile)) {
            if(is_null($position)) {
                $this->script[] = $this->dirSkin.'scripts/'.$scriptFile;
            } else {
                \fArrayInsert(
                    $this->script,
                    $position,
                    $this->dirSkin.'scripts/'.$scriptFile
                );
            }
        } else {
            \fLog("script file doesn't exist: ".$this->dirSkin.'scripts/'.$scriptFile);
            return false;
        }
    }

    /**
     * 在$this->script中加入需要加载的通用script样式
     * 请注意：这个方法加载的script是在DIR_SCRIPT目录下的
     * 
     * @param string $scriptFile
     * 需要加载的script文件名
     * 
     * @param integer $position
     * 加载到指定的位置，如果为null那么加载到script列表的末尾。
     * 如果为-1，那么加载到最前。
     * 默认为null
     */
    public function loadCommonScript(
        $scriptFile,
        $position = NULL
    ) {
        if(file_exists(_ROOT.DIR_SCRIPT.$scriptFile)) {
            $this->script[] = $scriptFile;
            if(is_null($position)) {
                $this->script[] = $scriptFile;
            } else {
                \fArrayInsert(
                    $this->script,
                    $position,
                    $scriptFile
                );
            }
        } else {
            \fLog("script file doesn't exist: ".$scriptFile);
            return false;
        }
    }

    /**
     * 遍历一个二维数组的每个子数组，将每个子数组中的数据填充进模板，并返回生成的结果。这个方法常用于批量生成格式相同的元素。
     * 规范：一般来说，这种批量复用的模板中，变量命名为"--varName"。这不是一个严格的规范，但这样会更易于识别和命名。
     * 
     * @param string $tplFile
     * 加载的模板文件名（不用带上相对路径），会根据皮肤设置指定目录。
     * 
     * @param array $varSets
     * 用于替换变量的二维数组。
     * 注意，数组中每个子数组是一组完整待替换的信息，子数组元素中，键名等于模板中要替换的变量名，值等于要替换的变量值。比如：
     *  $varSets = array(
     *      array('--cssPath' => '1.css', '--scriptPath' => 'a.js'),
     *      array('--cssPath' => '2.css', '--scriptPath' => 'b.js'),
     *      ...
     *  );
     * 
     * @return string
     * 返回复用组装完毕的内容
     */
    public function duplicate(
        string $tplFile,
        array $varSets
    ) {
        $tpl = $this->readTpl($tplFile);
        $return = '';

        //进行增量替换
        foreach ($varSets as $set => $arr) {
            if(is_array($arr)) {
                $return .= fReplace(
                    $tpl,
                    $arr
                );
            }
        }
        return $return;
    }

    /**
     * 加载一个模板，用传递的变量组对其进行替换，然后返回
     * 这个方法常用于快速返回一个无需打印的html语法片段
     * 
     * @param string $tplFile
     * 加载的模板文件名（不用带上相对路径），会根据皮肤设置指定目录。
     * 
     * @param array $vars
     * 要替换的变量组，键名等于模板中要替换的变量名，值等于要替换的变量值。
     * 
     * @return string
     * 返回替换好的html语法片段
     */
    public function quickRender(
        string $tplFile,
        array $vars
    ) {
        $tpl = $this->readTpl($tplFile);
        return fReplace(
            $tpl,
            $vars
        );
    }

    /**
     * 读取一个模板（但不加载到本对象的缓存里），并返回它的内容
     * 
     * @param string $tplFile
     * 加载的模板文件名（不用带上相对路径），会根据皮肤设置指定目录
     * 
     * @return string
     * 返回读取到的模板内容
     */
    public function readTpl(
        $tplFile
    ) {
        \fLog('reading tpl: '.$tplFile);
        $return = \fLoadFile(
            $this->dirSkin.'/'.$tplFile
        );

        return $return;
    }

    /**
     * 从数据库中取对应的语言内容并返回
     * 
     * @param string $placeholder
     * 语言占位符的名称（不包括{??}）
     * 
     * @return string
     * 返回从数据库中取到的语言
     * 如果没有取到，则会尝试从默认语言中取
     * 如果还没有取到，返回null
     */
    public function dbLang(
        string $placeholder
    ) {
        //从数据库中取语言数据
        $query = $this->db->getArr(
            'languages',
            array(
                "`lang` = '{$this->langCode}'",
                "`name` = '{$placeholder}'"
            ),
            null,
            1
        );
        
        //如果没有取到，则试着从languages表中取默认语言的同名记录
        if(
            $query === false
            && $this->langCode !== $GLOBALS['deploy']['lang']
        ) {
            $query = $this->db->getArr(
                'languages',
                array(
                    "`name` = '{$placeholder}'",
                    "`lang` = '{$GLOBALS['deploy']['lang']}'"
                ),
                null,
                1
            );
        }

        if($query !== false) {
            return \fDecode($query[0]['content']);
        } else {
            \fLog("Error: no language entry found of {$placeholder}");
            return null;
        }
    }

    /**
     * 在数据库中检查对应的语言内容是否存在
     * 
     * @param string $placeholder
     * 语言占位符的名称（不包括{??}）
     * 
     * @return int
     * 返回检查状态码
     * - 0：不存在
     * - 1：在默认语言配置中存在
     * - 2：在当前语言配置中存在
     */
    public function existLang (
        string $placeholder
    ) {
        //从数据库中取语言数据
        $query = $this->db->getArr(
            'languages',
            array(
                "`lang` = '{$this->langCode}'",
                "`name` = '{$placeholder}'"
            ),
            null,
            1
        );

        if($query !== false) {
            //当前语言配置中存在
            return 2;
        }

        //如果没有取到，则试着从languages表中取默认语言的同名记录
        if($this->lang !== $GLOBALS['deploy']['lang']) {
            $query = $this->db->getArr(
                'languages',
                array(
                    "`name` = '{$placeholder}'",
                    "`lang` = '{$GLOBALS['deploy']['lang']}'"
                ),
                null,
                1
            );
        }

        if($query !== false) {
            //在默认语言配置中存在
            return 1;
        }

        //不存在
        return 0;
    }

    /**
     * 设置渲染后的页面名称
     * 
     * @param string $title
     * 这个页面的名称（需要在common.ini中预先定义）
     */
    public function title(
        string $title
    ) {
        $this->set('!pageTitle',"{?{$title}?}");
    }

    /**
     * 将一个待替换的变量名和值写入$this->var中
     * 
     * @param string $key
     * 变量名
     * 
     * @param mixed $value
     * 变量值
     * 
     * @param bool $htmlEncode
     * 是否使用htmlentities对该值做预处理
     * 默认为false
     * 
     * @param any $nullDefault = ''
     * 如果变量为空或者未设置，那么使用此默认值
     */
    public function set(
        string $key,
        $value,
        bool $htmlEncode = false,
        $nullDefault = ''
    ) {
        if($htmlEncode === true) $value = htmlentities($value);
        $this->var[$key] = (!isset($value) || is_null($value)) ? $nullDefault : $value;
    }

    /**
     * 渲染并返回一个翻页器（已知总条目数，根据给定的条件自动分页）
     * 
     * @param integer $currentPage
     * 当前停留的页码
     * 
     * @param integer $totalRows
     * 总共条目数
     * 
     * @param integer $rowsPerPage
     * 每页有多少条目
     * 
     * @param string $baseUrl
     * 每个分页链接的基础URL，转义符 {?page?} 会替换为页码。比如：_ROOT.'something/index.php?page={?page?}'
     * 
     * @param integer $offset
     * 显示偏移当前页码±多少页的页码。比如，设为3时，如果当前页码为8，就会显示 |< < 5 6 7 8 9 10 11 > >|
     * 
     * @return string
     * 返回渲染好的分页器
     */
    public function pager(
        int $currentPage,
        int $totalRows,
        int $rowsPerPage,
        string $baseUrl,
        int $offset = 3
    ) {
        //如果一页就够显示，不显示翻页器
        if($totalRows <= $rowsPerPage) return '';

        return $this->pagerIndicator(
            $currentPage,
            ceil($totalRows / $rowsPerPage),
            $baseUrl,
            $offset
        );
    }

    /**
     * 渲染并返回一个翻页器（已知总页数）
     * 这个方法通常由$this->pager()调用，但也可以被外部直接调用
     * 
     * @param integer $currentPage
     * index-1的整数，代表当前用户所处的页号
     * 
     * @param integer $totalPages
     * 总共的页数
     * 
     * @param string $baseUrl
     * 每个分页链接的基础URL，转义符 {?$page?} 会替换为页码。比如：_ROOT.DIR_WIDGET.'something/index.php?page={?$page?}'
     * 
     * @param integer $offset
     * 显示偏移当前页码±多少页的页码。比如，设为3时，如果当前页码为8，就会显示 |< < 5 6 7 8 9 10 11 > >|
     * 
     * @return string
     * 返回渲染好的分页器
     */
     public function pagerIndicator(
        int $currentPage,
        int $totalPages,
        string $baseUrl,
        int $offset = 3
    ) {
        # 当前页码 > 最大页码
        if(
            $currentPage > $totalPages
        ) {
            return '';
        }
        $tplPager = $this->readTpl('pager/frame.html');
        $tplPagerIndicator = $this->readTpl('pager/dup.indicator.html');
        $tplPagerCurrent = $this->readTpl('pager/dup.current.html');


        $indicator = '';

        # 组装分页器：到第一页
        if($currentPage > 1) {
            $indicator .= \fReplace(
                $tplPagerIndicator,
                array(
                    '$pager.url' => \fReplace(
                        $baseUrl,
                        array('$page' => 1)
                    ),
                    '$pager.lang' => '{?button.pager.first?}'
                )
            );
        }

        # 组装分页器：到上一页
        if($currentPage > 1) {
            $indicator .= \fReplace(
                $tplPagerIndicator,
                array(
                    '$pager.url' => \fReplace(
                        $baseUrl,
                        array('$page' => $currentPage - 1)
                    ),
                    '$pager.lang' => '{?button.pager.previous?}'
                )
            );
        }

        # 组装分页器：向前偏移
        if($currentPage - $offset < 1) {
            $prevPage = 1;
        } else {
            $prevPage = $currentPage - $offset;
        }
        for ($i=$prevPage; $i < $currentPage; $i++) { 
            $indicator .= \fReplace(
                $tplPagerIndicator,
                array(
                    '$pager.url' => \fReplace(
                        $baseUrl,
                        array('$page' => $i)
                    ),
                    '$pager.lang' => $i
                )
            );
        }

        # 组装分页器：当前页
        $indicator .= \fReplace(
            $tplPagerCurrent,
            array('$pager.currentPage' => $currentPage)
        );
        
        # 组装分页器：向后偏移
        if($currentPage + $offset > $totalPages) {
            $morePage = $totalPages;
        } else {
            $morePage = $currentPage + $offset;
        }
        for ($i=$currentPage + 1; $i <= $morePage; $i++) { 
            $indicator .= \fReplace(
                $tplPagerIndicator,
                array(
                    '$pager.url' => \fReplace(
                        $baseUrl,
                        array('$page' => $i)
                    ),
                    '$pager.lang' => $i
                )
            );
        }

        # 组装分页器：到下一页
        if($currentPage < $totalPages) {
            $indicator .= \fReplace(
                $tplPagerIndicator,
                array(
                    '$pager.url' => \fReplace(
                        $baseUrl,
                        array('$page' => $currentPage +1)
                    ),
                    '$pager.lang' => '{?button.pager.next?}'
                )
            );
        }
        
        # 组装分页器：到最后页
        if($currentPage < $totalPages) {
            $indicator .= \fReplace(
                $tplPagerIndicator,
                array(
                    '$pager.url' => \fReplace(
                        $baseUrl,
                        array('$page' => $totalPages)
                    ),
                    '$pager.lang' => '{?button.pager.last?}'
                )
            );
        }

        return \fReplace(
            $tplPager,
            array('$pager' => $indicator)
        );
    }
    
    /**
     * 渲染html并返回渲染结果（但不输出）
     * 
     * @param various ...$snippetGroup
     * 这里可以传入多个参数，来决定要渲染哪些代码片段组
     */
    public function render(
        ...$snippetGroup
    ) {
        ################################################
        #渲染前的准备
        ################################################

        //加载通用语言设置
        // $this->loadLang('common.ini');

        //加载通用css到最前，因此它可以被之后加载的css覆盖
        $this->loadCss('css/common.css', -1);

        //加载通用js到最前
        // $this->loadScript('common.js', -1);

        //注入代码片段到head标签内
        $this->set('!headInjection', $this->headInjection);


        //如果没有设置过pageTitle, 那么此时使用默认pageTitle
        if(!isset($this->var['!pageTitle'])) {
            $this->title('pageTitle.default');
        }


        ################################################
        #组装复用元素
        ################################################

        //拼装CSS加载器
        if(!empty($this->css)) {
            $cssSet = array();
            //把$cssSet组装成$this->duplicate()可识别的数组
            foreach ($this->css as $k => $v) {
                $cssSet[] = array('--cssPath' => $v);
            }
            $this->set(
                '!cssLoader',
                $this->duplicate(
                    'header/cssLoader.html',
                    $cssSet
                )
            );
        } else {
            $this->set('!cssLoader','');
        }
        

        //拼装script加载器
        if(!empty($this->script)) {
            $scriptSet = array();
            //把$scriptSet组装成$this->duplicate()可识别的数组
            foreach ($this->script as $k => $v) {
                $scriptSet[] = array('--scriptPath' => $v);
            }
            $this->set(
                '!scriptLoader',
                $this->duplicate(
                    'header/scriptLoader.html',
                    $scriptSet
                )
            );
        } else {
            $this->set('!scriptLoader','');
        }
        
        //如果$GLOBALS['cache']['notify']不为空，那么拼装通知组件
        if(!empty($GLOBALS['cache']['notify'])) {
            $this->loadTpl('notify/container.html','notify');

            $notify = array();
            //把$notify组装成$this->duplicate()可识别的数组
            foreach ($GLOBALS['cache']['notify'] as $k => $notification) {
                $notify[] = array(
                    '--notification' => \fReplace(
                        $this->dbLang($notification['lang']),
                        $notification['vars'],
                    ),
                    '--notifyType' => $notification['type']
                );
            }
            $this->set(
                '!notification',
                $this->duplicate(
                    'notify/dup.row.html',
                    $notify
                )
            );
        }

        ################################################
        #准备渲染
        ################################################

        //通过$snippetGroup来决定要渲染哪些代码片段
        $renderQueue = array();
        if(empty($snippetGroup)) {
            //默认渲染内容
            $snippetGroup = array(
                'header',
                'navbar',
                'body',
                'notify',
                'footer'
            );
        }

        foreach ($snippetGroup as $k => $group) {
            if(isset($this->snippets[$group])) {
                $renderQueue[$group] = $this->snippets[$group];
            }
        }

        //如果有navbar要渲染
        // fPrint(fSelfDir());
        if(array_search('navbar', $snippetGroup) !== false) {
            $navComponents = array();
            foreach ($GLOBALS['deploy']['navbar'] as $menuName => $menuData) {
                $currentSub = '';
                
                //这个菜单项包含二级菜单项
                if(is_array($menuData)) { 
                    $subMenuItems = array();
                    foreach ($menuData as $subName => $subData) {

                        if(
                            \fSelfDir() == $GLOBALS['deploy']['deployedDir'].$subData
                            ||$_SERVER['PHP_SELF'] == $GLOBALS['deploy']['deployedDir'].$subData
                        ) { //是否是当前页
                            $thisSub = 'nzNavbarSub-current';
                            $currentSub = "{?pageTitle.{$subName}?}";
                            $menuUrl = _ROOT.$subData;
                        } else {
                            $thisSub = '';
                            $menuUrl = _ROOT.$subData;
                        }

                        $subMenuItems[] = array(
                            '--subMenuName' => "{?pageTitle.{$subName}?}",
                            '--url' => $menuUrl,
                            '--isCurrent' => $thisSub
                        );
                    }
                    $subMenuContainers = array(array( //组装子菜单里的每个菜单项
                        '--subMenuItems' => $this->duplicate(
                            'navbar/dup.subMenu.item.html',
                            $subMenuItems
                        )
                    ));

                    if($currentSub !== '') {
                        $subHidden = '';
                    } else {
                        $subHidden = 'hidden';
                    }

                    $navComponents[] = array(
                        '--name' => "{?pageTitle.{$menuName}?}",
                        '--subMenu' => $this->duplicate(
                            'navbar/dup.subMenu.container.html',
                            $subMenuContainers
                        ),
                        '--url' => '#',
                        '--currentSub' => $currentSub,
                        '--hideSub' => $subHidden,
                        '--parentCurrent' => '' //有子级，所以父级当前态始终不需要显示
                    );

                    
                } 
                
                //这个菜单不包含二级菜单项
                else { 
                    if(
                        \fSelfDir() == $GLOBALS['deploy']['deployedDir'].$menuData
                        || $_SERVER['PHP_SELF'] == $GLOBALS['deploy']['deployedDir'].$menuData
                    ) { //是当前页
                        $menuUrl = '#';
                        $parentCurrent = 'nzNavbarMenu-parentCurrent';
                    } else {
                        $menuUrl = _ROOT.$GLOBALS['deploy']['navbar'][$menuName];
                        $parentCurrent = '';
                    }

                    $navComponents[] = array(
                        '--name' => "{?pageTitle.{$menuName}?}",
                        '--url' => $menuUrl,
                        '--subMenu' => '',
                        '--currentSub' => '',
                        '--hideSub' => 'hidden',
                        '--parentCurrent' => $parentCurrent
                    );
                }

            }

            $this->set(
                '$navbar.menuItems',
                $this->duplicate(
                    'navbar/dup.menuItem.html',
                    $navComponents
                )
            );
        }


        //合并变量，注意这里的列装顺序，有同名的元素时，后者会覆盖前者
        $this->var = array_merge(
            $this->var, 
            $GLOBALS['cache']['var'], 
        );

        $return = '';

        $debugTimer = microtime(TRUE);

        //遍历所有需要渲染的代码，并将它们拼装成一段完整代码
        $this->parsed = '';
        foreach ($renderQueue as $part) {
            foreach ($part as $k => $code) {
                $this->parsed .= $code;
            }
        }

        ################################################
        #做基于语言和变量的替换处理
        ################################################
        $this->recursiveRender();

        //记录log并返回渲染好的代码
        \fLog('Html rendered in: '.(microtime(TRUE) - $debugTimer).'s');

        return $this->parsed;
    }

    /**
     * 对HTML代码做基于语言和变量的替换处理，支持递归替换
     * 
     * @param string $srcSet = null
     * 被替换的源内容
     * 为null时，会取&$this->parsed
     * 
     * @param array $varSet = null
     * 用于替换的变量
     * 为null时，会取&$this->var
     * 
     * @param bool $recursive
     * 是否进行递归替换，默认为true
     * 
     * @param int $recursion
     * 用于传递和追踪当前递归层数，默认为0
     * 
     * @param array $compare
     * 用于追踪递归时的占位符变化，传入上一次的占位符列表进行比对
     * 如果两次递归的占位符没有变化，那么就不再做进一步递归，从而节约性能。
     * 
     * @return string
     * 返回替换过的内容
     */
    public function recursiveRender(
        string $srcSet = null,
        array $varSet = null,
        bool $recursive = true,
        int $recursion = 0,
        array $compare = array()
    ) {
        \fLog("Rendering recursion: {$recursion}");
        #对递归层数做检查，如果超过递归层数限制，抛错并结束递归
        if($recursion >= $GLOBALS['setting']['fReplace']['maxRecursive']) {
            \fLog("too many recurring: {$recursive} > {$GLOBALS['setting']['fReplace']['maxRecursive']}", 1, true);
            return $srcSet;
        }

        #如果$srcSet为null，那么就对&$this->parsed做处理
        if(is_null($srcSet)) {
            $source = &$this->parsed;
        } else {
            $source = $srcSet;
        }

        #如果$varSet为null，那么就用&$this->var作为数据源
        if(is_null($varSet)) {
            $vars = &$this->var;
        } else {
            $vars = $varSet;
        }

        #匹配所有占位符名称，不允许有空格，输出结果到$match, $match[0]为包括{?...?}符号的匹配内容，$match[1]则为符号内的内容，实际有用的只有$match[1]
        preg_match_all('~\{\?([a-zA-Z0-9\-_\.\!\$]*?)\?\}~mU', $source, $match, PREG_PATTERN_ORDER, 0);
        // preg_match_all('~\{\?(.*?)\?\}~mU', $source, $match, PREG_PATTERN_ORDER, 0);
        #去除重复
        $match[1] = array_flip(array_flip($match[1]));

        // fPrint($match);
        $pairs = array();
        foreach ($match[1] as $k => $placeholder) {
            //从$vars中取值填补
            if(array_key_exists($placeholder, $vars)) {
                $pairs[$match[0][$k]] = $vars[$placeholder];
            } 
            //从数据库中取语言内容填补
            else {
                //从languages数据表中取对应的记录
                $query = $this->db->getArr(
                    'languages',
                    array(
                        "`name` = '{$placeholder}'",
                        "`lang` = '{$this->langCode}'"
                    ),
                    null,
                    1
                );
                //如果没有取到，则试着从languages表中取默认语言的同名记录
                if(
                    $query === false
                    && $this->langCode !== $GLOBALS['deploy']['lang']
                ) {
                    $query = $this->db->getArr(
                        'languages',
                        array(
                            "`name` = '{$placeholder}'",
                            "`lang` = '{$GLOBALS['deploy']['lang']}'"
                        ),
                        null,
                        1
                    );
                }

                if($query !== false) {
                    $pairs[$match[0][$k]] = \fDecode($query[0]['content']);
                }
            }
        }

        //对$source的内容做一次替换处理
        $source = strtr($source, $pairs);

        //对处理过的$source做一次正则提取
        preg_match_all('~\{\?([a-zA-Z0-9\-_\.\!\$]*?)\?\}~mU', $source, $check, PREG_PATTERN_ORDER, 0);
        if(
            empty($check[0]) //如果没有占位符则跳出递归
            || $check[0] === $compare //如果有占位符，但和上一次修改后的检查结果一致，则结束递归
        ) {
            return $source;
        } 
        //有增加新的变量，再进行一次递归
        else {
            if(is_null($srcSet)) {
                $this->recursiveRender(null, $vars, true, $recursion+1, $check[0]);
            } else {
                $this->recursiveRender($source, $vars, true, $recursion+1, $check[0]);
            }
        }
    }
    
    /**
     * 渲染并输出一般html
     */
    public function output($type=null) {
        switch ($type) {
            case 'maintainance':
                $this->loadTpl('maintainance/frame.html','body');
                break;

            case 'embed':
                $this->loadTpl('header/frame.html', 'header');
                break;

            case 'tooltip':
                // $this->loadTpl('tooltip/frame.html', 'header');
                break;
            
            default: // 加载一般html的框架
                //header
                $this->loadTpl('header/frame.html', 'header');
                
                //navbar
                $this->loadTpl('navbar/frame.html', 'navbar');

                //body
                $this->loadTpl('body.discord.html', 'body');

                //footer
                $this->loadTpl('footer/frame.html', 'footer');
                break;
        }
        
        \fEcho($this->render());
    }

    /**
     * 渲染并输出重定向页
     * 
     * @param string $targetUrl
     * 重定向页面的相对路径
     * 
     * @param string $targetName
     * 在信息提示中，告知用户跳转目标的页面名
     * 默认为NULL
     * 
     * @param string $message
     * 告知用户的信息提示，比如"注册成功"之类的标题信息
     */
    public function redirect(
        $targetUrl,
        $targetName = NULL,
        $message = NULL
    ) {
        $this->title('pageTitle.redirect');

        //加载redirect特有的模板
        $this->loadTpl('redirect/header.html','redirect');
        $this->loadTpl('redirect/body.frame.html','redirect');
        $this->loadTpl('footer/frame.html', 'footer');

        $this->set('$redirectUrl', $targetUrl);
        $this->set('!redirectAwaits', $GLOBALS['deploy']['redirectAwaits']);

        //处理跳转目标的名称
        $this->set('$redirectPageName', "{?{$targetName}?}");

        //处理提示文字
        if(is_null($message)) {
            $this->set('$redirectMessage', '{?redirect.message.default?}');
        } else {
            $this->set('$redirectMessage', "{?{$message}?}");
        }

        //只渲染redirect和footer代码片段
        \fEcho($this->render('redirect','footer'));
    }

    /**
     * 渲染并输出重定向页，该重定向页将返回$this->backUrl指定的上一层页面
     * 
     * @param string $message
     * 告知用户的信息提示，比如"注册成功"之类的标题信息
     */
    public function redirectBack(
        $message = null
    ) {
        $this->redirect(
            $this->backUrl,
            'pageTitle.previous',
            $message
        );
    }
}
?>