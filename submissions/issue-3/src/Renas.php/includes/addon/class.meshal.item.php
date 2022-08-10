<?php
namespace meshal;
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal能力的类
################################################


class xItem
{
    function __construct()
    {
        $this->amount = 0;

        $this->name = '';
        $this->creator = new \user\xAdapter; //创作者信息
        $this->lastUpdate = 0;

        $this->icon = '';
        $this->iconFile  = '';
        $this->image = '';

        $this->type = array();
        $this->occupancy = array(
            'type' => null,
            'slots' => null
        );
        $this->data = array();
        $this->loads = 0.0;
        $this->strength = array(
            'equip' => 0,
            'carry' => 0
        );
        $this->probabilityModifier = 0; 
        $this->controller = array(); //用户操作
    }

    /**
     * 随机从给定的物品数组中选择指定个数的物品，随机过程受物品的实力修正、物品的随机几率修正和给定数组中的随机权重修正影响
     * 
     * @param array $itemList
     * 以数组方式传递物品清单，格式为array('item1' => probability1, ...)，
     * 每个元素的键名是物品名，键值是随机概率的权重修正
     * 
     * @param int $pick = 1
     * 从这次随机中随机几个物品作为结果
     * 
     * @param bool $nonReset = false
     * 是否使用非重置随机算法
     * 所谓非重置随机，就是当一个结果被随机到之后，就会从随机库中移除
     * 
     * @return array
     * 返回一个数组格式的物品清单 array(
     *  'item1',
     *  'item2',
     *  ...
     * )，根据传递的参数，可能会随机到多个同名的物品
     */
    public static function loot(
        array $itemList,
        int $pick = 1,
        bool $nonReset = false
    ) {
        //根据给定的物品清单，组装权重数组
        $pre = array(
            'totalStrength' => 0,
            'totalProbabilityModifier' => 0
        );

        foreach ($itemList as $itemName => $weight) {
            $itemData = self::getData($itemName); //获取物品数据
            if($itemData !== false) {
                $pre['candidates'][$itemName] = array(
                    'strength' => abs($itemData['strength']['equip'] + $itemData['strength']['carry']), //与实力相关的随机概率为以0为原点的正态分布，数字越接近0，概率越大
                    'probabilityModifier' => $itemData['probabilityModifier'], //累加物品本身的权重修正
                    'tempWeight' => $weight //累加此次随机的权重修正
                );
                $pre['totalStrength'] += abs($itemData['strength']['equip'] + $itemData['strength']['carry']); //把实力累加
                $pre['totalProbabilityModifier'] += $itemData['probabilityModifier'] + (is_null($weight) ? 0 : $weight); //把权重修改量累加
            }
        }

        if(empty($pre['candidates'])) return array(); //如果候选物品为空，直接返回空数组

        $pool = array();
        foreach ($pre['candidates'] as $itemName => $itemData) {
            $pool[$itemName] = 
                $pre['totalStrength'] - $itemData['strength'] //与实力相关的随机概率为以0为原点的正态分布，数字越接近0，概率越大
                + $itemData['probabilityModifier'] //应用权重修改量
                + $itemData['tempWeight'] //此次随机的权重修正
                + 1 //最后加1为了确保所有修正器为0的特征也有1的权重
            ;
        }
        \fLog(\fDump($pool));

        return \fArrayRandWt($pool, $pick, $nonReset);
    }


