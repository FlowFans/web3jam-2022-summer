<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal角色的类
################################################

/***********************************************
 * 开发备忘录
 ***********************************************
 * 所有角色数据在对象中都是明码保存的
 * 只有当角色被使用xChar::export()方法导出时，才会做额外的编码处理
 * 从数据库中取出相关数据时，会在xChar::import()中做解码操作
 * 因此，不用操心编码问题。
 * 如果需要增加角色对象的属性，并涉及到保存数据库的fEncode()/fDecode()时，需要更新xChar::export()和xChar::import方法
 ***********************************************/


/**
 * 实例化
 * $obj = new meshal\xChar(
 *  int $ownerId = null //这个角色的拥有者uid
 * );
 * 
 * 常用方法
 * 
 * $obj->load(
 *  int $id //角色id
 * );
 * 根据传递的$id加载一个角色
 * 
 * $obj->save(
 *  bool $override = true //是否覆盖已经存在的数据
 * );
 * 保存角色数据到数据库中
 * 
 * $obj->export();
 * 把角色数据导出为一个数组
 * 
 * $obj->event(
 *  int $uid //与该角色有关的用户uid
 *  string event //事件类型
 *  mixed data //事件的补充资料（会被json编码）
 * );
 * 为角色添加一条事件记录
 * 
 */
class xChar
{
    function __construct(
        int $ownerId = null
    ) {
        global $db;
        $this->db = $db;
        // $this->db = new \xDatabase;
        $this->version = null; //此角色的适配最低版本
        $this->id = null; //角色id

        $this->stat = null; //角色当前状态
        /**
         * 角色状态
         * - null：在营地休息
         * - 'adventure'：正在冒险中
         */

        $this->sortScore = 0; //角色排序得分
        $this->lastUpdate = 0; //角色最后更新的时间
        $this->recoverStart = 0; //角色开始恢复时间

        $this->owner = new \user\xAdapter; //拥有者信息
        $this->creator = new \user\xAdapter; //创作者信息

        $this->portrait = null; //角色头像
        $this->name = null; //角色名称
        $this->bio = null; //角色小传
        $this->controller = array(); //用户操作
        $this->viewerUrl = '{?!dirRoot?}c/?id={?--charId?}'; //默认的角色查看链接

        /**
         * 属性注册表，格式：
         * array(
         *  '属性名' => '属性使用的类' //无需加上命名空间
         * )
         */
        $this->registry = array(
            'scores' => array(
                //基础属性
                'm' => 'xAttrScore', //强壮
                'a' => 'xAttrScore', //活力
                's' => 'xAttrScore', //精神
                
                //防护
                't' => 'xProtectionScore', //物理防护
                'e' => 'xProtectionScore', //侵蚀防护
                'r' => 'xProtectionScore', //灵异防护

                //免疫
                'ip' => 'xImmunityScore', //物理免疫
                'ie' => 'xImmunityScore', //侵蚀免疫
                'io' => 'xImmunityScore', //灵异免疫

                //其他
                'ap' => 'xPositiveScore', //机动
                'cc' => 'xCarryingCapability', //负载

                //范围
                'ms' => 'xDistanceScore', //移动速度
                'pr' => 'xDistanceScore', //感知范围

                //潜能与实力
                'strength' => 'xStrengthScore'
            ),

            'containers' => array(
                //特征容器
                'features' => 'xFeatures',

                //物品容器
                'inventory' => 'xInventory',

                //能力容器
                'abilities' => 'xAbilities'
            )
        );

        //实例化属性对象
        foreach ($this->registry['scores'] as $scoreName => $className) {
            $loadClass = '\\meshal\\char\\'.$className;
            $this->$scoreName = new $loadClass($this);
        }

        //实例化容器对象
        foreach ($this->registry['containers'] as $containerName => $className) {
            $loadClass = '\\meshal\\char\\'.$className;
            $this->$containerName = new $loadClass($this);
        }

        //设置初始属性
        $this->m->set('base', 3);
        $this->a->set('base', 3);
        $this->s->set('base', 3);
        $this->ap->set('base', 3);
        $this->m->restore();
        $this->a->restore();
        $this->s->restore();


        // fPrint($this);

        // //基本信息
        // $this->bio = array(
        //     'name' => null,
        //     'alias' => array(),
        //     'height' => null,
        //     'weight' => null,
        //     'birthday' => null
        // );

        // //装备
        // $this->equipment = array();

        // //携带
        // $this->inventory = array();   
    }

