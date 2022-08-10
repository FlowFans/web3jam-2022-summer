<?php
namespace meshal\adventure;

use xHtml;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#渲染冒险日志的类
################################################

class xLogRenderer
{
    function __construct(
        \meshal\xAdventure &$parent
    ) {
        global $db;
        $this->db = $db;
        $this->html = new \xHtml;
        $this->parent = $parent;
    }

    /**
     * 掷骰说明
     */
    public function roll(
        int $charId,
        array $data
    ) {
        return $this->html->quickRender(
            'adventure/log/rollInfo.html',
            array(
                '$rollData' => $charId.'.'.\fEncode(json_encode($data))
            )
        );
    }

    ################################################
    #冒险开始渲染
    ################################################

    /**
     * 渲染冒险开始的描述
     * 
     * @param string $adventureName
     * 冒险的名称
     */
    public function event_adventureStart(
        string $adventureName
    ) {
        if( //如果有为冒险单独定义的遭遇进入说明
            $this->html->existLang("adventureProlog.{$adventureName}") > 0
        ) {
            $prolog = "{?adventureProlog.{$adventureName}?}";
        } 
        
        else { //如果没有为冒险单独定义的遭遇进入说明，使用遭遇的默认说明
            $prolog = "{?common.adventureProlog?}";
        }

        return $this->html->quickRender(
            'adventure/log/adventureStart.html',
            array(
                '$adventureProlog' => $prolog
            )
        );
    }

    /**
     * 渲染检查入口的描述
     * 
     * @param array $data
     * 一段log数据
     */
    public function event_checkEntrance(
        array $data
    ) {
        if(!empty($data)) {
            $arr = array();
            //组织冒险的入口描述
            foreach ($data as $k => $info) {
                $dup = array();
                if($info['summary'] == true) {
                    if($this->html->existLang("adventureEntrance.{$this->parent->templateName}.{$info['encounter']}") > 0) {
                        //如果有为冒险单独定义的遭遇进入说明
                        $dup['--entranceDesc'] = "{?adventureEntrance.{$this->parent->templateName}.{$info['encounter']}?}";
                    } else {
                        //如果没有为冒险单独定义的遭遇进入说明，使用遭遇的默认说明
                        $dup['--entranceDesc'] = "{?adventureEntrance.{$info['encounter']}?}";
                    }
                    
                    $arr[] = $dup;
                }

                // $dup = array();
                // if($info['summary'] == true) {
                //     $dup['--entranceDesc'] = "{?adventureEntrance.{$info['encounter']}?}";
                //     $arr[] = $dup;
                // }
            }
        }

        $entrances = $this->html->duplicate(
            'adventure/log/checkEntrance.entrance.html',
            $arr
        );

        return $this->html->quickRender(
            'adventure/log/checkEntrance.html',
            array(
                '$entrances' => $entrances
            )
        );
    }

    /**
     * 渲染进入入口的描述
     * 
     * @param array $data
     * 一段log数据
     */
    public function event_gotoEntrance($data) {
        if($this->html->existLang("encounterApproach.{$this->parent->templateName}.{$data['encounter']}") > 0) {
            //如果有为冒险单独定义的遭遇进入说明
            $encounterGotoEntrance = "{?encounterApproach.{$this->parent->templateName}.{$data['encounter']}?}";
        } else {
            //如果没有为冒险单独定义的遭遇进入说明，使用遭遇的默认说明
            $encounterGotoEntrance = "{?encounterApproach.{$data['encounter']}?}";
        }

        return $this->html->quickRender(
            'adventure/log/gotoEntrance.html',
            array(
                '$gotoEntranceDesc' => $encounterGotoEntrance
            )
        );
    }

    ################################################
    #冒险过程渲染
    ################################################

