<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal冒险的类
################################################

/*
创建一个新冒险
    new(
        string $templateName, //冒险模板的name
        ...$charIds //加入这次冒险的角色
    )

保存这个冒险进数据库
    save()

加载一个冒险
    load(
        int $adventureId //要加载的冒险实例id
    )

执行这个冒险
    execute()

结束这个冒险，并释放其中的角色
    end()
*/
class xAdventure
{
    function __construct()
    {
        global $db;
        $this->db = $db;
        $this->team = new \meshal\xTeam; //冒险的队伍容器对象
        $this->dice = new \meshal\xDice; //掷骰器
        $this->checker = new \meshal\adventure\xChecker($this); //队伍检查器
        $this->executor = new \meshal\adventure\xExecutor($this); //冒险效果执行器
        $this->encounter = new \meshal\adventure\xEncounter($this); //遭遇处理器
        $this->logger = new \meshal\adventure\xLogger($this); //冒险记录处理器

        $this->id = null; //冒险Id
        $this->version = null; //冒险的版本
        $this->sealed = 0; //这个冒险的结算状态

        $this->relChar = array(); //用于存储最后与冒险交互的的角色Id
        $this->relSuccess = array(); //用于存储成功尝试的角色Id
        $this->relFailure = array(); //用于存储失败尝试的角色Id

        $this->type = array(); //冒险类型
        $this->apCost = 0; //冒险的ap消耗
        $this->teamMin = 0; //冒险的最小团队规模
        $this->teamMax = null; //冒险的最大团队规模
        $this->strengthMin = 0; //冒险的最小实力要求
        $this->strengthMax = null; //冒险的最大实力要求

        $this->startTime = 0; //冒险开始时间
        $this->endTime = 0; //冒险结束时间
        
        $this->templateName = null; //冒险模板名称
        $this->coverImage = null; //冒险的封面图
        $this->probability = 0; //冒险的随机权重
        $this->data = array(); //冒险的数据资料

        // $this->log = array(); //冒险的日志资料
        // $this->logCurrent = 0; //冒险日志资料的指针
        
        $this->ledger = array( //冒险的账本数据，用于记录收获
            'potentiality' => array(), //记录每个角色的潜能增长（charId => pp）
            'item' => array() //记录每个角色的物品获得（每个charId一个键名，键值是array(itemName => amount)
        );

        $this->controller = array(); //用户操作
    }

    /**
     * 从数据库中获取冒险实例的资料
     * 
     * @param int $id
     * 要查询的冒险实例id
     * 
     * @return mixed
     * 如果没有查到，返回false
     * 否则返回该冒险实例（数组格式）
     */
    public static function getInstance (
        int $id
    ) {
        global $db;
        $arr = $db->getArr(
            'adventure_instances',
            array(
                "`id` = '{$id}'"
            ),
            null,
            1
        );
        if($arr === false) {
            \fLog("Adventure instance({$id}) doesn't exist in the database");
            return false;
        }

        //组装队伍角色列表
        $members = array();
        $mArr = $db->getArr(
            'adventure_chars',
            array(
                "`adventureId` = '{$id}'"
            ),
            null
        );
        if($mArr !== false) {
            foreach($mArr as $k => $d) {
                $members[] = $d['charId'];
            }
        }

        $return = array(
            'id' => $id,
            'version' => $arr[0]['version'],
            'sealed' => $arr[0]['sealed'],
            'templateName' => $arr[0]['templateName'],
            'members' => $members,
            'ledger' => json_decode($arr[0]['ledger'], true),
            'log' => json_decode($arr[0]['log'], true),
            'startTime' => $arr[0]['startTime'],
            'endTime' => $arr[0]['endTime']
        );

        return $return;
    }

    /**
     * 从数据库中获取冒险模板的资料
     * 
     * @param string $templateName
     * 冒险模板的名称
     * 
     * @return mixed
     * 如果没有查到，返回false
     * 如果查到了冒险模板，以数组返回包含该冒险模板的数据
     */
    public static function getData (
        string $templateName
    ) {
        global $db;
        $arr = $db->getArr(
            'adventures',
            array(
                "`name` = '{$templateName}'"
            ),
            null,
            1
        );
        if($arr === false) {
            \fLog("{$templateName} doesn't exist in the library");
            return false;
        }

        $return = array(
            'fullname' => "meshal.adventure.{$arr[0]['name']}",
            'name' => $templateName,
            'coverImage' => \fDecode($arr[0]['coverImage']),
            'apCost' => $arr[0]['apCost'],
            'duration' => $arr[0]['duration'],
            'teamMin' => $arr[0]['teamMin'],
            'teamMax' => $arr[0]['teamMax'],
            'strengthMin' => $arr[0]['strengthMin'],
            'strengthMax' => $arr[0]['strengthMax'],
            'type' => json_decode($arr[0]['type'], true),
            'data' => json_decode($arr[0]['data'], true),
            'loot' => json_decode($arr[0]['loot'], true),
            'probability' => $arr[0]['probabilityModifier'],
            'totalShares' => $arr[0]['totalShares']
        );

        return $return;
    }

    /**
     * 添加关联角色Id
     * 
     * @param int ...$relCharIds
     * 添加的角色Id
     */
    public function addRelChar(
        ...$relCharIds
    ) {
        foreach ($relCharIds as $k => $charId) {
            if(array_search($charId, $this->relChar) === false) {
                $this->relChar[] = $charId;
            }
        }
    }

    /**
     * 添加成功角色Id
     * 
     * @param int ...$relCharIds
     * 添加的角色Id
     */
    public function addRelSuccess(
        ...$relCharIds
    ) {
        foreach ($relCharIds as $k => $charId) {
            if(array_search($charId, $this->relSuccess) === false) {
                $this->relSuccess[] = $charId;
            }
        }
    }

    /**
     * 添加成功角色Id
     * 
     * @param int ...$relCharIds
     * 添加的角色Id
     */
    public function addRelFailure(
        ...$relCharIds
    ) {
        foreach ($relCharIds as $k => $charId) {
            if(array_search($charId, $this->relFailure) === false) {
                $this->relFailure[] = $charId;
            }
        }
    }