    /**
     * 从数据库中加载角色数据
     * 
     * @param int $id
     * 加载的角色id
     */
    public function load(
        int $id
    ){
        //重载构造函数以清空数据
        $this->__construct();

        //根据传递的$id查询角色数据
        $query = $this->db->getArr(
            'characters',
            array(
                "`id` = '{$id}'"
            ),
            null,
            1
        );

        //查无此角色则返回false并记录错误
        if($query === false) {
            \fLog("The character({id=$id}) doesn't exist in database");
            return false;
        }

        $this->import($query[0]);

        $this->recover();
        return true;
    }

    /**
     * 导入一个数组，并根据这个数组为角色赋值
     * 
     * @param array $importData
     * 用于加载的数组，必须符合角色的数据格式
     */
    public function import(
        array $importData
    ) {
        //重载构造函数以清空数据
        $this->__construct();

        # 开始逐步加载数据
        
        //基础资料
        $this->id = $importData['id'];
        $this->stat = $importData['stat'];

        $this->version = $importData['version'];
        $this->lastUpdate = $importData['lastUpdate'];
        $this->recoverStart = $importData['recoverStart'];
        
        $this->name = \fDecode($importData['name']);
        $this->portrait = \fDecode($importData['portrait']);
        $this->bio = \fDecode($importData['bio']);

        $this->owner->load($importData['ownerId']);
        $this->creator->load($importData['creatorId']);

        $this->sortScore = $importData['sortScore'];
        
        //处理json格式的data数据
        $data = json_decode($importData['data'], true);

        //加载属性
        foreach ($this->registry['scores'] as $scoreName => $settings) {
            foreach ($data[$scoreName] as $property => $v) {
                $this->$scoreName->set($property, $v);
            }
        }

        //加载特征
        foreach ($data['features'] as $containerName => $record) {
            $this->features->$containerName = $record;
        }
        $this->features->update();

        //加载能力
        foreach ($data['abilities'] as $containerName => $record) {
            $this->abilities->$containerName = $record;
        }
        $this->abilities->update();

        //加载物品
        if(
            !empty($data['inventory'])
            && !is_null($data['inventory'])
        ) {
            $this->inventory->import($data['inventory']);
        }
    }

    /**
     * 保存角色数据进数据库
     * 
     * @param bool $override
     * 是否强制覆盖已有记录，为true时会覆盖；为false时则不会覆盖
     * 默认为true
     * 
     * @return bool 
     * 保存成功则返回true，否则返回false
     */
    public function save(
        bool $override = true
    ) {
        if(is_null($this->id)) { //这个角色是新的，可以直接insert
            $insert = $this->export();
            unset($insert['id']);
            $insert['lastUpdate'] = time();

            $this->id = $this->db->insert(
                'characters',
                $insert
            );

            if($this->id === false) {
                \fLog("Error while inserting new character");
                return false;
            } else {
                \fLog("New character(id={$this->id}) successfully inserted");
                \meshal\char\updateSort($this->id);
                return true;
            }
        } else {
            //检查是否有记录
            $query = $this->db->getArr(
                'characters',
                array(
                    "`id` = '{$this->id}'"
                ),
                null,
                1
            );

            if($query !== false) { //有记录
                if($override === false) { //不允许覆盖
                    \fLog('Failed to update character, to override, set $override to true');
                    return false;
                } else { //更新数据
                    $insert = $this->export();
                    $insert['lastUpdate'] = time();

                    $check = $this->db->update(
                        'characters',
                        $insert,
                        array(
                            "`id` = '{$this->id}'"
                        ),
                        1
                    );

                    if($check != false) {
                        \fLog("Character(id={$this->id}) updated");
                        return true;
                    } else {
                        \fLog("Failed to update character(id={$this->id})");
                        return false;
                    }
                }
            }
        }
        \meshal\char\updateSort($this->id);
    }