    /**
     * 进入一个冒险中的一场遭遇，这个方法用于渲染进入遭遇后的情况描述
     * 
     * @param string $adventureName
     * 冒险的名称
     * 
     * @param string $encounterName
     * 遭遇的名称
     */
    public function event_encounterProcess(
        string $adventureName,
        string $encounterName
    ) {
        $return = '';
        /**
         * 遭遇进入说明
         */
        if( //如果有为冒险单独定义的遭遇进入说明
            $this->html->existLang("encounterProcess.{$adventureName}.{$encounterName}") > 0
        ) {
            $encounterProcess = "{?encounterProcess.{$adventureName}.{$encounterName}?}";
        } 
        
        else { //如果没有为冒险单独定义的遭遇进入说明，使用遭遇的默认说明
            $encounterProcess = "{?encounterProcess.{$encounterName}?}";
        }

        $return .= $this->html->quickRender(
            'adventure/log/encounterProcess.html',
            array(
                '$encounterProcess' => $encounterProcess
            )
        );

        return $return;
    }

    /**
     * 冒险主体事件
     */
    public function event_encounterEvent( 
        array $data,
        string $adventureName,
        string $encounterName
    ) {
        $return = '';
        $hasChecks = 0;
        
        //渲染checkAll记录
        if(!empty($data['checkAll'])) {
            $hasChecks ++;
            $arr = array();
            $succeed = 1;
            foreach($data['checkAll'] as $idx => $info) { //遍历每个检查，并渲染它们
                $method = 'check_'.$info['method'];
                $arr[$idx]['--checkDetail'] = '';
                if(method_exists($this, $method)) {
                    $arr[$idx]['--checkDetail'] = $this->$method($info['detail']);
                    $arr[$idx]['--checkStat'] = $info['detail']['summary'] == true ? 'succeeded' : 'failed';
                    $succeed *= $info['detail']['summary'];
                } else {
                    break;
                }
            }
            $return .= $this->html->quickRender(
                'adventure/log/checkAll.html',
                array(
                    '$checkAll' => $this->html->duplicate(
                        'adventure/log/check.html',
                        $arr
                    ),
                    '$checkStat' => $succeed > 0 ? 'succeeded' : 'failed'
                )
            );
        }

        //渲染checkAny记录
        if(!empty($data['checkAny'])) {
            $hasChecks ++;
            $arr = array();
            $succeed = 0;
            foreach($data['checkAny'] as $idx => $info) { //遍历每个检查，并渲染它们
                $method = 'check_'.$info['method'];
                $arr[$idx]['--checkDetail'] = '';
                if(method_exists($this, $method)) {
                    $arr[$idx]['--checkDetail'] = $this->$method($info['detail']);
                    $arr[$idx]['--checkStat'] = $info['detail']['summary'] == true ? 'succeeded' : 'failed';
                    $succeed += $info['detail']['summary'];
                } else {
                    break;
                }
            }
            $return .= $this->html->quickRender(
                'adventure/log/checkAny.html',
                array(
                    '$checkAny' => $this->html->duplicate(
                        'adventure/log/check.html',
                        $arr
                    ),
                    '$checkStat' => $succeed > 0 ? 'succeeded' : 'failed'
                )
            );
        }

        
        



        //渲染执行记录
        if(!empty($data['execute'])) {
            // if($hasChecks > 0) { //添加一个衔接符号表示checks和execute的逻辑关系
            //     $return .= $this->html->quickRender(
            //         'adventure/log/checksToExecute.html',
            //         array()
            //     );
            // }

            //渲染检定-执行衔接（成功/失败）
            if(
                isset($data['summary'])
                && $hasChecks > 0
            ) {
                if($data['summary'] == true) { //总体检查成功
                    if($this->html->existLang("encounterSuccess.{$adventureName}.{$encounterName}") > 0) {
                        //如果有为冒险单独定义的遭遇衔接说明
                        $encounterSegue = "{?encounterSuccess.{$adventureName}.{$encounterName}?}";
                    } 
                    
                    elseif($this->html->existLang("encounterSuccess.{$encounterName}") > 0) {
                        //如果没有为冒险单独定义的遭遇衔接说明，使用遭遇的默认说明
                        $encounterSegue = "{?encounterSuccess.{$encounterName}?}";
                    }

                    else {
                        //如果没有遭遇的默认说明，使用通用的默认说明
                        $encounterSegue = "{?common.encounterSuccess?}";
                    }
                }
                
                else { //总体检查失败
                    if($this->html->existLang("encounterFailure.{$adventureName}.{$encounterName}") > 0) {
                        //如果有为冒险单独定义的遭遇衔接说明
                        $encounterSegue = "{?encounterFailure.{$adventureName}.{$encounterName}?}";
                    } 
                    
                    elseif($this->html->existLang("encounterFailure.{$encounterName}") > 0) {
                        //如果没有为冒险单独定义的遭遇衔接说明，使用遭遇的默认说明
                        $encounterSegue = "{?encounterFailure.{$encounterName}?}";
                    }

                    else {
                        //如果没有遭遇的默认说明，使用通用的默认说明
                        $encounterSegue = "{?common.encounterFailure?}";
                    }
                }

                $return .= $this->html->quickRender(
                    'adventure/log/encounterSegue.html',
                    array(
                        '$encounterSegue' => $encounterSegue
                    )
                );
            }

            $arr = array();
            foreach($data['execute'] as $idx => $info) {//遍历每个执行记录，并渲染它们
                $method = 'execute_'.$info['method'];
                $arr[$idx]['--executeDetail'] = '';
                if(method_exists($this, $method)) {
                    $arr[$idx]['--executeDetail'] = $this->$method($info['detail']);
                } else {
                    break;
                }
            }
            $return .= $this->html->quickRender(
                'adventure/log/executes.html',
                array(
                    '$executes' => $this->html->duplicate(
                        'adventure/log/execute.html',
                        $arr
                    )
                )
            );
        }

        return $return;
    }