    /**
     * 从数据库中获取物品模板的资料
     * 
     * @param string $itemName
     * 查询的物品名称
     * 
     * @return mixed
     * 如果没有查到，返回false；
     * 如果查到了物品，以数组返回包含该物品的数据
     */
    public static function getData (
        string $itemName
    ) {
        global $db;
        $arr = $db->getArr(
            'items',
            array(
                "`name` = '{$itemName}'"
            ),
            NULL,
            1
        );

        if($arr === false) {
            \fLog("Item {$itemName} doesn't exist in library");
            return false;
        }

        $arrType = $db->getArr( //从item_type表获取物品类型
            'item_types',
            array(
                "`name` = '{$itemName}'"
            ),
            null
        );
        $types = array();
        if($arrType !== false) {
            foreach ($arrType as $k => $cat) {
                $types[$cat['category']][] = $cat['type'];
            }
        }
        
        $data = json_decode($arr[0]['data'], true);

        #将效果结构化
        $effects = array();
        if(!empty($data['effects'])) {
            foreach($data['effects'] as $efxType => $efxCfg) {
                foreach($efxCfg as $k => $efx) {
                    $effects[$efxType][] = \fFormatCommand($efx, ',');
                }
            }
            $data['effects'] = $effects;
        } else {
            $data['effects'] = array();
        }
    
        /*
        ################################################
        # 稀有度计算
        ################################################
        $sum = $db->getArr(
            'feature_index',
            array(
                "`name` = '{$featureType}'"
            ),
            null,
            1
        );
        if($sum === false) { //如果没有权重总和记录，创建一个
            $db->insert(
                'feature_index',
                array(
                    'name' => "{$featureType}"
                )
            );
            $sum = $db->getArr( //重新获取该特征类型的权重总和数据
                'feature_index',
                array(
                    "`name` = '{$featureType}'"
                ),
                null,
                1
            );
        }
        
        $probability = array();

        //计算这个特征的随机权重
        $probability['weight'] = $sum[0]['strength'] - abs($arr[0]['strength']) + $arr[0]['probabilityModifier'];

        //根据特征的平均实力值，反向求取特征的平均实力权重
        $probability['benchmark']['strengthWt'] = \fSub(
            $sum[0]['strength'],
            \fDiv( //计算所有同类特征的平均实力值
                $sum[0]['strength'],
                $sum[0]['count']
            )
        );

        //计算平均权重修正
        $probability['benchmark']['modifierWt'] = \fDiv(
            $sum[0]['probabilityModifier'],
            $sum[0]['count']
        );

        //计算这个特征的随机概率
        $probability['result'] = \fDiv(
            $probability['weight'],
            \fMul( //将特征的平均实力权重 × 特征数，得到总权重
                $probability['benchmark']['strengthWt'],
                $sum[0]['count']
            ) + $sum[0]['probabilityModifier'] //加上总权重修正
        , 8);

        //根据比例进行渲染
        $descArr = $GLOBALS['meshal']['rarity']['feature'][$featureType]
            ? $GLOBALS['meshal']['rarity']['feature'][$featureType]
            : $GLOBALS['meshal']['rarity']['feature']['default']
        ;
        foreach ($descArr as $k => $v) {
            if(
                is_null($v['max']) //如果配置中的max为null，就意味着这是最大概率的描述
                || ( 
                    //判断是否在区间内
                    $probability['result'] <= $v['max']
                    && $probability['result'] > $v['min']
                )
            ) {
                $probability['rarity'] = "{?{$v['desc']}?}";
                $probability['style'] = $v['style'];
                break;
            }
        }

        ################################################
        # 稀有度计算结束
        ################################################
        */

        $return = array(
            'fullname' => "meshal.item.{$itemName}",
            'name' => $itemName,
            'lastUpdate' => $arr[0]['lastUpdate'],
            'icon' => is_null($arr[0]['icon']) || $arr[0]['icon'] == '' ? 'general' : $arr[0]['icon'],
            'iconFile' => 'ico.item.'.(is_null($arr[0]['icon']) || $arr[0]['icon'] == '' ? 'general' : $arr[0]['icon']).'.png',
            'type' => $types,
            'image' => \fDecode($arr[0]['image']),
            'occupancy' => array(
                'type' => $data['occupancy']['type'],
                'slots' => $data['occupancy']['slots']
            ),
            'data' => $data,
            'loads' => $arr[0]['loads'],
            'strength' => array(
                'equip' => $arr[0]['strengthEquip'],
                'carry' => $arr[0]['strengthCarry']
            ),
            'probabilityModifier' => 0,
            'totalShares' => $arr[0]['totalShares']
            // 'probability' => array(
            //     'modifier' => $arr[0]['probabilityModifier'],
            //     'result' => $probability['result'],
            //     'rarity' => $probability['rarity'],
            //     'rarityStyle' => $probability['style']
            // )
        );

        return $return;
    }