    /**
     * 导出角色数据
     * 
     * @return array
     * 返回的是一个数组
     */
    public function export() {
        $return = array(
            'id' => $this->id,
            'stat' => $this->stat,
            'version' => $this->version,
            'ownerId' => $this->owner->uid,
            'creatorId' => $this->creator->uid,
            'name' => \fEncode($this->name),
            'portrait' => \fEncode($this->portrait),
            'bio' => \fEncode($this->bio),
            'recoverStart' => $this->recoverStart
        );

        //组装属性部分
        $assembly = array();
        foreach ($this->registry['scores'] as $scoreName => $className) {
            $assembly[$scoreName] = $this->$scoreName->export();
        }

        //组装容器部分
        foreach ($this->registry['containers'] as $containerName => $className) {
            $assembly[$containerName] = $this->$containerName->export();
        }

        //组装物品部分
        // $assembly['inventory'] = $this->inventory->export();

        $return['data'] = json_encode($assembly);

        return $return;
    }

    /**
     * 导出角色数据为json格式
     * 
     * @return string
     * 返回的是一个json格式的字符串
     */
    public function exportJson () {
        $return = $this->export();
        $return['name'] = fDecode($return['name']);
        $return['portrait'] = fDecode($return['portrait']);
        $return['bio'] = fDecode($return['bio']);
        $return['data'] = json_decode($return['data'], true);

        return(json_encode($return));
    }

    /**
     * 向数据库的character_interaction表中推入一个用户与角色的交互事件
     * 
     * @param int $uid
     * 与此角色发生交互的用户id
     * 
     * @param string $event
     * 事件的类型名称
     * 
     * @param mixed $data
     * 事件的补充资料，这个参数取决于不同类型的事件，但总是会做json编码处理
     */
    public function event(
        $uid,
        string $event,
        $data = null
    ) {
        $this->db->insert(
            'character_events',
            array(
                'uid' => $uid,
                'charId' => $this->id,
                'event' => $event,
                'data' => is_null($data) ? null : json_encode($data),
                'timestamp' => time()
            )
        );
    }

    /**
     * 向角色渲染器添加操作
     * 通过这个，可以添加一些诸如流放、录用的操作（由具体使用该角色的页面添加）
     * 
     * @param string $url
     * 这个操作指向的脚本url
     * 
     * @param string $text
     * 这个操作的显示文字
     * 
     * @param string $textColor
     * 这个操作的显示文字字色（需要参考CSS样式表）
     * 
     * @param string $bgColor
     * 这个操作的背景颜色（需要参考CSS样式表）
     * 
     * @param array|string|null $auth
     * 这个操作可支持的用户组，支持以下参数，可在这个数组里包含多个项
     *  'any'：任意人可见
     *  'owner'：这个角色的拥有者可见
     *  'creator'：这个角色的创建者可见
     *  'guest'：非这个角色的拥有者和创建者可见
     * 默认为array('owner');
     * 
     * @param string|null $target
     * html url的窗口打开方式，包括 _self, _blank, _parent, _top
     * 如果为空，则会使用默认值"_self"
     * 
     * @param bool $alwaysShow
     * 这个按钮是否总是显示，默认为false
     * 
     * @param array $statForbid = array('adventure')
     * 当角色的stat在这些状态时，不添加操作
     */
    public function addCtrl(
        string $url,
        string $text,
        $css = null,
        $auth = array('owner'),
        $target = null,
        bool $alwaysShow = false,
        array $statForbid = array()
    ) {
        if(array_search($this->stat, $statForbid) !== false) {
            return;
        }

        if(!is_array($auth) && is_string($auth)) {
            $auth = array($auth);
        }
        if(!is_array($auth) && is_null($auth)) {
            $auth = array('owner');
        }

        if(is_null($css)) {
            $css = 'colorWhite1 bgOpaGreen1';
        }

        if(is_null($target)) {
            $target = '_self';
        }

        $this->controller[] = array(
            'url' => $url,
            'text' => $text,
            'css' => $css,
            'target' => $target,
            'alwaysShow' => $alwaysShow == false ? '' : 'common-controller-alwaysShow',
            'auth' => array_flip($auth)
        );
    }