    /**
     * 渲染检查下一个遭遇入口的描述
     * 
     * @param array $data
     * 一段log数据
     */
    public function event_checkNextScene(
        array $data
    ) {
        if(!empty($data)) {
            $arr = array();
            //组织冒险的入口描述
            foreach ($data['scenes'] as $k => $info) {
                $dup = array();
                if($info['summary'] == true) {
                    if($this->html->existLang("adventureEntrance.{$this->parent->templateName}.{$info['encounter']}") > 0) {
                        //如果有为冒险单独定义的遭遇进入说明
                        $dup['--entranceDesc'] = "{?adventureEntrance.{$this->parent->templateName}.{$info['encounter']}?}";
                    } else {
                        //如果没有为冒险单独定义的遭遇进入说明，使用遭遇的默认说明
                        $dup['--entranceDesc'] = "{?adventureEntrance.{$info['encounter']}?}";
                    }
                    
                    $arr[] = $dup;
                }
            }
        }

        $entrances = $this->html->duplicate(
            'adventure/log/checkNextScene.entrance.html',
            $arr
        );

        return $this->html->quickRender(
            'adventure/log/checkNextScene.html',
            array(
                '$entrances' => $entrances
            )
        );
    }

    /**
     * 渲染进入入口的描述
     * 
     * @param array $data
     * 一段log数据
     */
    public function event_gotoNextScene($data) {
        if($this->html->existLang("encounterApproach.{$this->parent->templateName}.{$data['encounter']}") > 0) {
            //如果有为冒险单独定义的遭遇进入说明
            $encounterGotoEntrance = "{?encounterApproach.{$this->parent->templateName}.{$data['encounter']}?}";
        } else {
            //如果没有为冒险单独定义的遭遇进入说明，使用遭遇的默认说明
            $encounterGotoEntrance = "{?encounterApproach.{$data['encounter']}?}";
        }

        return $this->html->quickRender(
            'adventure/log/gotoEntrance.html',
            array(
                '$gotoEntranceDesc' => $encounterGotoEntrance
            )
        );

        // return $this->html->quickRender(
        //     'adventure/log/gotoEntrance.html',
        //     array(
        //         '$encounterName' => $data['encounter']
        //     )
        // );
    }

    ################################################
    #冒险结束渲染
    ################################################