    /**
     * 移除关联角色Id
     * 
     * @param int ...$relCharIds
     * 移除的角色Id
     */
    public function removeRelChar(
        ...$relCharIds
    ) {
        foreach ($relCharIds as $k => $charId) {
            if(array_search($charId, $this->relChar) !== false) {
                unset($this->relChar[array_search($charId, $this->relChar)]);
            }
        }
    }

    /**
     * 移除成功角色Id
     * 
     * @param int ...$relCharIds
     * 移除的角色Id
     */
    public function removeRelSuccess(
        ...$relCharIds
    ) {
        foreach ($relCharIds as $k => $charId) {
            if(array_search($charId, $this->relSuccess) !== false) {
                unset($this->relSuccess[array_search($charId, $this->relSuccess)]);
            }
        }
    }

    /**
     * 移除失败角色Id
     * 
     * @param int ...$relCharIds
     * 移除的角色Id
     */
    public function removeRelFailure(
        ...$relCharIds
    ) {
        foreach ($relCharIds as $k => $charId) {
            if(array_search($charId, $this->relFailure) !== false) {
                unset($this->relFailure[array_search($charId, $this->relFailure)]);
            }
        }
    }

    /**
     * 重设关联角色Id
     */
    public function resetRelChar() {
        $this->relChar = array();
    }

    /**
     * 重设关联成功角色Id
     */
    public function resetRelSuccess() {
        $this->relSuccess = array();
    }

    /**
     * 重设关联失败角色Id
     */
    public function resetRelFailure() {
        $this->relFailure = array();
    }

    /**
     * 创建一个新的冒险
     * 
     * @param string $templateName
     * 冒险模板名称
     * 
     * @param int ...$charIds
     * 参与这次冒险的角色id，可变参数
     * 
     * @return int
     * 返回错误码
     * - 0：成功
     * - 1：冒险模板数据不存在
     * - 2：队伍为空
     * - 3：队伍中有角色已经在其他队伍中了
     * - 4：队伍中有角色的状态不正确（不在营地中）
     * - 5：队伍中有角色的AP不够
     * - 6：队伍中有角色的实力大于要求
     * - 7：队伍中有角色的实力小于要求
     * - 8：队伍人数超员
     * - 9：队伍人数不足
     */
    public function new (
        string $tplName,
        ...$charIds
    ) {
        $this->__construct(); //调用构造方法清空数据
        $this->team->reset(); //重设队伍

        //加载冒险模板数据
        $tpl = $this->db->getArr(
            'adventures',
            array(
                "`name` = '{$tplName}'"
            ),
            null,
            1
        );

        if($tpl === false) {
            \fLog("Adventure template '{$tplName}' doesn't exist in the library");
            return 1;
        }

        //加载队伍成员
        if(empty($charIds)) { //队伍不能为空
            \fLog("Members are required for starting a new adventure");
            return 2;
        }

        $this->templateName = $tplName;
        $this->version = $GLOBALS['meshal']['version']['adventure'];
        $this->coverImage = \fDecode($tpl[0]['coverImage']);
        $this->type = json_decode($tpl[0]['type'], true);
        $this->apCost = $tpl[0]['apCost'];
        $this->teamMin = $tpl[0]['teamMin'];
        $this->teamMax = $tpl[0]['teamMax'];
        $this->strengthMin = $tpl[0]['strengthMin'];
        $this->strengthMax = $tpl[0]['strengthMax'];

        $this->type = json_decode($tpl[0]['type'], true);
        $this->data = json_decode($tpl[0]['data'], true);

        foreach ($charIds as $k => $charId) {
            //检查角色是否已经在其他队伍中
            $query = $this->db->getArr(
                'adventure_chars',
                array(
                    "`charId` = '{$charId}'",
                    "`sealed` = '0'"
                ),
                null,
                1
            );
            if($query !== false) {
                \fLog("Error: Character id={$charId} is already in another team");
                return 3;
            }

            $char = new \meshal\xChar;
            $char->load($charId);

            //检查角色状态是否是null
            if(!is_null($char->stat)) {
                \fLog("Error: Character id={$charId}'s stat is not null");
                return 4;
            }

            //检查AP是否足够
            if(
                !is_null($this->apCost)
                && $char->ap->current < $this->apCost
            ) {
                \fLog("Error: Character id={$charId}'s AP is not enough for the adventure");
                return 5;
            }

            //检查角色实力是否大于冒险要求
            if(
                !is_null($this->strengthMax)
                && $char->strength->st > $this->strengthMax
            ) {
                \fLog("Error: Character id={$charId}'s strength is greater than strengthMax {$this->strengthMax}");
                return 6;
            }

            //检查角色实力是否小于冒险要求
            if(
                !is_null($this->strengthMin)
                && $char->strength->st < $this->strengthMin
            ) {
                \fLog("Error: Character id={$charId}'s strength is less than strengthMin {$this->strengthMin}");
                return 7;
            }

            //检查队伍是否满员
            if(
                !is_null($this->teamMax)
                && $this->team->count >= $this->teamMax
            ) {
                \fLog("Error: All seats of the team are already taken");
                return 8;
            }

            //向队伍添加角色
            $stat = $this->team->add($charId);
            if($stat === false) {
                \fLog("Error: Failed on adding member id={$charId} on creating new adventure");
            }
        }

        //检查队伍是否为空
        if(empty($this->team->members)) {
            \fLog("Error: Failed on creating the new adventure because there is no valid members in the team");
            return 2;
        }

        //检查队伍人数是否比最小要求人数多
        if($this->team->count < $this->teamMin) {
            \fLog("Error: There are not enough members in the team for starting the adventure");
            return 9;
        }

        //扣除队伍中每个角色的ap
        foreach($this->team->members as $charId => $char) {
            $char->ap->sub('current', $this->apCost);
            $char->save();
        }

        //设置冒险时间
        $this->startTime = time();
        
        //用速度最慢的成员决定这次冒险的耗时
        $this->endTime = $this->startTime + intval($tpl[0]['duration'] / $this->team->getMemberScoreLowest('ms')['value'] / $GLOBALS['setting']['adventure']['adventureSpeedFactor']);

        return 0;
    }