    /**
     * 对这个角色做html渲染
     * 
     * @param string $template = null
     * 加载的模板类型
     * 
     * @param bool $controller = true
     * 是否显示角色相关的控制操作，为true时显示，为false时不渲染
     * 默认为true
     * 
     * @param object \xUser $user = null
     * 要传递进来的$user对象（用于后续涉及到用户权限的渲染）
     * 
     * @return string
     * 返回的是渲染好的html代码
     */
    public function render(
        string $template = null,
        bool $controller = true,
        \xUser &$user = null,
        string $css = '',
        string $frameCss = ''
    ) {
        $renderer = new \xHtml;
        //角色查看器URL
        $renderer->set('--viewerUrl', $this->viewerUrl);

        //角色肖像目录
        $renderer->set('!dirPortrait', _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait']); //用户上传的角色肖像目录

        //基本数据
        $renderer->set('--charId', $this->id);
        $renderer->set('--charName', $this->name, true);
        $renderer->set('--portrait', 
            (
                is_null($this->portrait) || $this->portrait == '' 
                || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['portrait'].$this->portrait)
            ) 
                ? "{?!dirImg?}cardBg.default.jpg" 
                : "{?!dirPortrait?}{$this->portrait}"
        );
        $renderer->set('--charType', 'player');
        $renderer->set('--bio', (is_null($this->bio) || $this->bio == '') ? '{?common.bio.default?}' : $this->bio, true);
        
        //角色状态
        if(is_null($this->stat)) {
            $renderer->set('--stat', '{?character.stat.resting?}');
            $renderer->set('--statCss', 'characterStat-resting');
        } else {
            $renderer->set('--stat', "{?character.stat.{$this->stat}?}");
            $renderer->set('--statCss', "characterStat-{$this->stat}");
        }

        //属性最大值
        $renderer->set('--mTotal', $this->m->total);
        $renderer->set('--aTotal', $this->a->total);
        $renderer->set('--sTotal', $this->s->total);
        
        //属性当前值
        $renderer->set('--mCurrent', $this->m->current);
        $renderer->set('--aCurrent', $this->a->current);
        $renderer->set('--sCurrent', $this->s->current);

        //属性条
        $renderer->set(
            '--mBar',
            ($this->m->current / $this->m->total * 100).'%'
        );
        $renderer->set(
            '--aBar',
            ($this->a->current / $this->a->total * 100).'%'
        );
        $renderer->set(
            '--sBar',
            ($this->s->current / $this->s->total * 100).'%'
        );

        //防护
        $renderer->set('--tTotal', $this->t->current);
        $renderer->set('--eTotal', $this->e->current);
        $renderer->set('--rTotal', $this->r->current);
        //免疫
        $renderer->set('--ip', $this->ip->immune == 1 ? '{?term.immunity?}' : '');
        $renderer->set('--ie', $this->ie->immune == 1 ? '{?term.immunity?}' : '');
        $renderer->set('--io', $this->io->immune == 1 ? '{?term.immunity?}' : '');
        //如果免疫，则隐藏防护；反之隐藏免疫
        $renderer->set('--hideIp', $this->ip->immune == 1 ? 'hidden' : '');
        $renderer->set('--hideIe', $this->ie->immune == 1 ? 'hidden' : '');
        $renderer->set('--hideIo', $this->io->immune == 1 ? 'hidden' : '');
        $renderer->set('--showIp', $this->ip->immune == 1 ? '' : 'hidden');
        $renderer->set('--showIe', $this->ie->immune == 1 ? '' : 'hidden');
        $renderer->set('--showIo', $this->io->immune == 1 ? '' : 'hidden');
        //速度和距离
        $renderer->set('--msTotal', $this->ms->current);
        $renderer->set('--prTotal', $this->pr->current);
        
        //负载
        $renderer->set('--ccCurrent', $this->cc->current);
        $renderer->set('--ccTotal', $this->cc->total);
        if($this->cc->current > $this->cc->total || $this->cc->total == 0) {
            $renderer->set('--ccBar', '0%');
        } else {
            $renderer->set('--ccBar', (($this->cc->total - $this->cc->current) / $this->cc->total * 100).'%');
        }

        //机动
        $renderer->set('--apCurrent', $this->ap->current);
        $renderer->set('--apTotal', $this->ap->total);
        $renderer->set(
            '--apBar',
            ($this->ap->current / $this->ap->total * 100).'%'
        );

        //潜能和实力
        $renderer->set('--pp', $this->strength->pp);
        $renderer->set('--st', $this->strength->st);

        //属性详情
        $renderer->set('--detail.m', \fEncode(json_encode($this->m->export())));
        $renderer->set('--detail.a', \fEncode(json_encode($this->a->export())));
        $renderer->set('--detail.s', \fEncode(json_encode($this->s->export())));

        //防护与免疫详情
        if($this->ip->immune == 1) {
            $renderer->set('--detail.t', \fEncode(json_encode($this->ip->export())));
            $renderer->set('--scoreType.t', 'score-immune');
        } else {
            $renderer->set('--detail.t', \fEncode(json_encode($this->t->export())));
            $renderer->set('--scoreType.t', 'score');
        }

        if($this->ie->immune == 1) {
            $renderer->set('--detail.e', \fEncode(json_encode($this->ie->export())));
            $renderer->set('--scoreType.e', 'score-immune');
        } else {
            $renderer->set('--detail.e', \fEncode(json_encode($this->e->export())));
            $renderer->set('--scoreType.e', 'score');
        }

        if($this->io->immune == 1) {
            $renderer->set('--detail.r', \fEncode(json_encode($this->io->export())));
            $renderer->set('--scoreType.r', 'score-immune');
        } else {
            $renderer->set('--detail.r', \fEncode(json_encode($this->r->export())));
            $renderer->set('--scoreType.r', 'score');
        }

        //速度和距离详情
        $renderer->set('--detail.ms', \fEncode(json_encode($this->ms->export())));
        $renderer->set('--detail.pr', \fEncode(json_encode($this->pr->export())));

        //机动和负载详情
        $renderer->set('--detail.ap', \fEncode(json_encode($this->ap->export())));
        $renderer->set('--detail.cc', \fEncode(json_encode($this->cc->export())));

        //实力详情 ###技术债
        // $renderer->set('--detail.st', \fEncode(json_encode($this->strength->export())));

        //组装特征的临时数组
        $featureArr = array();
        foreach ($this->features as $type => $content) {
            foreach($content as $name => $data) {
                $featureArr[$type][] = array(
                    '--featureType' => $type,
                    '--featureName' => $name
                );
            }
        }
        $featureList = array();
        foreach ($GLOBALS['meshal']['featureContainer'] as $containerName => $settings) {
            foreach ($this->features->$containerName as $featureName => $data) {
                //组装时，如果一个特征容器和较弱特征容器中同时出现了某个特征，那么隐藏较弱特征容器中的特征
                if(!is_null($GLOBALS['meshal']['featureContainer'][$settings['major']])) {
                    if($this->features->isFeatureExist( //如果较强级特征容器中有同名特征，不做渲染并打断
                        $settings['major'],
                        $featureName
                    ) === true) {
                       break;
                    }
                }

                //如果数据库中没有取到特征的记录，不做渲染并打断
                $queryFeature = xFeature::getData($settings['type'], $featureName);
                if( $queryFeature === false) {
                    break;
                }

                $rendered = array(
                    '--featureType' => $settings['type'],
                    '--featureName' => "{?featureName.{$settings['type']}.{$featureName}?}",
                    '--featureCode' => $featureName,
                    '--rarityStyle' => $queryFeature['probability']['rarityStyle']
                );

                //如果这个特征属于弱级特征，显示弱级特征符号
                if(!is_null($GLOBALS['meshal']['featureContainer'][$settings['major']])) {
                    $rendered['--featurePoor'] = '{?featureType.poor?}';
                    $rendered['--displayPoor'] = '';
                } else {
                    $rendered['--featurePoor'] = '';
                    $rendered['--displayPoor'] = 'hidden';
                }

                $featureList[] = $rendered;
            }
        }

        if(empty($featureList)) {
            $renderer->set('--featureDisplay', '');
        } else {
            $renderer->set('--featureDisplay', 'hidden');
        }

        //渲染特征标签
        $renderer->set('--features', $renderer->duplicate('feature/tag.html', $featureList));
    

        //组装能力的临时数组
        $abilityArr = array();
        foreach ($this->abilities as $type => $content) {
            foreach($content as $name => $data) {
                $abilityArr[$type][] = array(
                    '--abilityType' => $type,
                    '--abilityName' => $name
                );
            }
        }
        $abilityList = array();
        foreach ($this->abilities->containerRegistry as $containerName => $settings) {
            foreach ($this->abilities->$containerName as $abilityName => $value) {
                $abilityList[] = array(
                    '--abilityContainer' => $containerName,
                    '--abilityName' => "{?ability.{$abilityName}?}",
                    '--abilityEnhanced' => $value['enhanced'] > 0 
                        ? "{?character.abilityEnhanced?}{$value['enhanced']}"
                        : ''
                );
                // $renderer->dbLang("meshal.ability.{$abilityName}");
            }
        }

        //渲染能力标签
        $renderer->set('--abilities', $renderer->duplicate('ability/tag.html', $abilityList));

        //组装装备的临时数组
        $inventory = $this->inventory->export();
        $equipment = '';
        foreach ($inventory['equipment'] as $slotName => $slotData) {
            if(!empty($slotData['items'])) {
                foreach ($slotData['items'] as $itemName => $amount) {
                    if($amount > 0) $equipment .= \meshal\xItem::renderTag($itemName, $amount);
                }
            }
            if($slotData['availableSlots'] > 0) {
                $equipment .= \meshal\xItem::renderEquipSlotTag($slotName, $slotData['availableSlots']);
            }
        }
        if($equipment == '') {
            $renderer->set('--equipmentDisplay', '');
        } else {
            $renderer->set('--equipmentDisplay', 'hidden');
        }
        $renderer->set('--equipment', $equipment);
        

        //组装携带物品的临时数组
        $carryings = '';
        if(!empty($this->inventory->carrying)) {
            foreach ($this->inventory->carrying as $itemName => $amount) {
                $carryings .= \meshal\xItem::renderTag($itemName, $amount);
            }
            $renderer->set('--inventoryDisplay', 'hidden');
        } else {
            $renderer->set('--inventoryDisplay', '');
        }
        $renderer->set('--carrying', $carryings);



        //选择加载模板
        if(is_null($template)) $template = 'sheet';
        $renderer->loadTpl("sheet/{$template}.frame.html");

        //渲染操作组件
        $renderer->set('$controller', '');
        if(!empty($this->controller)) {
            $ctrl = array();
            foreach ($this->controller as $k => $arr) {
                switch (true) {
                    case (
                        //任意人可见
                        isset($arr['auth']['any'])
                    ):
                        $ctrl[] = array(
                            '--charId' => $this->id,
                            '--uid' => $user->uid,
                            '--url' => $arr['url'],
                            '--text' => "{?{$arr['text']}?}",
                            '--css' => $arr['css'],
                            '--target' => $arr['target'],
                            '--show' => $arr['alwaysShow']
                        );
                        break;

                    case (
                        //要求是creator，已经传入了当前用户，且这个用户是角色的creator
                        isset($arr['auth']['creator'])
                        && !is_null($user)
                        && $this->creator->uid == $user->uid
                    ):
                        $ctrl[] = array(
                            '--charId' => $this->id,
                            '--uid' => $user->uid,
                            '--url' => $arr['url'],
                            '--text' => "{?{$arr['text']}?}",
                            '--css' => $arr['css'],
                            '--target' => $arr['target'],
                            '--show' => $arr['alwaysShow']
                        );
                        break;

                    case (
                        //要求是owner，已经传入了当前用户，且这个用户是角色的owner
                        isset($arr['auth']['owner'])
                        && !is_null($user)
                        && $this->owner->uid == $user->uid
                    ):
                        $ctrl[] = array(
                            '--charId' => $this->id,
                            '--uid' => $user->uid,
                            '--url' => $arr['url'],
                            '--text' => "{?{$arr['text']}?}",
                            '--css' => $arr['css'],
                            '--target' => $arr['target'],
                            '--show' => $arr['alwaysShow']
                        );
                        break;

                    case (
                        //要求是guest，已经传入了当前用户，但这个用户不是角色的owner或没有用户信息
                        isset($arr['auth']['guest'])
                        && (
                            $this->owner->uid != $user->uid
                            || is_null($user)
                        )
                    ):
                        $ctrl[] = array(
                            '--charId' => $this->id,
                            '--uid' => $user->uid ? $user->uid : '',
                            '--url' => $arr['url'],
                            '--text' => "{?{$arr['text']}?}",
                            '--css' => $arr['css'],
                            '--target' => $arr['target'],
                            '--show' => $arr['alwaysShow']
                        );
                        break;

                    default:
                        //默认不做任何渲染
                        break;
                }
            }
            if(!empty($ctrl)) {
                $renderer->set(
                    '$controller',
                    $renderer->duplicate(
                        "sheet/{$template}.controller.html",
                        $ctrl
                    )
                );
            } else {
                $renderer->set('$controller', '');
            }
        }

        //插入自定义css class
        $renderer->set('--frameCss', $frameCss);
        $renderer->set('--css', $css);

        //写入debugInfo
        if($GLOBALS['debug']['characterSheet'] === true) {
            $debugInfo = $this->sortScore;

            $renderer->set('--debugInfo',$debugInfo);
        } else {
            $renderer->set('--debugInfo','');
        }
        

        #将这个角色的所有数据推入待渲染数组
        return $renderer->render(
            'body'
        );
    }