    public function event_adventureEnd(
        string $adventureName
    ) {
        if( //如果有为冒险单独定义的遭遇进入说明
            $this->html->existLang("adventureEpilog.{$adventureName}") > 0
        ) {
            $epilog = "{?adventureEpilog.{$adventureName}?}";
        } 
        
        else { //如果没有为冒险单独定义的遭遇进入说明，使用遭遇的默认说明
            $epilog = "{?common.adventureEpilog?}";
        }

        return $this->html->quickRender(
            'adventure/log/adventureEnd.html',
            array(
                '$adventureEpilog' => $epilog
            )
        );

        $return = '';

        $return .= $this->html->quickRender( //冒险结束文本
            'adventure/log/adventureEnd.html',
            array(
                '$adventureName' => $adventureName
            )
        );

        return $return;
    }

    /**
     * 渲染结束事件
     */
    public function event_endEvent( 
        array $data
    ) {
        $return = '';
        
        //渲染checkAll记录
        if(!empty($data['checkAll'])) {
            $arr = array();
            $succeed = 1;
            foreach($data['checkAll'] as $idx => $info) { //遍历每个检查，并渲染它们
                $method = 'check_'.$info['method'];
                $arr[$idx]['--checkDetail'] = '';
                if(method_exists($this, $method)) {
                    $arr[$idx]['--checkDetail'] = $this->$method($info['detail']);
                    $arr[$idx]['--checkStat'] = $info['detail']['summary'] == true ? 'succeeded' : 'failed';
                    $succeed *= $info['detail']['summary'];
                } else {
                    break;
                }
            }
            $return .= $this->html->quickRender(
                'adventure/log/checkAll.html',
                array(
                    '$checkAll' => $this->html->duplicate(
                        'adventure/log/check.html',
                        $arr
                    ),
                    '$checkStat' => $succeed > 0 ? 'succeeded' : 'failed'
                )
            );


            // $arr = array();
            // foreach($data['checkAll'] as $idx => $info) { //遍历每个检查，并渲染它们
            //     $method = 'check_'.$info['method'];
            //     $arr[$idx]['--checkDetail'] = '';
            //     if(method_exists($this, $method)) {
            //         $arr[$idx]['--checkDetail'] = $this->$method($info['detail']);
            //     }
            // }
            // $return .= $this->html->duplicate(
            //     'adventure/log/checkAll.html',
            //     $arr
            // );
        }

        //渲染checkAny记录
        if(!empty($data['checkAny'])) {
            $arr = array();
            $succeed = 0;
            foreach($data['checkAny'] as $idx => $info) { //遍历每个检查，并渲染它们
                $method = 'check_'.$info['method'];
                $arr[$idx]['--checkDetail'] = '';
                if(method_exists($this, $method)) {
                    $arr[$idx]['--checkDetail'] = $this->$method($info['detail']);
                    $arr[$idx]['--checkStat'] = $info['detail']['summary'] == true ? 'succeeded' : 'failed';
                    $succeed += $info['detail']['summary'];
                } else {
                    break;
                }
            }
            $return .= $this->html->quickRender(
                'adventure/log/checkAny.html',
                array(
                    '$checkAny' => $this->html->duplicate(
                        'adventure/log/check.html',
                        $arr
                    ),
                    '$checkStat' => $succeed > 0 ? 'succeeded' : 'failed'
                )
            );
            // $arr = array();
            // foreach($data['checkAny'] as $idx => $info) { //遍历每个检查，并渲染它们
            //     $method = 'check_'.$info['method'];
            //     $arr[$idx]['--checkDetail'] = '';
            //     if(method_exists($this, $method)) {
            //         $arr[$idx]['--checkDetail'] = $this->$method($info['detail']);
            //     }
            // }
            // $return .= $this->html->duplicate(
            //     'adventure/log/checkAny.html',
            //     $arr
            // );
        }

        //渲染执行记录
        if(!empty($data['execute'])) {
            $arr = array();
            foreach($data['execute'] as $idx => $info) {//遍历每个执行记录，并渲染它们
                $method = 'execute_'.$info['method'];
                $arr[$idx]['--executeDetail'] = '';
                if(method_exists($this, $method)) {
                    $arr[$idx]['--executeDetail'] = $this->$method($info['result']);
                } else {
                    break;
                }
            }
            $return .= $this->html->quickRender(
                'adventure/log/executes.html',
                array(
                    '$executes' => $this->html->duplicate(
                        'adventure/log/execute.html',
                        $arr
                    )
                )
            );
            // $arr = array();
            // foreach($data['execute'] as $idx => $info) {//遍历每个执行记录，并渲染它们
            //     $method = 'execute_'.$info['method'];
            //     $arr[$idx]['--executeDetail'] = '';
            //     if(method_exists($this, $method)) {
            //         $arr[$idx]['--executeDetail'] = $this->$method($info['result']);
            //     }
            // }
            // $return .= $this->html->duplicate(
            //     'adventure/log/execute.html',
            //     $arr
            // );
        }

        return $return;
    }

