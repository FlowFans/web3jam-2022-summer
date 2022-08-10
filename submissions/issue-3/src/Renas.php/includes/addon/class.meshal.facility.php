<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal设施的类
################################################


class xFacility
{
    function __construct()
    {
        $this->amount = 0;

        $this->name = '';
        $this->level = 0;
        $this->lastUpdate = 0;

        $this->image = '';

        $this->data = array();
        $this->dataNextLevel = array();
        $this->buildCheckAll = array();
        $this->buildCheckAny = array();
        $this->buildMaterial = array();
        $this->efx = array();

        $this->probabilityModifier = 0; 
        $this->controller = array(); //用户操作
        $this->buildCountdown = null; //建造倒计时
    }

    /**
     * 从数据库中获取设施模板的资料
     * 
     * @param string $facilityName
     * 查询的设施名称
     * 
     * @param int $facilityLevel = 1
     * 设施等级
     * 
     * @return mixed
     * 如果没有查到，返回false；
     * 如果查到了物品，以数组返回包含该设施的数据
     */
    public static function getData (
        string $facilityName,
        int $facilityLevel = 1
    ) {
        global $db;
        $arr = $db->getArr(
            'facilities',
            array(
                "`name` = '{$facilityName}'",
                "`level` = '{$facilityLevel}'"
            ),
            NULL,
            1
        );

        if($arr === false) {
            \fLog("Facility {$facilityName}({$facilityLevel}) doesn't exist in library");
            return false;
        }

        $data = json_decode($arr[0]['data'], true);

        $return = array(
            'fullname' => "meshal.facility.{$facilityName}",
            'name' => $facilityName,
            'level' => $facilityLevel,
            'lastUpdate' => $arr[0]['lastUpdate'],
            'image' => \fDecode($arr[0]['image']),
            'data' => $data,
            'totalShares' => $arr[0]['totalShares']
        );

        return $return;
    }

    /**
     * 取一个设施名字下的最高等级
     * 
     * @param string $facilityName
     * 设施名称
     * 
     * @return int
     * 返回设施的最高等级
     * 如果返回0则表示设施不存在
     */
    public static function getMaxLevel(
        string $facilityName
    ) {
        global $db;

        $query = $db->getArr(
            'facilities',
            array(
                "`name` = '{$facilityName}'"
            ),
            null,1,null,
            '`level`',
            'DESC'
        );

        if($query === false) {
            \fLog("Facility {$facilityName} doesn't exist in library");
            return 0;
        } else {
            return $query[0]['level'];
        }
    }