    /**
     * 渲染Lite版角色卡
     * 
     * @param bool $controller = true
     * 是否显示角色相关的控制操作，为true时显示，为false时不渲染
     * 默认为true
     * 
     * @param object \xUser $user = null
     * 要传递进来的$user对象（用于后续涉及到用户权限的渲染）
     * 
     * @return string
     * 返回的是渲染好的html代码
     */
    public function renderLite(
        bool $controller = true,
        \xUser &$user = null,
        string $css = '',
        string $frameCss = ''
    ) {
        return $this->render('lite', $controller, $user, $css, $frameCss);
    }

    /**
     * 对这个角色做html渲染(tag版)
     * @param int $id
     * 
     * @param string $target = ''
     */
    public static function renderTag(
        int $id,
        string $target = ''
    ) {
        global $db;

        $renderer = new \xHtml;
        $query = $db->getArr(
            'characters',
            array(
                "`id` = '{$id}'"
            ),
            null,
            1
        );

        //查无此角色则返回false并记录错误
        if($query === false) {
            \fLog("The character({id=$id}) doesn't exist in database");
            return false;
        }

        //角色查看器URL
        $renderer->set('--viewerUrl', "{?!dirRoot?}c/?id={$id}");
        $renderer->set('--target', $target);

        //基本数据
        $renderer->set('--charId', $id);
        $renderer->set('--charName', \fDecode($query[0]['name']));
        $renderer->set('--charType', 'player');

        $renderer->loadTpl('character/tag.html');

        return $renderer->render(
            'body'
        );
    }