    ################################################
    #check方法渲染
    ################################################

    /**
     * 属性尝试（选最高的人）
     */
    public function check_attemptAttrHighest(
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/check.attemptAttrHighest.html');
        if(!empty($result['character'])) {
            foreach ($result['character'] as $charId => $scoreValue) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$scoreValue', $scoreValue);
                $renderer->set(
                    '$rollInfo', 
                    $this->roll($charId, array(
                        'ptRoll' => $result['result'],
                        'result' => $result['result']['result']
                    )
                ));
            }
        }
        
        if(is_null($result['params']['scoreName'])) {
            $renderer->set('$scoreName', "");
            $renderer->set('$scoreProp', "");
        } else {
            $renderer->set('$scoreName', "{?term.score.{$result['params']['scoreName']}?}");
            $renderer->set('$scoreProp', "{?term.scoreProp.{$result['params']['scoreProperty']}?}");
        }

        $renderer->set('$difficulty', $result['params']['difficulty']);
        $renderer->set('$result', $result['summary'] == true ? '{?common.succeeded?}' : '{?common.failed?}');
        

        switch (bccomp($result['params']['multiplier'], 1)) {
            case -1:
                $renderer->set('$multiplier', " {?common.divide?} ".\fDiv(1, $result['params']['multiplier']));
                break;
            
            case 1:
                $renderer->set('$multiplier', " {?common.multiply?} {$result['params']['multiplier']}");
                break;

            default:
                $renderer->set('$multiplier', '');
                break;
        }