    /**
     * 加载物品
     * 
     * @param string $itemName
     * 要加载的物品代码
     * 
     * @return bool
     * 加载状态，成功为true，失败为false
     */
    public function load(
        string $itemName
    ) {
        $this->__construct();

        $data = self::getData($itemName);

        if($data === false) return false;

        $this->name = $data['name'];
        $this->lastUpdate = $data['lastUpdate'];
        $this->icon = $data['icon'];
        $this->iconFile  = $data['iconFile'];
        $this->type = $data['type'];
        $this->image = $data['image'];
        $this->occupancy = $data['occupancy'];
        $this->data = $data['data'];
        $this->loads = $data['loads'];
        $this->strength = $data['strength'];
        $this->probabilityModifier = $data['probabilityModifier'];

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
            'alwaysShow' => $alwaysShow == false ? '' : 'characterController-alwaysShow',
            'auth' => array_flip($auth)
        );
    }

    public function render(
        \xUser &$user = null,
        string $template = 'item/card.frame.html',
        string $css = ''
    ) {
        $renderer = new \xHtml;

        //物品图片目录
        $renderer->set('!dirItemImage', _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage']); //用户上传的物品图片目录

        $renderer->set('--itemName', "{?itemName.{$this->name}?}");
        $renderer->set('--itemCode', $this->name);
        $renderer->set('--itemIcon', $this->icon);

        $renderer->set('--itemImage', 
            (
                is_null($this->image) || $this->image == '' 
                || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$this->image)
            ) 
                ? "{?!dirImg?}cardBg.default.item.jpg" 
                : "{?!dirItemImage?}{$this->image}"
        );

        if(is_null($this->amount)) {
            $renderer->set('--displayAmount', 'hidden');
            $renderer->set('--itemAmount', '');
        } else {
            $renderer->set('--displayAmount', '');
            $renderer->set('--itemAmount', $this->amount);
        }
        
        /**
         * 显示样式配置
         */
        $settings = array(
            /**
             * 数据格式，写在数组里的代表会做显示
             * - positive: 有增量时显示
             * - negative: 有减量时显示
             */

            'm' => array(
                'term' => 'attr.might',
                'positive' => true,
                'negative' => true
            ),
            'a' => array(
                'term' => 'attr.agility',
                'positive' => true,
                'negative' => true
            ),
            's' => array(
                'term' => 'attr.spirit',
                'positive' => true,
                'negative' => true
            ),
            't' => array(
                'term' => 'protection.toughness',
                'positive' => true,
                'negative' => true
            ),
            'e' => array(
                'term' => 'protection.endurance',
                'positive' => true,
                'negative' => true
            ),
            'r' => array(
                'term' => 'protection.resistance',
                'positive' => true,
                'negative' => true
            ),
            'ip' => array(
                'term' => 'immunity.physical',
                'positive' => '{?common.immune.physical?}'
            ),
            'ie' => array(
                'term' => 'immunity.erosive',
                'positive' => '{?common.immune.erosive?}'
            ),
            'io' => array(
                'term' => 'immunity.occult',
                'positive' => '{?common.immune.occult?}'
            ),
            'ap' => array(
                'term' => 'actionPoint',
                'positive' => true,
                'negative' => true
            ),
            'cc' => array(
                'term' => 'carryingCapability',
                'positive' => true,
                'negative' => true
            ),
            'pr' => array(
                'term' => 'perceptionRange',
                'positive' => true,
                'negative' => true
            ),
            'ms' => array(
                'term' => 'movingSpeed',
                'positive' => true,
                'negative' => true
            ),
            'strength' => array(
                'term' => 'strength',
                'positive' => true,
                'negative' => true
            )
        );
        
        $prepared = array();

        //修正的增减处理（装备）
        foreach ($this->data['equip']['modifier'] as $scoreName => $v) {
            $raw = array();
            switch (true) {
                case ( //增加显示
                    $v > 0
                    && isset($settings[$scoreName]['positive'])
                ):
                    if($settings[$scoreName]['positive'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.add?}{$v}";
                    } else {
                        $raw['--content'] = $settings[$scoreName]['positive'];
                    }

                    $raw['--type'] = 'modPositive';

                    $prepared['equip'][] = $raw;
                    break;

                case ( //减少显示
                    $v < 0
                    && isset($settings[$scoreName]['negative'])
                ):
                    $raw['--modifier'] = '';

                    if($settings[$scoreName]['negative'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {$v}";
                    } else {
                        $raw['--content'] = $settings[$scoreName]['negative'];
                    }

                    $raw['--type'] = 'modNegative';

                    $prepared['equip'][] = $raw;
                    break;

                default:
                    //不显示
                    break;
            }
        }

        //修正的倍数处理（装备）
        foreach ($this->data['equip']['multiplier'] as $scoreName => $v) {
            $raw = array();
            switch (true) {
                case ( //倍数>1显示
                    $v > 1
                    && isset($settings[$scoreName]['positive'])
                ):
                    if($settings[$scoreName]['positive'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.mul?}{$v}";
                    } else {
                        $raw['--content'] = $settings[$scoreName]['positive'];
                    }

                    $raw['--type'] = 'modPositive';

                    $prepared['equip'][] = $raw;
                    break;

                case ( //倍数<1显示
                    $v < 1
                    && isset($settings[$scoreName]['negative'])
                ):
                    if($settings[$scoreName]['negative'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.div?}".\fDiv(1, $v, 2);
                    } else {
                        $raw['--content'] = $settings[$scoreName]['negative'];
                    }

                    $raw['--type'] = 'modNegative';

                    $prepared['equip'][] = $raw;
                    break;

                default:
                    //不显示
                    break;
            }
        }

        //实力修正（装备）
        $raw = array();
        switch (true) {
            case (
                $this->strength['equip'] > 0
                && isset($settings['strength']['positive'])
            ):
                if($settings['strength']['positive'] === true) {
                    $raw['--content'] = "{?term.score.strength?} {?common.scoreMod.add?}{$this->strength['equip']}";
                } else {
                    $raw['--content'] = $settings['strength']['positive'];
                }
                
                $raw['--type'] = 'modSpecial';

                $prepared['equip'][] = $raw;
                break;
            
            case (
                $this->strength['equip'] < 0
                && isset($settings['strength']['negative'])
            ):
                if($settings['strength']['negative'] === true) {
                    $raw['--content'] = "{?term.score.strength?} {$this->strength['equip']}";
                } else {
                    $raw['--content'] = $settings['strength']['negative'];
                }
                
                $raw['--type'] = 'modSpecial';

                $prepared['equip'][] = $raw;
                break;
                
            default:
                //不显示
                break;
        }

        //修正的增减处理（携带）
        foreach ($this->data['carry']['modifier'] as $scoreName => $v) {
            $raw = array();
            switch (true) {
                case ( //增加显示
                    $v > 0
                    && isset($settings[$scoreName]['positive'])
                ):
                    if($settings[$scoreName]['positive'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.add?}{$v}";
                    } else {
                        $raw['--content'] = $settings[$scoreName]['positive'];
                    }
                    
                    $raw['--type'] = 'modPositive';

                    $prepared['carry'][] = $raw;
                    break;

                case ( //减少显示
                    $v < 0
                    && isset($settings[$scoreName]['negative'])
                ):
                    $raw['--modifier'] = '';

                    if($settings[$scoreName]['negative'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {$v}";
                    } else {
                        $raw['--content'] = $settings[$scoreName]['negative'];
                    }

                    $raw['--type'] = 'modNegative';

                    $prepared['carry'][] = $raw;
                    break;

                default:
                    //不显示
                    break;
            }
        }

        //修正的倍数处理（携带）
        foreach ($this->data['carry']['multiplier'] as $scoreName => $v) {
            $raw = array();
            switch (true) {
                case ( //倍数>1显示
                    $v > 1
                    && isset($settings[$scoreName]['positive'])
                ):
                    if($settings[$scoreName]['positive'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.mul?}{$v}";
                    } else {
                        $raw['--content'] = $settings[$scoreName]['positive'];
                    }

                    $raw['--type'] = 'modPositive';

                    $prepared['carry'][] = $raw;
                    break;

                case ( //倍数<1显示
                    $v < 1
                    && isset($settings[$scoreName]['negative'])
                ):
                    if($settings[$scoreName]['negative'] === true) {
                        $raw['--content'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.div?}".\fDiv(1, $v, 2);
                    } else {
                        $raw['--content'] = $settings[$scoreName]['negative'];
                    }

                    $raw['--type'] = 'modNegative';

                    $prepared['carry'][] = $raw;
                    break;

                default:
                    //不显示
                    break;
            }
        }

        //实力修正（携带）
        $raw = array();
        switch (true) {
            case (
                $this->strength['carry'] > 0
                && isset($settings['strength']['positive'])
            ):
                if($settings['strength']['positive'] === true) {
                    $raw['--content'] = "{?term.score.strength?} {?common.scoreMod.add?}{$this->strength['carry']}";
                } else {
                    $raw['--content'] = $settings['strength']['positive'];
                }
                
                $raw['--type'] = 'modSpecial';

                $prepared['carry'][] = $raw;
                break;
            
            case (
                $this->strength['carry'] < 0
                && isset($settings['strength']['negative'])
            ):
                if($settings['strength']['negative'] === true) {
                    $raw['--content'] = "{?term.score.strength?} {$this->strength['carry']}";
                } else {
                    $raw['--content'] = $settings['strength']['negative'];
                }

                $raw['--type'] = 'modSpecial';

                $prepared['carry'][] = $raw;
                break;
                
            default:
                //不显示
                break;
        }

        //物品的多类型列表组装
        $itemTypes = array();
        foreach ($this->type as $categoryName => $types) {
            foreach ($types as $k => $typeName) {
                $itemTypes[] = "{?itemType.{$categoryName}.{$typeName}?}";
            }
        }
        
        //物品的装备说明组装
        $renderer->set('$itemEquipType', "{?{$GLOBALS['meshal']['equipmentContainer'][$this->occupancy['type']]['name']}?}");
        $renderer->set('$itemEquipSlots', $this->occupancy['slots']);

        $renderer->set('$itemTypes', implode('{?common.itemType.separator?}', $itemTypes));
        $renderer->set('$itemLoads', $this->loads);
        $renderer->set('$itemName', $renderer->dbLang("itemName.{$this->name}"));
        $renderer->set('$desc', $renderer->dbLang("itemDesc.{$this->name}"));

        if(empty($prepared['equip'])) {
            $renderer->set('$modEquipDisplay', 'hidden');
            $renderer->set('$equipModifiers', '');
        } else {
            $renderer->set('$modEquipDisplay', '');
            $renderer->set(
                '$equipModifiers',
                $renderer->duplicate(
                    'item/card.row.html',
                    $prepared['equip']
                )
            );
        }

        if(empty($prepared['carry'])) {
            $renderer->set('$modCarryDisplay', 'hidden');
            $renderer->set('$carryModifiers', '');
        } else {
            $renderer->set('$modCarryDisplay', '');
            $renderer->set(
                '$carryModifiers',
                $renderer->duplicate(
                    'item/card.row.html',
                    $prepared['carry']
                )
            );
        }

        //使用说明
        if(empty($this->data['use']['efx'])) {
            $renderer->set('$modUsageDisplay', 'hidden');
            $renderer->set('$useCheckAll', '');
            $renderer->set('$useCheckAny', '');
            $renderer->set('$useEffects', '');
            $renderer->set('$showCheckAll', 'hidden');
            $renderer->set('$showCheckAny', 'hidden');
        } else {
            //渲染checkAll类前提
            if(!empty($this->data['use']['checkAll'])) {
                $renderer->set('$showCheckAll', '');
                $comp = array();
                foreach($this->data['use']['checkAll'] as $k => $cond) {
                    $comp[] = array(
                        '--listItem' => \fReplace($renderer->dbLang("itemCheck.{$cond[0]}"), $cond)
                    );
                }

                $list = $renderer->duplicate(
                    'item/card.row.li.html',
                    $comp
                );

                $renderer->set(
                    '$useCheckAll',
                    $renderer->quickRender(
                        'item/card.row.ul.html',
                        array(
                            '--list' => $list
                        )
                    )
                );
            } else {
                $renderer->set('$showCheckAll', 'hidden');
            }

            //渲染checkAny类前提
            if(!empty($this->data['use']['checkAny'])) {
                $renderer->set('$showCheckAny', '');
                $comp = array();
                foreach($this->data['use']['checkAny'] as $k => $cond) {
                    $comp[] = array(
                        '--listItem' => \fReplace($renderer->dbLang("itemCheck.{$cond[0]}"), $cond)
                    );
                }

                $list = $renderer->duplicate(
                    'item/card.row.li.html',
                    $comp
                );

                $renderer->set(
                    '$useCheckAny',
                    $renderer->quickRender(
                        'item/card.row.ul.html',
                        array(
                            '--list' => $list
                        )
                    )
                );
            } else {
                $renderer->set('$showCheckAny', 'hidden');
            }

            //渲染使用效果
            $comp = array();
            foreach($this->data['use']['efx'] as $k => $efx) {
                switch ($GLOBALS['meshal']['itemUsage'][$efx[0]]) {
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
                    '--listItem' => \fReplace($renderer->dbLang("itemUsage.{$efx[0]}"), $efx)
                );
            }

            $list = $renderer->duplicate(
                'item/card.row.li.html',
                $comp
            );

            $renderer->set(
                '$useEffects',
                $renderer->quickRender(
                    'item/card.row.ul.html',
                    array(
                        '--list' => $list
                    )
                )
            );
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
                        'item/card.controller.html',
                        $ctrl
                    )
                );
            } else {
                $renderer->set('$controller', '');
            }
        }

        //插入自定义css class
        $renderer->set('--css', $css);

        $renderer->loadTpl($template);

        return $renderer->render(
            'body'
        );
    }

    /**
     * 以tag形式渲染物品
     * 
     * @param string $itemName
     * 物品名
     * 
     * @param int $amount = null
     * 物品数量，为null时不显示
     */
    public static function renderTag(
        string $itemName,
        int $amount = null
    ) {
        $renderer = new \xHtml;

        $data = self::getData($itemName);

        $renderer->set('--itemCode', $itemName);
        $renderer->set('--itemName', "{?itemName.{$itemName}?}");
        $renderer->set('--rarityStyle', ''); ###还未做
        $renderer->set('--itemIcon', $data['icon']);

        if(is_null($amount)) {
            $renderer->set('--displayAmount', 'hidden');
            $renderer->set('--itemAmount', '');
        } else {
            $renderer->set('--displayAmount', '');
            $renderer->set('--itemAmount', $amount);
        }
        
        $renderer->loadTpl('item/tag.html');

        return $renderer->render(
            'body'
        );
    }

    public static function renderEquipSlotTag(
        string $slotName,
        int $amount = null
    ) {
        $renderer = new \xHtml;

        $renderer->set('--slotCode', $slotName);
        $renderer->set('--slotName', "{?term.equipSlot.{$slotName}?}");
        $renderer->set('--rarityStyle', ''); ###还未做

        if(is_null($amount)) {
            $renderer->set('--displayAmount', 'hidden');
            $renderer->set('--slotAmount', '');
        } else {
            $renderer->set('--displayAmount', '');
            $renderer->set('--slotAmount', $amount);
        }
        
        $renderer->loadTpl('item/tag.equipSlot.html');

        return $renderer->render(
            'body'
        );
    }
}
?>