    /**
     * 获取指定id角色的拥有者uid
     */
    public static function getOwnerId (
        int $id
    ) {
        global $db;

        $query = $db->getArr(
            'characters',
            array(
                "`id` = '{$id}'"
            ),
            null,
            1
        );

        //查无此角色则返回false并记录错误
        if($query === false) {
            \fLog("The character({id=$id}) doesn't exist in database");
            return false;
        }

        return $query[0]['ownerId'];
    }

    /**
     * 自动恢复这个角色的属性
     * 
     * @param int $endTime
     * 结算恢复的时间点
     */
    public function recover(
        $endTime = null
    ) {
        //如果不在营地，则不处理
        if(!is_null($this->stat)) {
            $this->m->set('nextRecover', $this->m->current < $this->m->total ? -1 : 0);
            $this->a->set('nextRecover', $this->a->current < $this->a->total ? -1 : 0);
            $this->s->set('nextRecover', $this->s->current < $this->s->total ? -1 : 0);
            $this->ap->set('nextRecover', $this->ap->current < $this->ap->total ? -1 : 0);
            return;
        }

        //如果在营地才恢复
        if(is_null($endTime)) $endTime = time(); //如果没有给定结束恢复的时间，那么就视同当前时间
        $period = $endTime - $this->recoverStart;

        $recovery = array(
            'm' => $period / $GLOBALS['setting']['character']['recoverInterval'] * $this->m->total + $this->m->currentDigit,
            'a' => $period / $GLOBALS['setting']['character']['recoverInterval'] * $this->a->total + $this->a->currentDigit,
            's' => $period / $GLOBALS['setting']['character']['recoverInterval'] * $this->s->total + $this->s->currentDigit,
            'ap' => $period / $GLOBALS['setting']['character']['recoverInterval'] * $this->ap->total + $this->ap->currentDigit
        );

        # m recovery
        if($this->m->current < $this->m->total) {
            $this->m->add(
                'current',
                floor($recovery['m'])
            );
    
            $this->m->set(
                'currentDigit',
                \fGetDigit($recovery['m'])
            );

            $this->m->set(
                'nextRecover',
                ceil(\fDiv((1 - $this->m->currentDigit), \fDiv($this->s->total, $GLOBALS['setting']['character']['recoverInterval'], 18)))
            );
        }

        # a recovery
        if($this->a->current < $this->a->total) {
            $this->a->add(
                'current',
                floor($recovery['a'])
            );
    
            $this->a->set(
                'currentDigit',
                \fGetDigit($recovery['a'])
            );

            $this->a->set(
                'nextRecover',
                ceil(\fDiv((1 - $this->a->currentDigit), \fDiv($this->s->total, $GLOBALS['setting']['character']['recoverInterval'], 18)))
            );
        }

        # s recovery
        if($this->s->current < $this->s->total) {
            $this->s->add(
                'current',
                floor($recovery['s'])
            );
    
            $this->s->set(
                'currentDigit',
                \fGetDigit($recovery['s'])
            );

            $this->s->set(
                'nextRecover',
                ceil(\fDiv((1 - $this->s->currentDigit), \fDiv($this->s->total, $GLOBALS['setting']['character']['recoverInterval'], 18)))
            );
        }

        # ap recovery
        if($this->ap->current < $this->ap->total) {
            $this->ap->add(
                'current',
                floor($recovery['ap'])
            );
    
            $this->ap->set(
                'currentDigit',
                \fGetDigit($recovery['ap'])
            );

            $this->ap->set(
                'nextRecover',
                ceil(\fDiv((1 - $this->ap->currentDigit), \fDiv($this->s->total, $GLOBALS['setting']['character']['recoverInterval'], 18)))
            );
        }

        $this->recoverStart = $endTime; //更新最后恢复的时间

        $this->save();
    }

    /**
     * 更新状态
     * 
     * @param string $stat = null
     * 更新的状态码，为空表示回营
     * 
     * @param string|int $timestamp = null
     * 修改状态的时间，为null时视作当前时间
     */
    public function setStat(
        string $stat = null,
        $timestamp = null
    ) {
        if(is_null($this->stat)) $this->recover($timestamp); //如果之前在营地，那么在改变状态前先结算一下恢复
        $this->stat = $stat;
        $this->save();
    }

    /**
     * 查询一个角色的名字
     * @param int $id
     * 角色的id
     * 
     * @return string
     * 返回角色的名字
     * 查询失败返回false
     */
    public static function getName(
        int $id
    ) {
        global $db;
        $query = $db->getArr(
            'characters', 
            array(
                "`id` = {$id}"
            ),
            null,
            1
        );

        //查无此角色则返回false并记录错误
        if($query === false) {
            \fLog("The character({id=$id}) doesn't exist in database");
            return false;
        }

        return \fDecode($query[0]['name']);
    }
}
?>