        switch (bccomp($result['params']['modifier'], 0)) {
            case -1:
                $renderer->set('$modifier', " {?common.sub?} ".abs($result['params']['modifier']));
                break;
            
            case 1:
                $renderer->set('$modifier', " {?common.add?} {$result['params']['modifier']}");
                break;

            default:
                $renderer->set('$modifier', '');
                break;
        }
        return $renderer->render('body');
    }


    /**
     * 属性尝试（选最低的人）
     */
    public function check_attemptAttrLowest(
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/check.attemptAttrLowest.html');
        if(!empty($result['character'])) {
            foreach ($result['character'] as $charId => $scoreValue) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$scoreValue', $scoreValue);
                $renderer->set(
                    '$rollInfo', 
                    $this->roll($charId, array(
                        'ptRoll' => $result['result'],
                        'result' => $result['result']['result']
                    )
                ));
            }
        }
        
        if(is_null($result['params']['scoreName'])) {
            $renderer->set('$scoreName', "");
            $renderer->set('$scoreProp', "");
        } else {
            $renderer->set('$scoreName', "{?term.score.{$result['params']['scoreName']}?}");
            $renderer->set('$scoreProp', "{?term.scoreProp.{$result['params']['scoreProperty']}?}");
        }

        $renderer->set('$difficulty', $result['params']['difficulty']);
        $renderer->set('$result', $result['summary'] == true ? '{?common.succeeded?}' : '{?common.failed?}');
        

        switch (bccomp($result['params']['multiplier'], 1)) {
            case -1:
                $renderer->set('$multiplier', " {?common.divide?} ".\fDiv(1, $result['params']['multiplier']));
                break;
            
            case 1:
                $renderer->set('$multiplier', " {?common.multiply?} {$result['params']['multiplier']}");
                break;

            default:
                $renderer->set('$multiplier', '');
                break;
        }

        switch (bccomp($result['params']['modifier'], 0)) {
            case -1:
                $renderer->set('$modifier', " {?common.sub?} ".abs($result['params']['modifier']));
                break;
            
            case 1:
                $renderer->set('$modifier', " {?common.add?} {$result['params']['modifier']}");
                break;

            default:
                $renderer->set('$modifier', '');
                break;
        }
        return $renderer->render('body');
    }

    /**
     * 随机选择一个角色尝试
     */
    public function check_attemptRandomMember(
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/check.attemptRandomMember.html');
        if(!empty($result['character'])) {
            foreach ($result['character'] as $charId => $scoreValue) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$scoreValue', $scoreValue);
                $renderer->set(
                    '$rollInfo', 
                    $this->roll($charId, array(
                        'ptRoll' => $result['result'],
                        'result' => $result['result']['result']
                    )
                ));
            }
        }
        
        if(is_null($result['params']['scoreName'])) {
            $renderer->set('$scoreName', "");
            $renderer->set('$scoreProp', "");
        } else {
            $renderer->set('$scoreName', "{?term.score.{$result['params']['scoreName']}?}");
            $renderer->set('$scoreProp', "{?term.scoreProp.{$result['params']['scoreProperty']}?}");
        }
        
        $renderer->set('$difficulty', $result['params']['difficulty']);
        $renderer->set('$result', $result['summary'] == true ? '{?common.succeeded?}' : '{?common.failed?}');
        

        switch (bccomp($result['params']['multiplier'], 1)) {
            case -1:
                $renderer->set('$multiplier', " {?common.divide?} ".\fDiv(1, $result['params']['multiplier']));
                break;
            
            case 1:
                $renderer->set('$multiplier', " {?common.multiply?} {$result['params']['multiplier']}");
                break;

            default:
                $renderer->set('$multiplier', '');
                break;
        }

        switch (bccomp($result['params']['modifier'], 0)) {
            case -1:
                $renderer->set('$modifier', " {?common.sub?} ".abs($result['params']['modifier']));
                break;
            
            case 1:
                $renderer->set('$modifier', " {?common.add?} {$result['params']['modifier']}");
                break;

            default:
                $renderer->set('$modifier', '');
                break;
        }
        return $renderer->render('body');
    }

    ################################################
    #execute方法渲染
    ################################################

    /**
     * 给队伍每个成员添加潜能
     */
    public function execute_addPotentialityToTeam(
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/execute.addPotentialityToChar.html');

        $return = '';
        if(!empty($result['result'])) {
            // 遍历每个结果，并渲染它们
            foreach($result['result'] as $charId => $detail) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$rollInfo', $this->roll($charId, $detail));
                $renderer->set('$pp', $detail['result']);
                if($detail['result'] > 0) {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar?}');
                } else {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar.none?}');
                }
                $return .= $renderer->render('body');
            }
        }

        return $this->html->quickRender(
            'adventure/log/execute.addPotentialityToTeam.html',
            array(
                '$detail' => $return
            )
        );
    }

    /**
     * 给所有关联角色潜能
     */
    public function execute_addPotentialityToRelChar (
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/execute.addPotentialityToChar.html');

        $return = '';
        if(!empty($result['result'])) {
            // 遍历每个结果，并渲染它们
            foreach($result['result'] as $charId => $detail) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$rollInfo', $this->roll($charId, $detail));
                $renderer->set('$pp', $detail['result']);
                if($detail['result'] > 0) {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar?}');
                } else {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar.none?}');
                }
                $return .= $renderer->render('body');
            }
        }

        return $this->html->quickRender(
            'adventure/log/execute.addPotentiality.html',
            array(
                '$detail' => $return
            )
        );
    }

    /**
     * 给关联成功角色潜能
     */
    public function execute_addPotentialityToRelSuccess (
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/execute.addPotentialityToChar.html');

        $return = '';
        if(!empty($result['result'])) {
            // 遍历每个结果，并渲染它们
            foreach($result['result'] as $charId => $detail) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$rollInfo', $this->roll($charId, $detail));
                $renderer->set('$pp', $detail['result']);
                if($detail['result'] > 0) {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar?}');
                } else {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar.none?}');
                }
                $return .= $renderer->render('body');
            }
        }

        return $this->html->quickRender(
            'adventure/log/execute.addPotentiality.html',
            array(
                '$detail' => $return
            )
        );
    }

    /**
     * 给关联失败角色潜能
     */
    public function execute_addPotentialityToRelFailure (
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/execute.addPotentialityToChar.html');

        $return = '';
        if(!empty($result['result'])) {
            // 遍历每个结果，并渲染它们
            foreach($result['result'] as $charId => $detail) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$rollInfo', $this->roll($charId, $detail));
                $renderer->set('$pp', $detail['result']);
                if($detail['result'] > 0) {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar?}');
                } else {
                    $renderer->set('$result', '{?adventureEvent.execute.addPotentialityToChar.none?}');
                }
                $return .= $renderer->render('body');
            }
        }

        return $this->html->quickRender(
            'adventure/log/execute.addPotentiality.html',
            array(
                '$detail' => $return
            )
        );
    }

    /**
     * 给随机角色物品
     */
    public function execute_giveItemToRandomMember (
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;

        $return = '';

        $renderer->set('$item', \meshal\xItem::renderTag($result['result']['item']['itemName'], $result['result']['item']['lootAmnt']));
        
        foreach($result['result']['character'] as $idx => $stat) {
            $charId = $idx;

            if(is_null($stat)) {
                $renderer->set('$stat', '');
            } else {
                $renderer->set('$stat', "{?adventureEvent.execute.addItemToChar.{$stat}?}");
            }
        }
        $renderer->set('$char', \meshal\xChar::renderTag($charId));
        $renderer->set(
            '$rollInfo',
            $this->roll(
                $charId,
                array(
                    'ptRoll' => $result['result']['item']['lootAmntPt'],
                    'numRoll' => $result['result']['item']['lootAmntNum'],
                    'baseAmount' => $result['params']['baseAmount'],
                    'result' => $result['result']['item']['lootAmnt']
                )
            )
        );
        
        if($result['result']['item']['lootAmnt'] <= 0) {
            $renderer->loadTpl('adventure/log/execute.giveItemToChar.nothing.row.html');
        } else {
            $renderer->loadTpl('adventure/log/execute.giveItemToChar.row.html');
        }

        $return = $renderer->render('body');

        if($result['result']['item']['lootAmnt'] <= 0) {
            return $this->html->quickRender(
                'adventure/log/execute.giveItemToChar.nothing.html',
                array(
                    '$detail' => $return
                )
            );
        } else {
            return $this->html->quickRender(
                'adventure/log/execute.giveItemToChar.html',
                array(
                    '$detail' => $return
                )
            );
        }
    }

    /**
     * 对所有关联角色分别造成攻击
     */
    public function execute_attackRelChar (
        array $result
    ) {
        # 局部渲染器，避免和父级的xHtml对象混用
        $renderer = new \xHtml;
        $renderer->loadTpl('adventure/log/execute.attackChar.html');

        $return = '';
        if(!empty($result['result'])) {
            // 遍历每个结果，并渲染它们
            foreach($result['result'] as $charId => $detail) {
                $renderer->set('$char', \meshal\xChar::renderTag($charId));
                $renderer->set('$rollInfo', $this->roll($charId, $detail));
                if($detail['result'] > 0) {
                    $renderer->set('$result', '{?adventureEvent.execute.attackChar?}');
                } else {
                    $renderer->set('$result', '{?adventureEvent.execute.attackChar.missed?}');
                }
                $return .= $renderer->render('body');
            }
        }

        return $this->html->quickRender(
            'adventure/log/execute.attackChar.html',
            array(
                '$detail' => $return
            )
        );
    }
}
?>