    /**
     * 导出这个Adventure为数组
     */
    public function export() {
        $entry = array(
            'version' => $this->version,
            'sealed' => $this->sealed,
            'templateName' => $this->templateName,
            'team' => json_encode($this->team->export()),
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'ledger' => json_encode($this->ledger),
            'log' => $this->logger->export()
        );

        return $entry;
    }

    /**
     * 保存这个冒险到数据库
     */
    public function save() {

        //组装写入的数据
        $entry = $this->export();
        unset($entry['team']); //组队信息不在这里写入

        //检查数据
        $query = $this->db->getArr(
            'adventure_instances',
            array(
                "`id` = '{$this->id}'"
            ),
            null,
            1
        );

        if($query === false) { //不存在则新建
            $insert = $this->db->insert(
                'adventure_instances',
                $entry
            );

            if($insert !== false) {
                $this->id = $insert;
                \fLog("New adventure id={$insert} has been created");

                //保存角色与冒险的关系到adventure_chars表
                foreach ($this->team->members as $charId => $char) {
                    $this->db->insert(
                        'adventure_chars',
                        array(
                            'charId' => $charId,
                            'uid' => $char->owner->uid,
                            'adventureId' => $this->id,
                            'startTime' => $this->startTime,
                            'endTime' => $this->endTime
                        )
                    );
                    $char->stat = 'adventure';
                    $char->save();
                }

                return true;
            } else {
                \fLog("New adventure id={$insert} has faild on creating");
                return false;
            }
        } else { //存在则更新
            $stat = $this->db->update(
                'adventure_instances',
                $entry,
                array(
                    "`id` = '{$this->id}'"
                ),
                1
            );

            if($stat === false) {
                \fLog("Error while updating adventure id={$this->id}");
                return false;
            }

            //更新角色与冒险的关系到adventure_chars表
            $this->db->delete( //从数据库中删除所有本冒险的组队角色
                'adventure_chars',
                array(
                    "`adventureId` = '{$this->id}'"
                )
            );

            foreach ($this->team->members as $charId => $char) { //遍历队伍并重新保存
                $this->db->insert(
                    'adventure_chars',
                    array(
                        'charId' => $charId,
                        'uid' => $char->owner->uid,
                        'adventureId' => $this->id,
                        'startTime' => $this->startTime,
                        'endTime' => $this->endTime
                    )
                );
                $char->stat = 'adventure';
                $char->save();
            }

            return true;
        }
    }

    /**
     * 加载一个冒险实例
     * 
     * @param int $adventureId
     * 要加载的冒险实例Id
     */
    public function load(
        int $adventureId
    ) {
        $this->__construct(); //调用构造方法清空数据

        //加载冒险实例
        $arr = $this->db->getArr(
            'adventure_instances',
            array(
                "`id` = '{$adventureId}'"
            ),
            null,
            1
        );

        if($arr === false) {
            \fLog("Adventure id={$adventureId} doesn't exist in database");
            return false;
        }

        $this->id = $adventureId;
        $this->version = $arr[0]['version'];
        $this->sealed = $arr[0]['sealed'];
        $this->startTime = $arr[0]['startTime'];
        $this->endTime = $arr[0]['endTime'];
        $this->ledger = json_decode($arr[0]['ledger'], true);
        $this->templateName = $arr[0]['templateName'];

        //加载队伍信息
        $teamList = $this->db->getArr(
            'adventure_chars',
            array(
                "`adventureId` = '{$this->id}'"
            )
        );

        if($teamList !== false) { //遍历$teamList并添加成员
            foreach ($teamList as $k => $charData) {

                #只有sealed == false的冒险才需要加载完整的成员信息，否则只加载id以提升性能
                if($this->sealed == false) {
                    $stat = $this->team->add($charData['charId']);
                } else {
                    $stat = $this->team->addId($charData['charId']);
                }
                
                if($stat === false) {
                    \fLog("Error: something went wrong while adding team member {$charData['charId']}");
                } else {
                    \fLog("Character id={$charData['charId']} is added to adventure id={$this->id}'s team");
                }
            }
        }
        
        //加载冒险模板数据
        $tpl = $this->db->getArr(
            'adventures',
            array(
                "`name` = '{$this->templateName}'"
            ),
            null,
            1
        );

        if($tpl === false) {
            \fLog("Error: adventure template '{$this->templateName}' doesn't exist in the library");
            return false;
        }

        $this->apCost = $tpl[0]['apCost'];
        $this->teamMin = $tpl[0]['teamMin'];
        $this->teamMax = $tpl[0]['teamMax'];
        $this->strengthMin = $tpl[0]['strengthMin'];
        $this->strengthMax = $tpl[0]['strengthMax'];
        $this->probability = $tpl[0]['probabilityModifier'];

        $this->coverImage = \fDecode($tpl[0]['coverImage']);
        $this->type = json_decode($tpl[0]['type'], true);
        $this->data = json_decode($tpl[0]['data'], true);

        return true;
    }