    /**
     * 加载设施
     * 
     * @param string $facilityName
     * 要加载的设施代码
     * 
     * @param int $facilityLevel
     * 要加载的设施等级
     * 
     * @return bool
     * 加载状态，成功为true，失败为false
     */
    public function load(
        string $facilityName,
        int $facilityLevel = 1
    ) {
        $this->__construct();

        $data = self::getData($facilityName, $facilityLevel);
        $nextLevel = self::getData($facilityName, $facilityLevel + 1);

        if($data === false) return false;

        $this->name = $data['name'];
        $this->level = $data['level'];
        $this->lastUpdate = $data['lastUpdate'];
        $this->image = $data['image'];
        $this->data = $data['data'];
        $this->dataNextLevel = $nextLevel === false ? array() : $nextLevel['data'];
        $this->buildCheckAll = $data['data']['build']['checkAll'];
        $this->buildCheckAny = $data['data']['build']['checkAny'];
        $this->buildMaterial = $data['data']['build']['material'];
        $this->efx = $data['data']['efx'];
        // $this->totalShares = $data['totalShares'];
        // $this->probabilityModifier = $data['probabilityModifier'];

        return true;
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
     * 默认为array('any');
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
        $auth = array('any'),
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

    public function render(
        bool $showNextLevel = true,
        \xUser &$user = null,
        string $template = 'facility/card.frame.html',
        string $css = ''
    ) {
        global $user;
        global $db;
        $renderer = new \xHtml;

        //设施图片目录
        $renderer->set('!dirFacilityImage', _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['facilityImage']); //用户上传的设施图片目录

        $renderer->set('--facilityName', "{?facilityName.{$this->name}.{$this->level}?}");
        $renderer->set('--facilityCode', $this->name);

        $renderer->set('--facilityImage', 
            (
                is_null($this->image) || $this->image == '' 
                || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['facilityImage'].$this->image)
            ) 
                ? "{?!dirImg?}cardBg.default.facility.jpg" 
                : "{?!dirFacilityImage?}{$this->image}"
        );

        if(is_null($this->level) || $this->level == 0) {
            $renderer->set('--displayLevel', 'hidden');
            $renderer->set('--facilityLevel', '');
        } else {
            $renderer->set('--displayLevel', '');
            $renderer->set('--facilityLevel', $this->level);
        }

        $renderer->set('$desc', "{?facilityDesc.{$this->name}.{$this->level}?}");

        //渲染设施效果
        if(empty($this->data['efx'])) {
            $renderer->set('$facilityEfx', '');
        } else {
            $comp = array();
            foreach($this->data['efx'] as $k => $efx) {
                switch($GLOBALS['meshal']['userEfx'][$efx[0]]) {
                    case 1:
                        $type = 'modPositive';
                        break;
                    
                    case 0:
                        $type = 'modNegative';
                        break;
                    
                    default:
                        $type = 'modSpecial';
                        break;
                }

                $comp[] = array(
                    '--type' => $type,
                    '--listItem' => \fReplace($renderer->dbLang("userEfx.{$efx[0]}"), $efx)
                );
            }

            $list = $renderer->duplicate(
                'facility/card.row.li.html',
                $comp
            );

            $renderer->set(
                '$facilityEfx',
                $renderer->quickRender(
                    'facility/card.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );
        }
        //渲染当前建筑要求
        $renderer->set('$currentLevelDisplay', '');
        $renderer->set('$facilityBuildTime', \fFormatTime($this->data['build']['time'] / $GLOBALS['setting']['facility']['buildSpeedFactor'],'hour'));
        $renderer->set('$facilityBuildChar', $this->data['build']['char']);
        if(is_null($user->uid) || !isset($user->uid)) {
            $renderer->set('$css.buildChar', '');
        } {
            $renderer->set(
                '$css.buildChar',
                \user\xChecker::restingSurvivorsMoreThan($user->uid, $this->data['build']['char'])
                    ? ''
                    : 'modNegative'
            );
        }
        $renderer->set('$facilityBuildAP', $this->data['build']['ap']);
        //渲染checkAll类前提
        if(!empty($this->data['build']['checkAll'])) {
            $renderer->set('$showCurrentLevelCheckAll', '');
            $comp = array();
            foreach($this->data['build']['checkAll'] as $k => $cond) {
                $comp[] = array(
                    '--type' => '',
                    '--listItem' => \fReplace($renderer->dbLang("userCheck.{$cond[0]}"), $cond)
                );
            }

            $list = $renderer->duplicate(
                'facility/card.row.li.html',
                $comp
            );

            $renderer->set(
                '$facilityCheckAll',
                $renderer->quickRender(
                    'facility/card.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );
        } else {
            $renderer->set('$showCurrentLevelCheckAll', 'hidden');
        }

        //渲染checkAny类前提
        if(!empty($this->data['build']['checkAny'])) {
            $renderer->set('$showCurrentLevelCheckAny', '');
            $comp = array();
            foreach($this->data['build']['checkAny'] as $k => $cond) {
                $comp[] = array(
                    '--type' => '',
                    '--listItem' => \fReplace($renderer->dbLang("userCheck.{$cond[0]}"), $cond)
                );
            }

            $list = $renderer->duplicate(
                'facility/card.row.li.html',
                $comp
            );

            $renderer->set(
                '$facilityCheckAny',
                $renderer->quickRender(
                    'facility/card.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );
        } else {
            $renderer->set('$showCurrentLevelCheckAny', 'hidden');
        }

        //渲染material消耗前提
        if(!empty($this->data['build']['material'])) {
            $renderer->set('$showCurrentLevelMaterial', '');
            $comp = array();
            foreach($this->data['build']['material'] as $k => $cond) {
                if(is_null($user->uid) || !isset($user->uid)) {
                    $renderType = '';
                } else {
                    $renderType = $user->inventory->checkStock($cond[0], $cond[1]) == false ? 'modNegative' : '';
                }
                $comp[] = array(
                    '--type' => $renderType,
                    '--listItem' => \fReplace($renderer->dbLang("common.facility.buildMaterial"), $cond)
                );
            }

            $list = $renderer->duplicate(
                'facility/card.row.li.html',
                $comp
            );

            $renderer->set(
                '$facilityBuildMaterial',
                $renderer->quickRender(
                    'facility/card.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );
        } else {
            $renderer->set('$showCurrentLevelMaterial', 'hidden');
        }
        
        //渲染下一级的信息
        if(
            empty($this->dataNextLevel)
            || $showNextLevel === false
        ) {
            $renderer->set('$nextLevelDisplay', 'hidden');
            $renderer->set('$facilityNextLevelEfx', '');
            $renderer->set('$facilityUpgradeCheckAll', '');
            $renderer->set('$facilityUpgradeCheckAny', '');
            $renderer->set('$facilityUpgradeMaterial', '');
            $renderer->set('$facilityUpgradeTime', '');
            $renderer->set('$facilityUpgradeChar', '');
            $renderer->set('$showNextLevelCheckAll', 'hidden');
            $renderer->set('$showNextLevelCheckAny', 'hidden');
        } else {
            $renderer->set('$nextLevelDisplay', '');
            $renderer->set('$facilityUpgradeTime', \fFormatTime($this->dataNextLevel['build']['time'] / $GLOBALS['setting']['facility']['buildSpeedFactor'], 'hour'));
            $renderer->set('$facilityUpgradeChar', $this->dataNextLevel['build']['char']);
            if(is_null($user->uid) || !isset($user->uid)) {
                $renderer->set('$css.upgradeChar', '');
            } else {
                $renderer->set(
                    '$css.upgradeChar',
                    \user\xChecker::restingSurvivorsMoreThan($user->uid, $this->dataNextLevel['build']['char'])
                        ? ''
                        : 'modNegative'
                );
            }
            $renderer->set('$facilityUpgradeAP', $this->dataNextLevel['build']['ap']);
            //渲染checkAll类前提
            if(!empty($this->dataNextLevel['build']['checkAll'])) {
                $renderer->set('$showNextLevelCheckAll', '');
                $comp = array();
                foreach($this->dataNextLevel['build']['checkAll'] as $k => $cond) {
                    $comp[] = array(
                        '--type' => '',
                        '--listItem' => \fReplace($renderer->dbLang("userCheck.{$cond[0]}"), $cond)
                    );
                }

                $list = $renderer->duplicate(
                    'facility/card.row.li.html',
                    $comp
                );

                $renderer->set(
                    '$facilityUpgradeCheckAll',
                    $renderer->quickRender(
                        'facility/card.row.ul.html',
                        array(
                            '--list' => $list
                        )
                    )
                );
            } else {
                $renderer->set('$showNextLevelCheckAll', 'hidden');
            }

            //渲染checkAny类前提
            if(!empty($this->dataNextLevel['build']['checkAny'])) {
                $renderer->set('$showNextLevelCheckAny', '');
                $comp = array();
                foreach($this->dataNextLevel['build']['checkAny'] as $k => $cond) {
                    $comp[] = array(
                        '--type' => '',
                        '--listItem' => \fReplace($renderer->dbLang("userCheck.{$cond[0]}"), $cond)
                    );
                }

                $list = $renderer->duplicate(
                    'facility/card.row.li.html',
                    $comp
                );

                $renderer->set(
                    '$facilityUpgradeCheckAny',
                    $renderer->quickRender(
                        'facility/card.row.ul.html',
                        array(
                            '--list' => $list
                        )
                    )
                );
            } else {
                $renderer->set('$showNextLevelCheckAny', 'hidden');
            }

            //渲染material消耗前提
            if(!empty($this->dataNextLevel['build']['material'])) {
                $renderer->set('$showNextLevelMaterial', '');
                $comp = array();
                foreach($this->dataNextLevel['build']['material'] as $k => $cond) {
                    if(is_null($user->uid) || !isset($user->uid)) {
                        $renderType = '';
                    } else {
                        $renderType = $user->inventory->checkStock($cond[0], $cond[1]) == false ? 'modNegative' : '';
                    }
                    $comp[] = array(
                        '--type' => $renderType,
                        '--listItem' => \fReplace($renderer->dbLang("common.facility.buildMaterial"), $cond)
                    );
                }

                $list = $renderer->duplicate(
                    'facility/card.row.li.html',
                    $comp
                );

                $renderer->set(
                    '$facilityUpgradeMaterial',
                    $renderer->quickRender(
                        'facility/card.row.ul.html',
                        array(
                            '--list' => $list
                        )
                    )
                );
            } else {
                $renderer->set('$showNextLevelMaterial', 'hidden');
            }

            //渲染设施效果
            if(empty($this->dataNextLevel['efx'])) {
                $renderer->set('$facilityUpgradeEfx', '');
            } else {
                $comp = array();
                foreach($this->dataNextLevel['efx'] as $k => $efx) {
                    switch($GLOBALS['meshal']['userEfx'][$efx[0]]) {
                        case 1:
                            $type = 'modPositive';
                            break;
                        
                        case 0:
                            $type = 'modNegative';
                            break;
                        
                        default:
                            $type = 'modSpecial';
                            break;
                    }

                    $comp[] = array(
                        '--type' => $type,
                        '--listItem' => \fReplace($renderer->dbLang("userEfx.{$efx[0]}"), $efx)
                    );
                }

                $list = $renderer->duplicate(
                    'facility/card.row.li.html',
                    $comp
                );

                $renderer->set(
                    '$facilityUpgradeEfx',
                    $renderer->quickRender(
                        'facility/card.row.ul.html',
                        array(
                            '--list' => $list
                        )
                    )
                );
            }
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
                        'facility/card.controller.html',
                        $ctrl
                    )
                );
            } else {
                $renderer->set('$controller', '');
            }
        }

        //倒计时
        if(
            $user->uid
            && !is_null($this->buildCountdown)
        ) {
            $renderer->set('--showCountdown', '');
            $renderer->set('--countdown', \fFormatTime($this->buildCountdown));
        } else {
            $renderer->set('--showCountdown', 'hidden');
            $renderer->set('--countdown', '');
        }

        //插入自定义css class
        $renderer->set('--css', $css);

        $renderer->loadTpl($template);

        return $renderer->render(
            'body'
        );
    }

    /**
     * 以tag形式渲染设施
     * 
     * @param string $facilityName
     * 设施名
     * 
     * @param int $facilityLevel = null
     * 设施等级，为null时不显示
     */
    public static function renderTag(
        string $facilityName,
        int $facilityLevel = 1
    ) {
        $renderer = new \xHtml;

        $data = self::getData($facilityName, $facilityLevel);

        $renderer->set('--facilityCode', $facilityName);
        $renderer->set('--facilityName', "{?facilityName.{$facilityName}?}");
        $renderer->set('--rarityStyle', ''); ###还未做

        if(is_null($facilityLevel)) {
            $renderer->set('--displayLevel', 'hidden');
            $renderer->set('--facilityLevel', '');
        } else {
            $renderer->set('--displayLevel', '');
            $renderer->set('--facilityLevel', $facilityLevel);
        }
        
        $renderer->loadTpl('facility/tag.html');

        return $renderer->render(
            'body'
        );
    }

    /**
     * 检查是否可进行升级
     * 
     * @param int $uid
     * 检查建造的用户uid
     * 
     * @return int
     * 返回状态码
     *  0：可以升级
     *  1：没有下一级的配置
     *  2：前提检查不通过
     *  3：材料不足
     *  4：正在建造中
     */
    public function checkUpgrade(
        int $uid
    ) {
        global $db;
        //没有下一级的配置
        if(empty($this->dataNextLevel)) return 1;

        $user = new \user\xAdapter;
        $user->load($uid);

        //检查建造队列中是否有正在建造的记录
        $building = $db->getCount(
            'facility_building',
            array(
                "`uid` = '{$uid}'",
                "`facilityName` = '{$this->name}'"
            ),
            null,
            1
        );
        if($building > 0) return 4;

        //进行建造前提检查
        $checkAll = 1;
        if(!empty($this->dataNextLevel['build']['checkAll'])) {
            foreach ($this->dataNextLevel['build']['checkAll'] as $k => $check) {
                $param = $check;
                $param[0] = $uid;
                if(method_exists('\user\xChecker', $check[0])) { //检查方法是否存在
                    $checkAll *= \user\xChecker::{$check[0]}(...$param) == true ? 1 : 0; //累乘检查结果
                }
            }
        }

        $checkAny = 1;
        if(!empty($this->dataNextLevel['build']['checkAny'])) {
            $checkAny = 0;
            foreach ($this->dataNextLevel['build']['checkAny'] as $k => $check) {
                $param = $check;
                $param[0] = $uid;
                if(method_exists('\user\xChecker', $check[0])) { //检查方法是否存在
                    $checkAny += \user\xChecker::{$check[0]}(...$param) == true ? 1 : 0; //累加检查结果
                }
            }
        }

        if($checkAny * $checkAll == 0) return 2;

        //进行材料检查
        $checkMaterial = 1;
        if(!empty($this->dataNextLevel['build']['material'])) {
            foreach ($this->dataNextLevel['build']['material'] as $k => $check) {
                $checkMaterial *= $user->inventory->checkStock($check[0], $check[1]) == true ? 1 : 0; //累加检查结果
            }
        }

        if($checkMaterial == 0) return 3;

        return 0;
    }
}
?>