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

$html = new \xHtml;
$char = new \meshal\xChar;
$user = new \xUser(false);
/**
 * $_POST['dataInfo']的格式为：'className.typeName.nameOrId'
 * - className: 比如rule, character, feature, ability, item
 * - typeName: 根据className的不同子类（character没有）
 * - nameOrId: 某个内容的名字或id
 */


if(isset($_GET['datainfo'])) {
    $param = explode('.', $_GET['datainfo']);
    switch ($param[0]) {
        case 'rule':
            $exploded = explode('.', $_GET['datainfo'], 2);
            $info = \meshal\xRule::getData($_GET['datainfo']);
            // fPrint($data);
            $html->loadTpl('tooltip/rule/frame.html');
            $html->set('$term', "{?term.{$exploded[1]}?}");
            $html->set('$info', $info['content']);
            break;
        
        case 'rollInfo': ###
            $exploded = explode('.', $_GET['datainfo'], 3);
            $charId = $exploded[1];
            $data = json_decode(\fDecode($exploded[2]), true);
            $html->loadTpl('tooltip/rollInfo/frame.html');

            $symbolPt = $html->readTpl('tooltip/rollInfo/dice.pt.html');
            $symbolNum = $html->readTpl('tooltip/rollInfo/dice.num.html');
            $html->set('$dice.pt', $symbolPt);
            $html->set('$dice.num', $symbolNum);


            //渲染掷骰说明
            $html->set('$char', \meshal\xChar::getName($charId));
            $comp = array();
            if( //组装点数
                isset($data['ptRoll']['raw'])
                && count($data['ptRoll']['raw']) > 0
            ) {
                $comp[] = count($data['ptRoll']['raw']).$symbolPt;
            }

            if( //组装骰面
                isset($data['numRoll']['raw'])
                && count($data['numRoll']['raw']) > 0
            ) {
                $comp[] = count($data['numRoll']['raw']).$symbolNum;
            }

            if(
                isset($data['baseAmount'])
            ) {
                $base = abs($data['baseAmount']);
                switch (bccomp($data['baseAmount'], 0)) {
                    case -1:
                        $baseAmount = " {?common.sub?} {$base}";
                        break;

                    case 1:
                        $baseAmount = " {?common.add?} {$base}";
                        break;
                    
                    default:
                    $baseAmount = '';
                        break;
                }
            }

            $html->set('$rolls', implode('{?common.add?}', $comp).$baseAmount);
            $html->set('$result', $data['result']);

            // $html->set('$result', $data['result'] == false ? 0 : $data['result']);

            if( //对点数做渲染
                isset($data['ptRoll']['raw'])
                && count($data['ptRoll']['raw']) > 0
            ) {
                $html->set('$show.pt', '');
                $html->set('$roll.pt', count($data['ptRoll']['raw']));
                $html->set('$total.pt', $data['ptRoll']['result']);
                $detail = array();
                foreach($data['ptRoll']['raw'] as $k => $d) {
                    $detail[] = "{?common.dicePt.{$d}?}";
                }
                $html->set('$detail.pt', implode('{?common.comma?}', $detail));
            } else {
                $html->set('$show.pt', 'hidden');
                $html->set('$roll.pt', '');
                $html->set('$total.pt', '');
                $html->set('$detail.pt', '');
            }

            if( //对骰面做渲染
                isset($data['numRoll']['raw'])
                && count($data['numRoll']['raw']) > 0
            ) {
                $html->set('$show.num', '');
                $html->set('$roll.num', count($data['numRoll']['raw']));
                $html->set('$total.num', $data['numRoll']['result']);
                $detail = array();
                foreach($data['numRoll']['raw'] as $k => $d) {
                    $detail[] = $d;
                }
                $html->set('$detail.num', implode('{?common.comma?}', $detail));
            } else {
                $html->set('$show.num', 'hidden');
                $html->set('$roll.num', '');
                $html->set('$total.num', '');
                $html->set('$detail.num', '');
            }

            if( //对基础值做渲染
                isset($data['baseAmount'])
                && $data['baseAmount'] != 0
            ) {
                $base = abs($data['baseAmount']);
                switch (bccomp($data['baseAmount'], 0)) {
                    case -1:
                        $html->set('$total.baseAmount', "{?common.sub?} {$base}");
                        $html->set('$show.baseAMount', '');
                        break;
                    
                    case 1:
                        $html->set('$total.baseAmount', "{?common.add?} {$base}");
                        $html->set('$show.baseAMount', '');
                        break;

                    default:
                        $html->set('$total.baseAmount', '');
                        $html->set('$show.baseAMount', 'hidden');
                        break;
                }
            } else {
                $html->set('$show.baseAmount', 'hidden');
                $html->set('$total.baseAmount', '');
            }
            // echo(\meshal\xChar::renderTag($charId));
            // \fPrint($data);
            break;

        case 'score': //一般数值的显示样式
            /**
             * 显示样式配置
             * 每个元素是一个数组，对应一个属性构成
             * - term: 这个属性构成的显示术语
             * - hide: 触发隐藏的值，如果这个构成部分等于这个值，那么就隐藏
             * - display: 显示在哪个容器中，modifier / multiplier
             * 
             * 没有在这里设定的属性构成恒定不显示（或由其他特殊逻辑处理显示）
             */
            $settings = array(
                'base' => array(
                    'term' => 'baseScore',
                    'benchmark' => null,
                    'display' => 'base'
                ),
                'feature' => array(
                    'term' => 'feature',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'ability' => array(
                    'term' => 'ability',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'equipment' => array(
                    'term' => 'equipment',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'carrying' => array(
                    'term' => 'carrying',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'buff' => array(
                    'term' => 'buff',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'featureMultiplier' => array(
                    'term' => 'feature',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'abilityMultiplier' => array(
                    'term' => 'ability',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'equipmentMultiplier' => array(
                    'term' => 'equipment',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'carryingMultiplier' => array(
                    'term' => 'carrying',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'buffMultiplier' => array(
                    'term' => 'buff',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                )
            );

            /**
             * score这里传递的datainfo参数中分成3个部分，用“.”分隔：
             * - score：参数头
             * - param1：属性的数据构成（用\fEncode(json_encode())处理过的）
             * - param2：引用哪个说明文本
             */

            $exploded = explode('.', $_GET['datainfo'], 3);
            $info = \meshal\xRule::getData("rule.{$exploded[2]}");
            $data = json_decode(\fDecode($exploded[1]), true);

            $html->loadTpl('tooltip/score/frame.html');
            $html->set('$term', "{?term.{$exploded[2]}?}");
            $html->set('$info', $info['content']);
            $html->set('$totalScore', $data['total']);

            $rendered = array();

            foreach ($settings as $type => $set) {
                if($data[$type] !== $set['benchmark']) {
                    $comp = array();

                    switch ($set['display']) {
                        case 'base':
                            $comp['--type'] = 'modSpecial';
                            $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.equal?} {$data[$type]}";
                            $rendered['base'][] = $comp;
                            break;

                        case 'modifier':
                            if($data[$type] > $set['benchmark']) {
                                $comp['--type'] = 'modPositive';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.add?}{$data[$type]}";
                            }
                            else {
                                $comp['--type'] = 'modNegative';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {$data[$type]}";
                            }
                            $rendered['modifier'][] = $comp;
                            break;

                        case 'multiplier':
                            if($data[$type] > $set['benchmark']) {
                                $comp['--type'] = 'modPositive';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.mul?}{$data[$type]}";
                            } else {
                                $comp['--type'] = 'modNegative';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.div?}".\fDiv(1, $data[$type], 2);
                            }
                            $rendered['multiplier'][] = $comp;
                            break;
                        
                        default:
                            # code...
                            break;
                    }
                }
            }

            $html->set('$base', empty($rendered['base']) ? '' : $html->duplicate('tooltip/score/row.html', $rendered['base']));
            $html->set('$modifier', empty($rendered['modifier']) ? '' : $html->duplicate('tooltip/score/row.html', $rendered['modifier']));
            $html->set('$multiplier', empty($rendered['multiplier']) ? '' :  $html->duplicate('tooltip/score/row.html', $rendered['multiplier']));

            break;

        case 'score-immune': //免疫的显示样式
                /**
                 * 显示样式配置
                 * 每个元素是一个数组，对应一个属性构成
                 * - term: 这个属性构成的显示术语
                 * - hide: 触发隐藏的值，如果这个构成部分等于这个值，那么就隐藏
                 * - display: 显示在哪个容器中，modifier / multiplier
                 * 
                 * 没有在这里设定的属性构成恒定不显示（或由其他特殊逻辑处理显示）
                 */
                $settings = array(
                    'base' => array(
                        'term' => 'baseScore',
                        'benchmark' => 0,
                        'display' => 'immune'
                    ),
                    'feature' => array(
                        'term' => 'feature',
                        'benchmark' => 0,
                        'display' => 'immune'
                    ),
                    'ability' => array(
                        'term' => 'ability',
                        'benchmark' => 0,
                        'display' => 'immune'
                    ),
                    'equipment' => array(
                        'term' => 'equipment',
                        'benchmark' => 0,
                        'display' => 'immune'
                    ),
                    'carrying' => array(
                        'term' => 'carrying',
                        'benchmark' => 0,
                        'display' => 'immune'
                    ),
                    'buff' => array(
                        'term' => 'buff',
                        'benchmark' => 0,
                        'display' => 'immune'
                    )
                );
    
                /**
                 * score这里传递的datainfo参数中分成3个部分，用“.”分隔：
                 * - score：参数头
                 * - param1：属性的数据构成（用\fEncode(json_encode())处理过的）
                 * - param2：引用哪个说明文本
                 */
    
                $exploded = explode('.', $_GET['datainfo'], 3);
                $info = \meshal\xRule::getData("rule.{$exploded[2]}");
                $data = json_decode(\fDecode($exploded[1]), true);
                $html->loadTpl('tooltip/score/frame.html');
                $html->set('$term', "{?term.{$exploded[2]}?}");
                $html->set('$info', $info['content']);
                $html->set('$totalScore', $data['total'] > 0 ? '{?term.immunity?}' : '');
    
                $rendered = array();
    
                foreach ($settings as $type => $set) {
                    if($data[$type] !== $set['benchmark']) {
                        $comp = array();

                        if(
                            $data[$type] > $set['benchmark']
                            && $set['display'] == 'immune'
                        ) {
                            $comp['--type'] = 'modPositive';
                            $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?term.immunity?}";
                        }
                        $rendered[] = $comp;
                    }
                }
    
                $html->set('$modifier', empty($rendered) ? '' : $html->duplicate('tooltip/score/row.html', $rendered));
                $html->set('$base', '');
                $html->set('$multiplier', '');
    
                break;

        case 'score-attr': //主要属性的显示样式
            /**
             * 显示样式配置
             * 每个元素是一个数组，对应一个属性构成
             * - term: 这个属性构成的显示术语
             * - benchmark: 触发隐藏的值，如果这个构成部分等于这个值，那么就隐藏
             * - display: 显示在哪个容器中，modifier / multiplier
             * 
             * 没有在这里设定的属性构成恒定不显示（或由其他特殊逻辑处理显示）
             */
            $settings = array(
                'base' => array(
                    'term' => 'baseScore',
                    'benchmark' => null,
                    'display' => 'base'
                ),
                'feature' => array(
                    'term' => 'feature',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'ability' => array(
                    'term' => 'ability',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'equipment' => array(
                    'term' => 'equipment',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'carrying' => array(
                    'term' => 'carrying',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'buff' => array(
                    'term' => 'buff',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'featureMultiplier' => array(
                    'term' => 'feature',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'abilityMultiplier' => array(
                    'term' => 'ability',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'equipmentMultiplier' => array(
                    'term' => 'equipment',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'carryingMultiplier' => array(
                    'term' => 'carrying',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'buffMultiplier' => array(
                    'term' => 'buff',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                )
            );

            /**
             * score这里传递的datainfo参数中分成3个部分，用“.”分隔：
             * - score：参数头
             * - param1：属性的数据构成（用\fEncode(json_encode())处理过的）
             * - param2：引用哪个说明文本
             */

            $exploded = explode('.', $_GET['datainfo'], 3);
            $info = \meshal\xRule::getData("rule.{$exploded[2]}");
            $data = json_decode(\fDecode($exploded[1]), true);
            $html->loadTpl('tooltip/score/frame.attr.html');
            $html->set('$term', "{?term.{$exploded[2]}?}");
            $html->set('$info', $info['content']);
            $html->set('$currentScore', $data['current']);
            $html->set('$maxScore', $data['total']);

            $rendered = array();

            foreach ($settings as $type => $set) {
                if($data[$type] !== $set['benchmark']) {
                    $comp = array();

                    switch ($set['display']) {
                        case 'base':
                            $comp['--type'] = 'modSpecial';
                            $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.equal?} {$data[$type]}";
                            $rendered['base'][] = $comp;
                            break;
                            
                        case 'modifier':
                            if($data[$type] > $set['benchmark']) {
                                $comp['--type'] = 'modPositive';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.add?}{$data[$type]}";
                            }
                            else {
                                $comp['--type'] = 'modNegative';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {$data[$type]}";
                            }
                            $rendered['modifier'][] = $comp;
                            break;

                        case 'multiplier':
                            if($data[$type] > $set['benchmark']) {
                                $comp['--type'] = 'modPositive';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.mul?}{$data[$type]}";
                            } else {
                                $comp['--type'] = 'modNegative';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.div?}".\fDiv(1, $data[$type], 2);
                            }
                            $rendered['multiplier'][] = $comp;
                            break;
                        
                        default:
                            # code...
                            break;
                    }
                }
            }

            $html->set('$base', empty($rendered['base']) ? '' : $html->duplicate('tooltip/score/row.html', $rendered['base']));
            $html->set('$modifier', empty($rendered['modifier']) ? '' : $html->duplicate('tooltip/score/row.html', $rendered['modifier']));
            $html->set('$multiplier', empty($rendered['multiplier']) ? '' :  $html->duplicate('tooltip/score/row.html', $rendered['multiplier']));

            switch ($data['nextRecover']) {
                case -1: //-1表示还没回营地
                    $html->set('$recoverDisplay', '');
                    $html->set('$recoverInfo', '{?common.recoverAtCampsite?}');
                    break;

                case 0: //0表示已经恢复满了
                    $html->set('$recoverDisplay', 'hidden');
                    $html->set('$recoverInfo', '');
                    break;
                
                default: //默认显示
                    $html->set('$recoverDisplay', '');
                    $html->set('$recoverInfo', '{?common.nextRecoverTime?}'.\fFormatTime(time() + $data['nextRecover']));
                    break;
            }
            // $html->set('$recoverDisplay', $data['nextRecover'] == 0 ? 'hidden' : '');
            // $html->set('$nextRecoverTime', $data['nextRecover'] == 0 ? '' : \fFormatTime(time() + $data['nextRecover']));

            break;

        case 'score-cc': //主要属性的显示样式
            /**
             * 显示样式配置
             * 每个元素是一个数组，对应一个属性构成
             * - term: 这个属性构成的显示术语
             * - benchmark: 触发隐藏的值，如果这个构成部分等于这个值，那么就隐藏
             * - display: 显示在哪个容器中，modifier / multiplier
             * 
             * 没有在这里设定的属性构成恒定不显示（或由其他特殊逻辑处理显示）
             */
            $settings = array(
                'base' => array(
                    'term' => 'baseScore',
                    'benchmark' => null,
                    'display' => 'base'
                ),
                'feature' => array(
                    'term' => 'feature',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'ability' => array(
                    'term' => 'ability',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'equipment' => array(
                    'term' => 'equipment',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'carrying' => array(
                    'term' => 'carrying',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'buff' => array(
                    'term' => 'buff',
                    'benchmark' => 0,
                    'display' => 'modifier'
                ),
                'featureMultiplier' => array(
                    'term' => 'feature',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'abilityMultiplier' => array(
                    'term' => 'ability',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'equipmentMultiplier' => array(
                    'term' => 'equipment',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'carryingMultiplier' => array(
                    'term' => 'carrying',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                ),
                'buffMultiplier' => array(
                    'term' => 'buff',
                    'benchmark' => 1,
                    'display' => 'multiplier'
                )
            );

            /**
             * score这里传递的datainfo参数中分成3个部分，用“.”分隔：
             * - score：参数头
             * - param1：属性的数据构成（用\fEncode(json_encode())处理过的）
             * - param2：引用哪个说明文本
             */

            $exploded = explode('.', $_GET['datainfo'], 3);
            $info = \meshal\xRule::getData("rule.{$exploded[2]}");
            $data = json_decode(\fDecode($exploded[1]), true);
            $html->loadTpl('tooltip/score/frame.cc.html');
            $html->set('$term', "{?term.{$exploded[2]}?}");
            $html->set('$info', $info['content']);
            $html->set('$currentScore', $data['current']);
            $html->set('$maxScore', $data['total']);

            $rendered = array();

            foreach ($settings as $type => $set) {
                if($data[$type] !== $set['benchmark']) {
                    $comp = array();

                    switch ($set['display']) {
                        case 'base':
                            $comp['--type'] = 'modSpecial';
                            $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.equal?} {$data[$type]}";
                            $rendered['base'][] = $comp;
                            break;
                            
                        case 'modifier':
                            if($data[$type] > $set['benchmark']) {
                                $comp['--type'] = 'modPositive';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.add?}{$data[$type]}";
                            }
                            else {
                                $comp['--type'] = 'modNegative';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {$data[$type]}";
                            }
                            $rendered['modifier'][] = $comp;
                            break;

                        case 'multiplier':
                            if($data[$type] > $set['benchmark']) {
                                $comp['--type'] = 'modPositive';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.mul?}{$data[$type]}";
                            } else {
                                $comp['--type'] = 'modNegative';
                                $comp['--mod'] = "{?term.score.mod.{$set['term']}?} {?common.scoreMod.div?}".\fDiv(1, $data[$type], 2);
                            }
                            $rendered['multiplier'][] = $comp;
                            break;
                        
                        default:
                            # code...
                            break;
                    }
                }
            }

            $html->set('$base', empty($rendered['base']) ? '' : $html->duplicate('tooltip/score/row.html', $rendered['base']));
            $html->set('$modifier', empty($rendered['modifier']) ? '' : $html->duplicate('tooltip/score/row.html', $rendered['modifier']));
            $html->set('$multiplier', empty($rendered['multiplier']) ? '' :  $html->duplicate('tooltip/score/row.html', $rendered['multiplier']));

            switch ($data['nextRecover']) {
                case -1: //-1表示还没回营地
                    $html->set('$recoverDisplay', '');
                    $html->set('$recoverInfo', '{?common.recoverAtCampsite?}');
                    break;

                case 0: //0表示已经恢复满了
                    $html->set('$recoverDisplay', 'hidden');
                    $html->set('$recoverInfo', '');
                    break;
                
                default: //默认显示
                    $html->set('$recoverDisplay', '');
                    $html->set('$recoverInfo', '{?common.nextRecoverTime?}'.\fFormatTime(time() + $data['nextRecover']));
                    break;
            }
            // $html->set('$recoverDisplay', $data['nextRecover'] == 0 ? 'hidden' : '');
            // $html->set('$nextRecoverTime', $data['nextRecover'] == 0 ? '' : \fFormatTime(time() + $data['nextRecover']));

            break;

        case 'feature':
            /**
             * 显示样式配置
             */
            $settings = array(
                /**
                 * 数据格式，写在数组里的代表会做显示
                 * - positive: 有增量时显示，为true默认显示，否则显示键值给定的内容
                 * - negative: 有减量时显示，为true默认显示，否则显示键值给定的内容
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


            $data = \meshal\xFeature::getData($param[1], $param[2]);

            $html->set(
                '$debug', 
                $GLOBALS['debug']['tooltip'] === true
                    ? $data['probability']['result'] * 100.0 . '%'
                    : ''
            );
            $html->set('$rarity', $data['probability']['rarity']);
            $html->set('$rarityStyle', $data['probability']['rarityStyle']);

            $html->loadTpl('tooltip/feature/frame.html');
            
            $prepared = array();

            //修正的增减处理
            foreach ($data['data']['modifier'] as $scoreName => $v) {
                $raw = array();
                switch (true) {
                    case ( //增加显示
                        $v > 0
                        && isset($settings[$scoreName]['positive'])
                    ):
                        if($settings[$scoreName]['positive'] === true) {
                            $raw['--effect'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.add?}{$v}";
                        } else {
                            $raw['--effect'] = $settings[$scoreName]['positive'];
                        }
                        
                        $raw['--type'] = 'modPositive';

                        $prepared[] = $raw;
                        break;

                    case ( //减少显示
                        $v < 0
                        && isset($settings[$scoreName]['negative'])
                    ):
                        if($settings[$scoreName]['negative'] === true) {
                            $raw['--effect'] = "{?term.score.{$settings[$scoreName]['term']}?} {$v}";
                        } else {
                            $raw['--effect'] = $settings[$scoreName]['negative'];
                        }

                        $raw['--type'] = 'modNegative';

                        $prepared[] = $raw;
                        break;

                    default:
                        //不显示
                        break;
                }
            }

            //修正的倍数处理
            foreach ($data['data']['multiplier'] as $scoreName => $v) {
                $raw = array();
                switch (true) {
                    case ( //倍数>1显示
                        $v > 1
                        && isset($settings[$scoreName]['positive'])
                    ):
                        if($settings[$scoreName]['positive'] === true) {
                            $raw['--effect'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.mul?}{$v}";
                        } else {
                            $raw['--effect'] = $settings[$scoreName]['positive'];
                        }
                        
                        $raw['--type'] = 'modPositive';

                        $prepared[] = $raw;
                        break;

                    case ( //倍数<1显示
                        $v < 1
                        && isset($settings[$scoreName]['negative'])
                    ):
                        if($settings[$scoreName]['negative'] === true) {
                            $raw['--effect'] = "{?term.score.{$settings[$scoreName]['term']}?} {?common.scoreMod.div?}".\fDiv(1, $v, 2);
                        } else {
                            $raw['--effect'] = $settings[$scoreName]['negative'];
                        }
                        
                        $raw['--type'] = 'modNegative';

                        $prepared[] = $raw;
                        break;

                    default:
                        //不显示
                        break;
                }
            }

            //实力修正
            $raw = array();
            switch (true) {
                case (
                    $data['strength'] > 0
                    && isset($settings['strength']['positive'])
                ):
                    if($settings['strength']['positive'] === true) {
                        $raw['--effect'] = "{?term.score.strength?} {?common.scoreMod.add?}{$data['strength']}";
                    } else {
                        $raw['--effect'] = $settings['strength']['positive'];
                    }

                    $raw['--type'] = 'modSpecial';

                    $prepared[] = $raw;
                    break;
                
                case (
                    $data['strength'] < 0
                    && isset($settings['strength']['negative'])
                ):
                    if($settings['strength']['negative'] === true) {
                        $raw['--effect'] = "{?term.score.strength?} {$data['strength']}";
                    } else {
                        $raw['--effect'] = $settings['strength']['negative'];
                    }

                    $raw['--type'] = 'modSpecial';

                    $prepared[] = $raw;
                    break;
                    
                default:
                    //不显示
                    break;
            }
            
            $html->set('$featureType', $data['type']);
            $html->set('$featureName', $html->dbLang("featureName.{$data['type']}.{$data['name']}"));
            $html->set('$desc', $html->dbLang("featureDesc.{$data['type']}.{$data['name']}"));

            if(empty($prepared)) {
                $html->set('$modDisplay', 'hidden');
            } else {
                $html->set('$modDisplay', '');
            }
            $html->set(
                '$modifiers',
                $html->duplicate(
                    'tooltip/feature/row.html',
                    $prepared
                )
            );
            break;

        case 'item':
            $html->loadTpl('tooltip/frame.html');
            $item = new \meshal\xItem;
            $item->load($param[1]);
            $html->set('$tooltipContent', $item->render($user, 'tooltip/item/frame.html'));

            break;

        case 'facility':
            $html->loadTpl('tooltip/frame.html');
            $facility = new \meshal\xFacility;
            $facility->load($param[1], $param[2]);
            $html->set('$tooltipContent', $facility->render(false, $user, 'tooltip/facility/frame.html'));

            break;
        
        default:
            # code...
            break;
    }
}

$html->output('tooltip');
\fDie();
?>