    /**
     * 向渲染器添加操作
     * 通过这个，可以添加一些诸如查看log的操作（由具体使用该冒险的页面添加）
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
     *  'owner'：这个冒险的拥有者可见
     *  'creator'：这个冒险的创建者可见
     *  'guest'：非这个冒险的拥有者和创建者可见
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
     * 当冒险的stat在这些状态时，不添加操作
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
            'alwaysShow' => $alwaysShow == false ? '' : 'adventure-controller-alwaysShow',
            'auth' => array_flip($auth)
        );
    }

    /**
     * 渲染冒险卡片(实例)
     * 
     * @param bool $controller = true
     * 是否显示控制器
     * 
     * @param object &$user = null
     * 当前查看该冒险的用户对象
     * 
     * @param string $css = ''
     * 额外向这个冒险注入的css class内容
     * 
     * @param bool $ignoreSealed = false
     * 是否忽略这个冒险的sealed状态
     */
    public function render(
        bool $controller = true,
        \xUser &$user = null,
        string $css = '',
        bool $ignoreSealed = false
    ) {
        $renderer = new \xHtml;
        
        if(
            $this->sealed == 0
            || $ignoreSealed === true
        ) {
            $renderer->set('--sealed', '');
            $renderer->set('--sealed-bg', '');
        } else {
            $renderer->set('--sealed', 'adventure-cardFrame-sealed');
            $renderer->set('--sealed-bg', 'adventure-cardFrame-bg-sealed');
        }

        //冒险封面图目录
        $renderer->set('!dirAdventureCover', _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover']); 

        //基本数据
        $renderer->set('--adventureId', $this->id);
        // $renderer->set('--stat', $this->sealed == true ? 'sealed' : 'ongoing');
        $renderer->set('--adventureName', "{?adventureName.{$this->templateName}?}");
        $renderer->set('--coverImage',
            (
                is_null($this->coverImage) || $this->coverImage == ''
                || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$this->coverImage)
            )
                ? "{?!dirImg?}adventureCover.default.jpg"
                : "{?!dirAdventureCover?}{$this->coverImage}"
        );
        $renderer->set('--description', "{?adventureDesc.{$this->templateName}?}");
        $renderer->set('--countdown', \fFormatTime($this->endTime));

        //组装冒险类型
        if(!empty($this->type)) {
            $types = array();
            foreach($this->type as $k => $t) {
                $types[] = "{?adventureType.{$t}?}";
            }
            $renderer->set('--adventureType', implode('{?common.comma?}', $types));
        } else {
            $renderer->set('--adventureType', '');
        }

        //组装冒险人员
        if(!empty($this->team->members)) {
            $memberList = '';
            foreach($this->team->members as $memberId => $char) {
                $memberList .= \meshal\xChar::renderTag($memberId);
            }
            $renderer->set('--team', $memberList);
        } else {
            $renderer->set('--team', '{?common.none?}');
        }

        //组装物品收获
        if(
            $this->sealed == true
            && !empty($this->ledger['item'])
        ) {
            $lootList = array();
            foreach ($this->ledger['item'] as $charId => $itemList) {
                $itemComp = array(
                    '--charTag' => \meshal\xChar::renderTag($charId),
                    '--itemList' => ''
                );
                foreach($itemList as $itemName => $itemAmount) {
                    $itemComp['--itemList'] .= \meshal\xItem::renderTag($itemName, $itemAmount);
                }
                $lootList[$charId] = $itemComp;
            }

            $renderer->set(
                '--itemFound',
                $renderer->duplicate(
                    'adventure/instance.dup.itemFound.html',
                    $lootList
                )
            );
            // fPrint($renderer->var['--itemFound']);
            $renderer->set('--showItemFound', '');
        } else {
            $renderer->set('--showItemFound', 'hidden');
            $renderer->set('--itemFound', '');
        }

        //倒计时
        if($this->sealed == false) {
            $renderer->set('--showCountdown', '');
        } else {
            $renderer->set('--showCountdown', 'hidden');
        }

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
                            '--adventureId' => $this->id,
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
                            '--adventureId' => $this->id,
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
                            '--adventureId' => $this->id,
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
                            '--adventureId' => $this->id,
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
                        'adventure/instance.controller.html',
                        $ctrl
                    )
                );
            } else {
                $renderer->set('$controller', '');
            }
        }

        $renderer->loadTpl('adventure/instance.frame.html');
        return $renderer->render('body');
    }

    /**
     * 对一个冒险实例做html渲染(tag版)
     * @param int $id
     * 
     * @param string target = ''
     */
    public static function renderTag(
        int $id,
        string $target = ''
    ) {
        global $db;

        $renderer = new \xHtml;
        $query = $db->getArr(
            'adventure_instances',
            array(
                "`id` = '{$id}'"
            ),
            null,
            1
        );

        //查无此冒险则返回false并记录错误
        if($query === false) {
            \fLog("The adventure({id=$id}) doesn't exist in database");
            return false;
        }

        //角色查看器URL
        $renderer->set('--viewerUrl', "{?!dirRoot?}a/?id={$id}");
        $renderer->set('--target', $target);

        //基本数据
        $renderer->set('--adventureId', $id);
        $renderer->set('--adventureName', "{?adventureName.{$query[0]['templateName']}?}");
        $renderer->set('--date', \fFormatTime($query[0]['endTime']));

        $renderer->loadTpl('adventure/tag.html');

        return $renderer->render(
            'body'
        );
    }

    /**
     * 渲染冒险模板
     */
    public function renderTpl(
        string $templateName,
        bool $controller = true,
        \xUser &$user = null,
        string $css = ''
    ) {
        $renderer = new \xHtml;
        $tpl = self::getData($templateName);
        
        
        //冒险封面图目录
        $renderer->set('!dirAdventureCover', _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover']); 

        //基本数据
        $renderer->set('--adventureName', "{?adventureName.{$tpl['name']}?}");
        $renderer->set('--coverImage',
            (
                is_null($tpl['coverImage']) || $tpl['coverImage'] == ''
                || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['adventureCover'].$tpl['coverImage'])
            )
                ? "{?!dirImg?}adventureCover.default.jpg"
                : "{?!dirAdventureCover?}{$tpl['coverImage']}"
        );
        $renderer->set('--description', "{?adventureDesc.{$tpl['name']}?}");
        $renderer->set('--duration', \fFormatTime(intval($tpl['duration'] / $GLOBALS['setting']['adventure']['adventureSpeedFactor'])), 'hour');

        //组装冒险类型
        if(!empty($tpl['type'])) {
            $types = array();
            foreach($tpl['type'] as $k => $t) {
                $types[] = "{?adventureType.{$t}?}";
            }
            $renderer->set('--adventureType', implode('{?common.comma?}', $types));
        } else {
            $renderer->set('--adventureType', '');
        }

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
                            '--adventureId' => $this->id,
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
                            '--adventureId' => $this->id,
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
                            '--adventureId' => $this->id,
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
                            '--adventureId' => $this->id,
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
                        'adventure/instance.controller.html',
                        $ctrl
                    )
                );
            } else {
                $renderer->set('$controller', '');
            }
        }

        $renderer->loadTpl('adventure/tpl.frame.html');
        return $renderer->render('body');
    }

    /**
     * 开始这个冒险
     * 
     * @return int
     * 返回冒险开始的错误码
     * - 0：没有错误
     * - 1：时间还没有到，未触发冒险结算
     * - 2：冒险已经结算过了
     * - 3：冒险数据为空
     * - 4：队伍数据为空
     * - 5：冒险入口数据为空
     * - 6：冒险场景数据为空
     */
    public function start() {
        //检查当前时间是否晚于endTime
        if($this->endTime > time()) {
            return 1;
        }

        //检查冒险是否已经结算过了
        if($this->sealed == 1) {
            \fLog("This adventure has already sealed");
            return 2;
        }

        //检查冒险数据
        if(empty($this->data)) {
            \fLog("Error: adventure data is not set");
            return 3;
        }

        //检查队伍数据
        if(empty($this->team->members)) {
            \fLog("Error: team is empty");
            return 4;
        }

        /**
         * 决定处理冒险的入口
         */
        if(empty($this->data['entrance'])) { //检查冒险模板的入口数据有效性
            \fLog("Error: no entrance data set in adventure template {$this->templateName}");
            return 5;
        }
        if(empty($this->data['scenes'])) { //检查冒险模板的遭遇数据有效性
            \fLog("Error: no scenes data set in adventure template {$this->templateName}");
            return 6;
        }

        $entranceList = array();
        $logBuff = array();
        $this->logger->addScene('@start'); //'@start' 为冒险入口场景的特殊标识
        foreach($this->data['entrance'] as $sceneId => $sceneData) {
            $logBuff[$sceneId]['encounter'] = $this->data['scenes'][$sceneId]['encounter'];
            //进行前提检查(All部分)
            $checkAll = 1;
            if(!empty($sceneData['checkAll'])) {
                foreach ($sceneData['checkAll'] as $k => $params) {
                    $method = array_shift($params);
                    if(method_exists($this->checker, $method)) {
                        $check = $this->checker->$method(...$params); //运行检查器
                        $checkAll *= $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAll
                        // $this->log("adventure.entrance.checkAll.{$method}", '', $check['log']);
                        $logBuff[$sceneId]['checkAll'][] = array(
                            'method' => $method,
                            'result' => $check['detail']
                        );
                    } else {
                        \fLog("Method {$method} doesn't exist in the checker");
                    }
                }
            }
            \fLog("checkAll = {$checkAll}");

            //进行前提检查(Any部分)
            $checkAny = 1;
            if(!empty($sceneData['checkAny'])) {
                $checkAny = 0;
                foreach ($sceneData['checkAny'] as $k => $params) {
                    // if($checkAny > 0) break; //提升性能
                    $method = array_shift($params);
                    if(method_exists($this->checker, $method)) {
                        $check = $this->checker->$method(...$params); //运行检查器
                        $checkAny += $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAny
                        // $this->log("adventure.entrance.checkAny.{$method}", '', $check['log']);
                        $logBuff[$sceneId]['checkAny'][] = array(
                            'method' => $method,
                            'result' => $check['detail']
                        );
                    } else {
                        \fLog("Method {$method} doesn't exist in the checker");
                    }
                }
            }
            \fLog("checkAny = {$checkAny}");

            //检查通过，将sceneId添加到随机清单
            if($checkAll * $checkAny > 0) {
                $entranceList[$sceneId] = $sceneData['probability'];
                $logBuff[$sceneId]['summary'] = true;
            } else {
                $logBuff[$sceneId]['summary'] = false;
            }
        }

        $this->logger->addEvent('checkEntrance', $logBuff);

        //获取本次冒险入口的sceneId并执行遭遇
        $entrance = \fArrayRandWt($entranceList)[0];
        // $this->log("adventure.entrance.start", $this->data['scenes'][$entrance]['encounter'], array());
        $this->logger->addEvent(
            'gotoEntrance',
            array(
                'sceneId' => $entrance,
                'encounter' => $this->data['scenes'][$entrance]['encounter']
            )
        );
        \fLog("Adventure: entrance = {$entrance}");
        $this->execute($entrance);

        //向epoch访问统计表加数据
        \fEpochAdd(
            'adventure',
            $this->templateName
        );
        return 0;
    }

    /**
     * 运行一个冒险场景
     * 
     * @param int $sceneId
     * 冒险场景Id
     * 
     * @return int
     * 执行冒险场景的返回错误码
     * - 0：没有错误
     * - 1：场景在配置中不存在
     */
    private function execute(
        int $sceneId
    ) {
        //检查是否有对应有效配置
        if(
            !$this->data['scenes'][$sceneId]
            || !is_array($this->data['scenes'][$sceneId])
        ) {
            \fLog("Error: scene({$sceneId}) doesn't exist");
            return 1;
        }

        //获取encounter配置
        // $encounterData = \meshal\adventure\xEncounter::getData($this->data['scenes'][$sceneId]['encounter']);

        $this->logger->addScene($this->data['scenes'][$sceneId]['encounter']);

        //遭遇处理器加载模板数据
        $this->encounter->load($this->data['scenes'][$sceneId]['encounter']);

        //重设关联角色
        $this->resetRelChar();
        $this->resetRelSuccess();
        $this->resetRelFailure();

        //执行遭遇处理器
        $encounterReturn = $this->encounter->execute();

        //触发下一个场景
        if( //配置中如果为空，表示没有下一个场景，触发结束事件
            is_null($this->data['scenes'][$sceneId]['next'])
            || empty($this->data['scenes'][$sceneId]['next'])
        ) {
            $this->logger->addEvent('lastSceneReached', array());
            $this->end();
        } else { //有next配置，根据上一个遭遇的执行返回结果决定下一个场景
            $nextDefault = true;

            //触发success
            if(
                $encounterReturn === true
                && !empty($this->data['scenes'][$sceneId]['next']['success'])
            ) { 
                $logBuff = array('type' => 'success');
                $nextList = array();
                foreach($this->data['scenes'][$sceneId]['next']['success'] as $sceneId => $sceneData) {
                    $logBuff['scenes'][$sceneId]['encounter'] = $this->data['scenes'][$sceneId]['encounter'];
                    //前提检查（all部分）
                    $checkAll = 1;
                    if(!empty($sceneData['checkAll'])) {
                        foreach ($sceneData['checkAll'] as $k => $params) {
                            $method = array_shift($params);
                            if(method_exists($this->checker, $method)) {
                                $check = $this->checker->$method(...$params); //运行检查器
                                $checkAll *= $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAll
                                // $this->log("adventure.nextSuccess.checkAll.{$method}", '', $check['log']); //记录log
                                $logBuff['scenes'][$sceneId]['checkAll'][] = array(
                                    'method' => $method,
                                    'result' => $check['detail']
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the checker");
                            }
                        }
                    }
                    \fLog("checkAll = {$checkAll}");

                    //前提检查（any部分）
                    $checkAny = 1;
                    if(!empty($sceneData['checkAny'])) {
                        $checkAny = 0;
                        foreach ($sceneData['checkAny'] as $k => $params) {
                            // if($checkAny > 0) break; //提升性能
                            $method = array_shift($params);
                            if(method_exists($this->checker, $method)) {
                                $check = $this->checker->$method(...$params); //运行检查器
                                $checkAny += $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAny
                                // $this->log("adventure.nextSuccess.checkAny.{$method}", '', $check['log']); //记录log
                                $logBuff['scenes'][$sceneId]['checkAny'][] = array(
                                    'method' => $method,
                                    'result' => $check['detail']
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the checker");
                            }
                        }
                    }
                    \fLog("checkAny = {$checkAny}");

                    //检查通过，将sceneId添加到随机清单
                    if($checkAll * $checkAny > 0) {
                        $nextList[$sceneId] = $sceneData['probability'];
                        $logBuff['scenes'][$sceneId]['summary'] = true;
                    } else {
                        $logBuff['scenes'][$sceneId]['summary'] = false;
                    }
                }

                if(!empty($nextList)) {
                    $nextDefault = false;
                    $this->logger->addEvent('checkNextScene', $logBuff);
                    \fLog("Adventure: trigger next scene as a success, list=".\fDump($nextList));
                }
            }

            //触发failure
            elseif(
                $encounterReturn === false
                && !empty($this->data['scenes'][$sceneId]['next']['failure'])
            ) { 
                $logBuff = array('type' => 'failure');
                $nextList = array();
                foreach($this->data['scenes'][$sceneId]['next']['failure'] as $sceneId => $sceneData) {
                    $logBuff['scenes'][$sceneId]['encounter'] = $this->data['scenes'][$sceneId]['encounter'];
                    //前提检查（all部分）
                    $checkAll = 1;
                    if(!empty($sceneData['checkAll'])) {
                        foreach ($sceneData['checkAll'] as $k => $params) {
                            $method = array_shift($params);
                            if(method_exists($this->checker, $method)) {
                                $check = $this->checker->$method(...$params); //运行检查器
                                $checkAll *= $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAll
                                // $this->log("adventure.nextFailure.checkAll.{$method}", '', $check['log']);
                                $logBuff['scenes'][$sceneId]['checkAll'][] = array(
                                    'method' => $method,
                                    'result' => $check['detail']
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the checker");
                            }
                        }
                    }
                    \fLog("checkAll = {$checkAll}");

                    //前提检查（any部分）
                    $checkAny = 1;
                    if(!empty($sceneData['checkAny'])) {
                        $checkAny = 0;
                        foreach ($sceneData['checkAny'] as $k => $params) {
                            // if($checkAny > 0) break; //提升性能
                            $method = array_shift($params);
                            if(method_exists($this->checker, $method)) {
                                $check = $this->checker->$method(...$params); //运行检查器
                                $checkAny += $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAny
                                // $this->log("adventure.nextFailure.checkAny.{$method}", '', $check['log']);
                                $logBuff['scenes'][$sceneId]['checkAny'][] = array(
                                    'method' => $method,
                                    'result' => $check['detail']
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the checker");
                            }
                        }
                    }
                    \fLog("checkAny = {$checkAny}");

                    //检查通过，将sceneId添加到随机清单
                    if($checkAll * $checkAny > 0) {
                        $nextList[$sceneId] = $sceneData['probability'];
                        $logBuff['scenes'][$sceneId]['summary'] = true;
                    } else {
                        $logBuff['scenes'][$sceneId]['summary'] = false;
                    }
                }

                if(!empty($nextList)) {
                    $nextDefault = false;
                    $this->logger->addEvent('checkNextScene', $logBuff);
                    \fLog("Adventure: trigger next scene as a failure, list=".\fDump($nextList));
                }
            }

            //触发default
            if(
                (
                    $nextDefault === true
                    || empty($nextList)
                )
                && !empty($this->data['scenes'][$sceneId]['next']['default'])
            ) {
                $logBuff = array('type' => 'default');
                $nextList = array();
                foreach($this->data['scenes'][$sceneId]['next']['default'] as $sceneId => $sceneData) {
                    $logBuff['scenes'][$sceneId]['encounter'] = $this->data['scenes'][$sceneId]['encounter'];
                    //前提检查（all部分）
                    $checkAll = 1;
                    if(!empty($sceneData['checkAll'])) {
                        foreach ($sceneData['checkAll'] as $k => $params) {
                            $method = array_shift($params);
                            if(method_exists($this->checker, $method)) {
                                $check = $this->checker->$method(...$params); //运行检查器
                                $checkAll *= $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAll
                                // $this->log("adventure.next.checkAll.{$method}", '', $check['log']);
                                $logBuff['scenes'][$sceneId]['checkAll'][] = array(
                                    'method' => $method,
                                    'result' => $check['detail']
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the checker");
                            }
                        }
                    }
                    \fLog("checkAll = {$checkAll}");

                    //前提检查（any部分）
                    $checkAny = 1;
                    if(!empty($sceneData['checkAny'])) {
                        $checkAny = 0;
                        foreach ($sceneData['checkAny'] as $k => $params) {
                            $method = array_shift($params);
                            if($checkAny > 0) break; //提升性能
                            if(method_exists($this->checker, $method)) {
                                $check = $this->checker->$method(...$params); //运行检查器
                                $checkAny += $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAny
                                // $this->log("adventure.next.checkAny.{$method}", '', $check['log']);
                                $logBuff['scenes'][$sceneId]['checkAny'][] = array(
                                    'method' => $method,
                                    'result' => $check['detail']
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the checker");
                            }
                        }
                    }
                    \fLog("checkAny = {$checkAny}");

                    //检查通过，将sceneId添加到随机清单
                    if($checkAll * $checkAny > 0) {
                        $nextList[$sceneId] = $sceneData['probability'];
                        $logBuff['scenes'][$sceneId]['summary'] = true;
                    } else {
                        $logBuff['scenes'][$sceneId]['summary'] = false;
                    }
                }

                $this->logger->addEvent('checkNextScene', $logBuff);
                \fLog("Adventure: trigger next scene as a default, list=".\fDump($nextList));
            }

            //进行执行
            if(empty($nextList)) { //如果$nextList没有场景，那么冒险结束
                $this->logger->addEvent('lastSceneReached', array());
                $this->end();
            } else { //否则执行下一个场景
                //随机一个场景的sceneId并执行
                $next = \fArrayRandWt($nextList)[0];
                // $this->log('adventure.gotoNext', '', array('scene' => $next));
                $this->logger->addEvent(
                    'gotoNextScene',
                    array(
                        'sceneId' => $next,
                        'encounter' => $this->data['scenes'][$next]['encounter']
                    )
                );
                $this->execute($next);
            }
        }
        return 0;
    }

    /**
     * 结束这个冒险，保存进度并且释放其中的角色
     */
    public function end() {

        $this->logger->addScene('@end'); //'@end' 为冒险结束场景的特殊标识
        if(!empty($this->data['end'])) {
            
            foreach($this->data['end'] as $endId => $endData) { //遍历end配置并逐个处理
                $logBuff = array();
                //前提检查（all部分）
                $checkAll = 1;
                if(!empty($endData['checkAll'])) {
                    foreach ($endData['checkAll'] as $k => $params) {
                        // fPrint($params);
                        $method = array_shift($params);
                        if(!is_string($method)) {
                            \fLog("Error: {$this->name} \$method is not a string");
                            \fLog(\fDump($method));
                        }
                        if(method_exists($this->checker, $method)) {
                            $check = $this->check->$method(...$params); //运行检查器
                            $checkAll *= $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAll
                            // $this->log("adventure.end.checkAll.{$method}", '', $check['log']);
                            $logBuff['checkAll'][] = array(
                                'method' => $method,
                                'result' => $check['detail']
                            );
                        } else {
                            \fLog("Method {$method} doesn't exist in the checker");
                        }
                    }
                }
                \fLog("checkAll = {$checkAll}");

                //前提检查（any部分）
                $checkAny = 1;
                if(!empty($endData['checkAny'])) {
                    $checkAny = 0;
                    foreach ($endData['checkAny'] as $k => $params) {
                        // fPrint($params);
                        $method = array_shift($params);
                        if(!is_string($method)) {
                            \fLog("Error: {$this->name} \$method is not a string");
                            \fLog(\fDump($method));
                        }
                        if(method_exists($this->checker, $method)) {
                            $check = $this->check->$method(...$params); //运行检查器
                            $checkAny += $check['summary'] === true ? 1 : 0; //根据检查结果累加checkAny
                            // $this->log("adventure.end.checkAny.{$method}", '', $check['log']);
                            $logBuff['checkAny'][] = array(
                                'method' => $method,
                                'result' => $check['detail']
                            );
                        } else {
                            \fLog("Method {$method} doesn't exist in the checker");
                        }
                    }
                }
                \fLog("checkAny = {$checkAny}");

                //检查通过，执行success配置
                if($checkAll * $checkAny > 0) {
                    $logBuff['summary'] = true;
                    if(!empty($endData['success'])) {
                        foreach ($endData['success'] as $k => $params) {
                            // fPrint($params);
                            $method = array_shift($params);
                            if(!is_string($method)) {
                                \fLog("Error: {$this->name} \$method is not a string");
                                \fLog(\fDump($method));
                            }
                            if(method_exists($this->executor, $method)) {
                                $result = $this->executor->$method(...$params);
                                // $this->log("adventure.end.success.{$method}", '', $result['log']);
                                $logBuff['execute'][] = array(
                                    'method' => $method,
                                    'result' => $result
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the executor");
                            }
                        }
                    }
                } 
                //检查失败，执行failure配置
                else {
                    $logBuff['summary'] = false;
                    if(!empty($endData['failure'])) {
                        foreach ($endData['failure'] as $k => $params) {
                            // fPrint($params);
                            $method = array_shift($params);
                            if(!is_string($method)) {
                                \fLog("Error: {$this->name} \$method is not a string");
                                \fLog(\fDump($method));
                            }
                            if(method_exists($this->executor, $method)) {
                                $result = $this->executor->$method(...$params);
                                // $this->log("adventure.end.failure.{$method}", '', $result['log']);
                                $logBuff['execute'][] = array(
                                    'method' => $method,
                                    'result' => $result
                                );
                            } else {
                                \fLog("Method {$method} doesn't exist in the executor");
                            }
                        }
                    }
                }
                $this->logger->addEvent(
                    'endEvent',
                    $logBuff
                );
                
            }

            $this->logger->addEvent( //打上结束标志
                'endAdventure',
                array()
            );
        }
        $this->sealed = 1; //将这个冒险的状态设为结束
        
        // $this->log('adventure.endOfLog', '', array()); //记录最后一条log

        $this->save(); //保存冒险

        $this->db->update( //从数据库中更新所有本冒险记录的状态
            'adventure_chars',
            array(
                'sealed' => true
            ),
            array(
                "`adventureId` = '{$this->id}'",
            )
        );

        //将队伍中的角色状态设为休息中
        foreach ($this->team->members as $charId => $char) {
            $char->stat = null;
        }

        //将冒险收获放到用户的inventory
        $lootList = array();
        if(!empty($this->ledger['item'])) {
            foreach ($this->ledger['item'] as $charId => $itemList) {
                foreach($itemList as $itemName => $amount) {
                    $this->team->members[$charId]->inventory->discard($itemName, $amount, true);
                    $lootList[$this->team->members[$charId]->owner->uid][$itemName] += $amount;
                }
            }
        }

        //保存每个角色
        foreach ($this->team->members as $charId => $char) {
            $char->save();
        }

        //准备向char的owners发消息
        $msg = array();
        /*
        $msg => array(
            ownerId1 => array(
                char => array(
                    charId1 => charName1,
                    ...
                ),
                potentiality => array(
                    charId1 => ppAmount1,
                    ...
                ),
                item => array(
                    itemId1 => itemAmount1,
                    ...
                )
            ),
            ...
        )
        */

        $ownerList = $this->team->getOwnerListDistinct();
        foreach($ownerList as $ownerId => $d) {
            $msg[$ownerId] = array(
                'char' => array(),
                'potentiality' => array(),
                'item' => $lootList[$ownerId]
            );
        }

        foreach($this->team->members as $charId => $char) {
            $msg[$char->owner->uid]['char'][$charId] = $char->name;
            $msg[$char->owner->uid]['potentiality'][$charId] = $this->ledger['potentiality'][$charId];
        }

        //分别发消息给各角色的拥有者
        foreach($msg as $ownerId => $msgData) {
            //组装物品清单
            if(!empty($msgData['item'])) {
                $lootSum = array();
                foreach ($msgData['item'] as $itemName => $itemAmount) {
                    $lootSum[] = "{?common.messageBullet?}{?itemName.{$itemName}?} {?common.item.amountSymbol?}{$itemAmount}";
                }
                $lootMsg = '{?message.adventure.loot?}';
                $lootSummary = implode('{?common.comma?}', $lootSum);
            } else {
                $lootMsg = '{?message.adventure.noLoot?}';
                $lootSummary = '';
            }

            //组装潜能清单
            if(!empty($msgData['potentiality'])) {
                $ppSum = array();
                foreach ($msgData['potentiality'] as $charId => $ppAmount) {
                    if($ppAmount > 0) {
                        $ppSum[] = "{?common.messageBullet?}{$this->team->members[$charId]->name} {?common.scoreMod.add?}{$ppAmount}";
                    }
                }
            }
            if(!empty($ppSum)) {
                $ppMsg = '{?message.adventure.potentiality?}';
                $ppSummary = implode('{?common.comma?}', $ppSum);
            } else {
                $ppMsg = '';
                $ppSummary = '';
            }

            /*
            {?$characterNames?} returned from {?$adventureName?}. {?$ppMsg?} {?$ppSummary?} {?$lootDetail?} {?$lootSummary?}
            */
            \fMsg(
                $ownerId,
                'adventure',
                'message.adventure.end',
                array(
                    '$adventureName' => "{?adventureName.{$this->templateName}?}",
                    '$characterNames' => implode('{?common.comma?}', $msgData['char']),
                    '$lootMsg' => $lootMsg,
                    '$lootSummary' => $lootSummary,
                    '$ppMsg' => $ppMsg,
                    '$ppSummary' => $ppSummary
                ),
                $this->endTime
            );
        }
    }

    /**
     * 渲染日志记录
     */
    public function renderLog() {
        if(\fCheckVersion($this->version, $GLOBALS['meshal']['version']['adventure']) == -1) {
            return false;
        }
        $data = $this->getInstance($this->id);
        $return = '';
        $renderer = new \meshal\adventure\xLogRenderer($this);
        //遍历所有log
        foreach($data['log'] as $logIdx => $data) {
            switch ($data['scene']) {
                //冒险开始
                case '@start':
                    $return .= $renderer->event_adventureStart($this->templateName);
                    if(!empty($data['events'])) {
                        foreach($data['events'] as $idx => $event) {
                            $method = 'event_'.$event['event'];
                            if(method_exists($renderer, $method)) {
                                $return .= $renderer->$method($event['data']);
                            }
                        }
                    }
                    break;

                //冒险结束
                case '@end':
                    $return .= $renderer->event_adventureEnd($this->templateName);
                    if(!empty($data['events'])) {
                        foreach($data['events'] as $idx => $event) {
                            $method = 'event_'.$event['event'];
                            if(method_exists($renderer, $method)) {
                                $return .= $renderer->$method($event['data']);
                            }
                        }
                    }
                    break;
                
                //冒险中的scene
                default:
                    //渲染遭遇
                    $return .= $renderer->event_encounterProcess($this->templateName, $data['scene']);

                    //遍历遭遇中的每一个事件并渲染
                    if(!empty($data['events'])) {
                        foreach($data['events'] as $idx => $event) {
                            $method = 'event_'.$event['event'];
                            if(method_exists($renderer, $method)) {
                                $return .= $renderer->$method($event['data'], $this->templateName, $data['scene']);
                            }
                        }
                    }
                    break;
            }
        }

        //返回所有渲染好的结果
        return $return;
    }

    /**
     * 为这次冒险记账（潜能）
     * 
     * @param int $charId
     * 获得潜能的角色
     * 
     * @param int $amount
     * 获得潜能的数量
     */
    public function addLedgerPotentiality(
        int $charId,
        int $amount
    ) {
        $this->ledger['potentiality'][$charId] += $amount;
    }

    /**
     * 为这次冒险记账（物品）
     * 
     * @param int $charId
     * 获得物品的角色
     * 
     * @param string $itemName
     * 获得的物品名称
     * 
     * @param int $amount = 1
     * 获得的物品数量
     */
    public function addLedgerItem(
        int $charId,
        string $itemName,
        int $amount = 1
    ) {
        $this->ledger['item'][$charId][$itemName] += $amount;
    }
}
?>