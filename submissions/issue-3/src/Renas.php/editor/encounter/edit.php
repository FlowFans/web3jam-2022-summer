<?php
################################################
# 初始化开始
################################################

# 常量 _EXTERNAL 用于表示这个脚本是否可被外部访问
define('_EXTERNAL', true); 

#规定这个脚本所在的相对根目录的路径，每个可被外部访问的脚本都需要定义这个常量。
define('_ROOT','./../../');

# 启动时加载 loader
require_once _ROOT.'_loader.php';

################################################
# 初始化结束
################################################

use meshal\adventure\xEncounter as xEncounter;

// $db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

//只允许特定用户组访问
$user->challengeRole('admin', 'editor');

$error = 0; //错误计数器

if($_POST['submit']) { //有提交行为
    \fLog('Received $_POST data:');
    \fLog(\fDump($_POST), 1, false);
    \fLog('Received $_GET data:');
    \fLog(\fDump($_GET), 1, false);
    if($_POST['editName'] !== '') { //是提交对已有遭遇的编辑
        $data = \meshal\adventure\xEncounter::getData($_POST['editName']);

        //取 adventureEntrance 对应的语言记录
        $adventureEntrance = $db->getArr(
            'languages',
            array(
                "`name` = 'adventureEntrance.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        //取 encounterApproach 对应的语言记录
        $encounterApproach = $db->getArr(
            'languages',
            array(
                "`name` = 'encounterApproach.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        //取 encounterProcess 对应的语言记录
        $encounterProcess = $db->getArr(
            'languages',
            array(
                "`name` = 'encounterProcess.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        //取 encounterSuccess 对应的语言记录
        $encounterSuccess = $db->getArr(
            'languages',
            array(
                "`name` = 'encounterSuccess.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        //取 encounterFailure 对应的语言记录
        $encounterFailure = $db->getArr(
            'languages',
            array(
                "`name` = 'encounterFailure.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        $submit = localSubmit();

        if($error == 0) {
            if($data === false) {
                //假设数据库中不存在，那么就直接创建
                $check = $db->insert(
                    'encounters',
                    $submit
                );
    
                //插入或更新 adventureEntrance 对应的语言记录
                if($adventureEntrance === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureEntrance.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureEntrance']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureEntrance']),
                        ),
                        array(
                            "`name` = 'adventureEntrance.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterApproach 对应的语言记录
                if($encounterApproach === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterApproach.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterApproach']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterApproach']),
                        ),
                        array(
                            "`name` = 'encounterApproach.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterProcess 对应的语言记录
                if($encounterProcess === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterProcess.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterProcess']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterProcess']),
                        ),
                        array(
                            "`name` = 'encounterProcess.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterSuccess 对应的语言记录
                if($encounterSuccess === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterSuccess.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterSuccess']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterSuccess']),
                        ),
                        array(
                            "`name` = 'encounterSuccess.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterFailure 对应的语言记录
                if($encounterFailure === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterFailure.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterFailure']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterFailure']),
                        ),
                        array(
                            "`name` = 'encounterFailure.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }
            } else {
                //数据库中有记录，则更新数据
                $postEditName = $_POST['editName'];
                $check = $db->update(
                    'encounters',
                    $submit,
                    array(
                        "`name` = '{$postEditName}'"
                    ),
                    1
                );
    
                //插入或更新 adventureEntrance 对应的语言记录
                if($adventureEntrance === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureEntrance.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureEntrance']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureEntrance']),
                        ),
                        array(
                            "`name` = 'adventureEntrance.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterApproach 对应的语言记录
                if($encounterApproach === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterApproach.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterApproach']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterApproach']),
                        ),
                        array(
                            "`name` = 'encounterApproach.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterProcess 对应的语言记录
                if($encounterProcess === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterProcess.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterProcess']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterProcess']),
                        ),
                        array(
                            "`name` = 'encounterProcess.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterSuccess 对应的语言记录
                if($encounterSuccess === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterSuccess.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterSuccess']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterSuccess']),
                        ),
                        array(
                            "`name` = 'encounterSuccess.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 encounterFailure 对应的语言记录
                if($encounterFailure === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "encounterFailure.{$_POST['editName']}",
                            'content' => \fEncode($_POST['encounterFailure']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['encounterFailure']),
                        ),
                        array(
                            "`name` = 'encounterFailure.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }
            }
        } else {
            //有错误提示，则不跳转页面，而是在当前页面显示错误提示
            $html->set('$editName', $_POST['editName']);

            //把已提交的数据重组回来
            foreach ($_POST as $k => $v) {
                $html->set("\${$k}", isset($v) ? $v : '');
            }

            //加载页面模板
            $html->loadTpl(
                'editor/encounter/body.editor.html',
                'body'
            );

            localAssembler();
            $html->set('$nameReadonly', 'readonly');
            $html->output();
            \fDie();
        }
        

        //进入重定向页
        $html->set('$encounterName', $_POST['editName']);

        $html->redirect(
            'index.php',
            'pageTitle.editor.encounter',
            'redirect.message.editor.encounter.updated'
        );
        \fDie(); 
        
    } else { //是提交一个新遭遇的数据
        //检查数据有效性
        if($_POST['name'] == '') {
            \fNotify('notify.editor.encounter.nameRequired', 'warn');
            $error++;
        }

        //检查是否有重名项
        if(xEncounter::getData($_POST['name']) !== false) {
            \fNotify('notify.editor.encounter.nameTaken', 'warn');
            $error++;
        }

        //检查token
        if(!$_POST['token'] || $_POST['token'] == '') {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.encounter',
                'redirect.message.editor.encounter.failed'
            );
            \fDie();
        }

        //根据token取encounter_stage数据
        $stage = $db->getArr(
            'encounter_stage',
            array(
                "`stageToken` = '{$_POST['token']}'"
            ),
            null,
            1
        );
        if($stage === false) {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.encounter',
                'redirect.message.editor.encounter.failed'
            );
            \fDie();
        }

        //组装提交的数据
        $submit = localSubmit();

        if($error == 0) {
            //插入数据
            $check = $db->insert(
                'encounters',
                $submit
            );

            //插入或更新 adventureEntrance 对应的语言记录
            $adventureEntrance = $db->getArr(
                'languages',
                array(
                    "`name` = 'adventureEntrance.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($adventureEntrance === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "adventureEntrance.{$_POST['name']}",
                        'content' => \fEncode($_POST['adventureEntrance']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['adventureEntrance']),
                    ),
                    array(
                        "`name` = 'adventureEntrance.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新 encounterApproach 对应的语言记录
            $encounterApproach = $db->getArr(
                'languages',
                array(
                    "`name` = 'encounterApproach.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($encounterApproach === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "encounterApproach.{$_POST['name']}",
                        'content' => \fEncode($_POST['encounterApproach']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['encounterApproach']),
                    ),
                    array(
                        "`name` = 'encounterApproach.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新 encounterProcess 对应的语言记录
            $encounterProcess = $db->getArr(
                'languages',
                array(
                    "`name` = 'encounterProcess.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($encounterProcess === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "encounterProcess.{$_POST['name']}",
                        'content' => \fEncode($_POST['encounterProcess']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['encounterProcess']),
                    ),
                    array(
                        "`name` = 'encounterProcess.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新 encounterSuccess 对应的语言记录
            $encounterSuccess = $db->getArr(
                'languages',
                array(
                    "`name` = 'encounterSuccess.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($encounterSuccess === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "encounterSuccess.{$_POST['name']}",
                        'content' => \fEncode($_POST['encounterSuccess']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['encounterSuccess']),
                    ),
                    array(
                        "`name` = 'encounterSuccess.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }
            
            //插入或更新 encounterFailure 对应的语言记录
            $encounterFailure = $db->getArr(
                'languages',
                array(
                    "`name` = 'encounterFailure.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($encounterFailure === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "encounterFailure.{$_POST['name']}",
                        'content' => \fEncode($_POST['encounterFailure']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['encounterFailure']),
                    ),
                    array(
                        "`name` = 'encounterFailure.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //进入重定向页
            $html->set('$encounterName', $_POST['name']);

            $html->redirect(
                'index.php',
                'pageTitle.editor.encounter',
                'redirect.message.editor.encounter.created'
            );
            \fDie(); 
            
        } else {
            //有错误提示，则不跳转页面，而是在当前页面显示错误提示
            $html->set('$editName', '');

            //把已提交的数据重组回来
            foreach ($_POST as $k => $v) {
                $html->set("\${$k}", isset($v) ? $v : '');
            }

            //加载页面模板
            $html->loadTpl(
                'editor/encounter/body.editor.html',
                'body'
            );

            localAssembler();
            $html->set('$nameReadonly', '');
            $html->output();
            \fDie();
        }
    }
} else { //非提交行为
    if($_GET['name']) { //通过链接进入的已有遭遇（GET方法从地址参数中获取）。读取已有遭遇并准备编辑。
        //加载模板
        $html->loadTpl(
            'editor/encounter/body.editor.html',
            'body'
        );
        
        $html->set('$nameReadonly', 'readonly');
        $html->set('$editName', $_GET['name']);

        $data = \meshal\adventure\xEncounter::getData($_GET['name']);

        if($data !== false) {
            //获取stage的数据
            $stageData = $db->getArr(
                'encounter_stage',
                array(
                    "`name` = '{$_GET['name']}'",
                    "`editorId` = '{$user->uid}'"
                ),
                null,
                1
            );

            //对旧的encounter_stage数据（该用户未保存的编辑）做清理
            $db->delete(
                'encounter_stage',
                array(
                    "`editorId` = '{$user->uid}'",
                )
            );

            //将加载到的数据存入encounter_stage用于编辑
            $token = \fGenGuid();
            $db->insert(
                'encounter_stage',
                array(
                    'editorId' => $user->uid,
                    'stageToken' => $token,
                    'name' => $data['name'],
                    'intensity' => $data['intensity'],
                    'duration' => $data['duration'],
                    'data' => json_encode($data['data']),
                    'loot' => json_encode($data['loot']),
                    'probabilityModifier' => $data['probability'],
                    'adventureEntrance' => \fEncode($html->dbLang("adventureEntrance.{$_GET['name']}")),
                    'encounterApproach' => \fEncode($html->dbLang("encounterApproach.{$_GET['name']}")),
                    'encounterProcess' => \fEncode($html->dbLang("encounterProcess.{$_GET['name']}")),
                    'encounterSuccess' => \fEncode($html->dbLang("encounterSuccess.{$_GET['name']}")),
                    'encounterFailure' => \fEncode($html->dbLang("encounterFailure.{$_GET['name']}")),
                )
            );

            //把加载到的数据填入前端
            $html->set('$token', $token);
            $html->set('$name', $data['name']);
            $html->set('$intensity', $data['intensity']);
            $html->set('$duration', $data['duration']);
            $html->set('$probabilityModifier', $data['probability']);
            $html->set('$adventureEntrance', $html->dbLang("adventureEntrance.{$_GET['name']}"));
            $html->set('$encounterApproach', $html->dbLang("encounterApproach.{$_GET['name']}"));
            $html->set('$encounterProcess', $html->dbLang("encounterProcess.{$_GET['name']}"));
            $html->set('$encounterSuccess', $html->dbLang("encounterSuccess.{$_GET['name']}"));
            $html->set('$encounterFailure', $html->dbLang("encounterFailure.{$_GET['name']}"));

            if(empty($data['data']['checkAll'])) {
                $html->set('$checkAll', '');
            } else {
                $arr = array();
                foreach($data['data']['checkAll'] as $k => $param) {
                    //把数组拼装回JSON格式
                    $arr[] = json_encode($param);
                }
                $html->set('$checkAll', implode(PHP_EOL, $arr));
            }

            if(empty($data['data']['checkAny'])) {
                $html->set('$checkAny', '');
            } else {
                $arr = array();
                foreach($data['data']['checkAny'] as $k => $param) {
                    //把数组拼装回JSON格式
                    $arr[] = json_encode($param);
                }
                $html->set('$checkAny', implode(PHP_EOL, $arr));
            }

            if(empty($data['data']['success'])) {
                $html->set('$success', '');
            } else {
                $arr = array();
                foreach($data['data']['success'] as $method => $param) {
                    //把数组拼装回JSON格式
                    $arr[] = json_encode($param);
                }
                $html->set('$success', implode(PHP_EOL, $arr));
            }

            if(empty($data['data']['failure'])) {
                $html->set('$failure', '');
            } else {
                $arr = array();
                foreach($data['data']['failure'] as $method => $param) {
                    //把数组拼装回JSON格式
                    $arr[] = json_encode($param);
                }
                $html->set('$failure', implode(PHP_EOL, $arr));
            }

            $html->set('$nameReadonly', 'readonly');
            $html->output();
            \fDie();

        } else { //没有查到这个遭遇，那么从白板开始
            //清除占位变量
            localPreset();
            $html->output();
            \fDie();
        }
    } else { //一个白板物品的创建页面
        $token = \fGenGuid();

        //删掉用户之前在encounter_stage中的信息
        $db->delete(
            'encounter_stage',
            array(
                "`editorId` = '{$user->uid}'"
            ),
            1
        );

        //为这次的白板编辑新建一个encounter_stage记录
        $db->insert(
            'encounter_stage',
            array(
                'editorId' => $user->uid,
                'stageToken' => $token
            )
        );

        $html->loadTpl(
            'editor/encounter/body.editor.html',
            'body'
        );

        localPreset();
        localAssembler();

        $html->set('$token', $token);
        $html->output();
        \fDie();
    }
}

\fDie();

//一些dup模块的标准化组装
function localAssembler() {}

//预设变量
function localPreset() {
    global $html;

    $html->set('$token', '');
    $html->set('$nameReadonly', '');
    $html->set('$editName', '');
    $html->set('$name', '');
    $html->set('$intensity', 0);
    $html->set('$duration', 0);
    $html->set('$probabilityModifier', 0);

    $html->set('$adventureEntrance', '');
    $html->set('$encounterApproach', '');
    $html->set('$encounterProcess', '');
    $html->set('$encounterSuccess', '');
    $html->set('$encounterFailure', '');

    $html->set('$checkAll', '');
    $html->set('$checkAny', '');
    $html->set('$success', '');
    $html->set('$failure', '');
}

//组装提交的信息为写入数据库的数据
function localSubmit() {
    global $db;
    global $user;

    $submit = array(
        'name' => $_POST['editName'] == '' ? $_POST['name'] : $_POST['editName'],
        'probabilityModifier' => intval($_POST['probabilityModifier']),
        'intensity' => intval($_POST['intensity']),
        'duration' => intval($_POST['duration']),
        'lastUpdate' => time()
    );

    $submitData = array(
        'checkAll' => array(),
        'checkAny' => array(),
        'success' => array(),
        'failure' => array()
    );

    //将 checkAll 的文本转换成数组
    $checkAll = \fLineToArray($_POST['checkAll']);
    if(!empty($checkAll)) {
        foreach ($checkAll as $k => $v) {
            $param = json_decode($v, true);
            $submitData['checkAll'][] = $param;
        }
    }

    //将 checkAny 的文本转换成数组
    $checkAny = \fLineToArray($_POST['checkAny']);
    if(!empty($checkAny)) {
        foreach ($checkAny as $k => $v) {
            $param = json_decode($v, true);
            $submitData['checkAny'][] = $param;
        }
    }

    //将 success 的文本转换成数组
    $success = \fLineToArray($_POST['success']);
    if(!empty($success)) {
        foreach ($success as $k => $v) {
            $param = json_decode($v, true);
            $submitData['success'][] = $param;
        }
    }

    //将 failure 的文本转换成数组
    $failure = \fLineToArray($_POST['failure']);
    if(!empty($failure)) {
        foreach ($failure as $k => $v) {
            $param = json_decode($v, true);
            $submitData['failure'][] = $param;
        }
    }

    $submit['data'] = json_encode($submitData);
    $submit['loot'] = localLoot($submitData);
    
    return $submit;
}

function localLoot(
    array $submitData
) {
    if(empty($submitData)) return array(); //为空直接返回
    $return = array();

    foreach($submitData as $entryGroup) { //遍历遭遇的每个事件组
        if(!empty($entryGroup)) {
            foreach($entryGroup as $k => $event) { //遍历事件组中的每个事件
                ################################################
                # 只对特定事件类型做解析，并提取出loot物品
                ################################################
                switch ($event[0]) {
                    case 'giveItemToRandomMember':
                        unset($event[0]);
                        foreach($event as $j => $v) { //遍历给物品的每个可能性数组
                            $return[] = $v['itemName'];
                        }
                        break;
                    
                    default: //默认不处理
                        break;
                }
                ################################################
                # 对事件类型的处理结束
                ################################################
            }
        }
    }

    return json_encode(array_merge(array_flip(array_flip($return)))); //返回去重后的序列数组
}
?>