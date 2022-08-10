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
use meshal\xAdventure as xAdventure;

$db = new \xDatabase;
$html = new \xHtml;
$user = new \xUser;

//只允许特定用户组访问
$user->challengeRole('admin', 'editor');

$error = 0; //错误计数器
if(isset($_GET['delScene'])) { //是一次通过get方法做场景删除的请求
    \fLog("Deleting scene #{$_GET['delScene']} from stage data");
    if( //检查提交数据有效性
        !$_GET['name']
        ||!$_GET['token']
    ) {
        \fLog("Error: missing params.");
        $html->redirect(
            'edit.php',
            'pageTitle.editor.adventure',
            'redirect.message.editor.adventure.failed'
        );
        \fDie();
    }

    $stageData = $db->getArr( //从stage表中取临时数据
        'adventure_stage',
        array(
            "`editorId` = '{$user->uid}'",
            "`stageToken` = '{$_GET['token']}'"
        ),
        null,1
    );

    if($stageData === false) { //stage数据出错
        \fLog("Error: stage data doesn't exist.");
        $html->redirect(
            'edit.php',
            'pageTitle.editor.adventure',
            'redirect.message.editor.adventure.failed'
        );
        \fDie();
    }

    $stageData = $stageData[0];
    $stageData['data'] = json_decode($stageData['data'], true);
    $stageData['encounterText'] = json_decode($stageData['encounterText'], true);

    // fPrint($stageData);

    if(empty($stageData['data']['scenes'])) {
        \fLog("Error: there's no scenes in the adventure stage data");
        $html->redirect(
            'edit.php',
            'pageTitle.editor.adventure',
            'redirect.message.editor.adventure.failed'
        );
        \fDie();
    }

    unset($stageData['data']['scenes'][$_GET['delScene']]);
    $stageData['data']['scenes'] = array_merge($stageData['data']['scenes']);

    unset($stageData['encounterText'][$_GET['delScene']]);
    $stageData['encounterText'] = array_merge($stageData['encounterText']);
    
    \fLog("Scene #{$_GET['delScene']} is removed from stage data");

    $db->update(
        'adventure_stage',
        array(
            'data' => json_encode($stageData['data']),
            'encounterText' => json_encode($stageData['encounterText'])
        ),
        array(
            "`editorId` = '{$user->uid}'",
            "`stageToken` = '{$_GET['token']}'"
        ),
        1
    );

    header("Location: edit.php?name={$_GET['name']}&token={$_GET['token']}");
    // fPrint($stageData);
    \fDie();
}

if($_POST['submit']) { //有提交行为
    \fLog('Received $_POST data:');
    \fLog(\fDump($_POST), 1, false);
    \fLog('Received $_GET data:');
    \fLog(\fDump($_GET), 1, false);
    if($_POST['editName'] !== '') { //是提交对已有冒险的编辑
        $data = \meshal\xAdventure::getData($_POST['editName']);

        //取 adventureName 对应的语言记录
        $adventureName = $db->getArr(
            'languages',
            array(
                "`name` = 'adventureName.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        //取 adventureDesc 对应的语言记录
        $adventureDesc = $db->getArr(
            'languages',
            array(
                "`name` = 'adventureDesc.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        //取 adventureProlog 对应的语言记录
        $adventureProlog = $db->getArr(
            'languages',
            array(
                "`name` = 'adventureProlog.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        //取 adventureEpilog 对应的语言记录
        $adventureEpilog = $db->getArr(
            'languages',
            array(
                "`name` = 'adventureEpilog.{$_POST['editName']}'",
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
                    'adventures',
                    $submit
                );
    
                //插入或更新 adventureName 对应的语言记录
                if($adventureName === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureName.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureName']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureName']),
                        ),
                        array(
                            "`name` = 'adventureName.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 adventureDesc 对应的语言记录
                if($adventureDesc === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureDesc.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureDesc']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureDesc']),
                        ),
                        array(
                            "`name` = 'adventureDesc.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 adventureProlog 对应的语言记录
                if($adventureProlog === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureProlog.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureProlog']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureProlog']),
                        ),
                        array(
                            "`name` = 'adventureProlog.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 adventureEpilog 对应的语言记录
                if($adventureEpilog === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureEpilog.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureEpilog']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                     $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureEpilog']),
                        ),
                        array(
                            "`name` = 'adventureEpilog.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }
            } else {
                //数据库中有记录，则更新数据
                $postEditName = $_POST['editName'];
                $check = $db->update(
                    'adventures',
                    $submit,
                    array(
                        "`name` = '{$postEditName}'"
                    ),
                    1
                );
    
                //插入或更新 adventureName 对应的语言记录
                if($adventureName === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureName.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureName']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureName']),
                        ),
                        array(
                            "`name` = 'adventureName.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 adventureDesc 对应的语言记录
                if($adventureDesc === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureDesc.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureDesc']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureDesc']),
                        ),
                        array(
                            "`name` = 'adventureDesc.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 adventureProlog 对应的语言记录
                if($adventureProlog === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureProlog.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureProlog']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureProlog']),
                        ),
                        array(
                            "`name` = 'adventureProlog.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }

                //插入或更新 adventureEpilog 对应的语言记录
                if($adventureEpilog === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "adventureEpilog.{$_POST['editName']}",
                            'content' => \fEncode($_POST['adventureEpilog']),
                            'lang' => $html->langCode
                        )
                    );
                } else {
                    $db->update(
                        'languages',
                        array(
                            'content' => \fEncode($_POST['adventureEpilog']),
                        ),
                        array(
                            "`name` = 'adventureEpilog.{$_POST['editName']}'",
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
                'editor/adventure/body.editor.html',
                'body'
            );

            localAssembler();
            $html->set('$nameReadonly', 'readonly');
            $html->output();
            \fDie();
        }
        

        //进入重定向页
        $html->set('$adventureName', $_POST['editName']);

        $html->redirect(
            'index.php',
            'pageTitle.editor.adventure',
            'redirect.message.editor.adventure.updated'
        );
        \fDie(); 
        
    } else { //是提交一个新冒险的数据
        //检查数据有效性
        if($_POST['name'] == '') {
            \fNotify('notify.editor.adventure.nameRequired', 'warn');
            $error++;
        }

        //检查是否有重名项
        if(xEncounter::getData($_POST['name']) !== false) {
            \fNotify('notify.editor.adventure.nameTaken', 'warn');
            $error++;
        }

        //检查token
        if(!$_POST['token'] || $_POST['token'] == '') {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.adventure',
                'redirect.message.editor.adventure.failed'
            );
            \fDie();
        }

        //根据token取adventure_stage数据
        $stage = $db->getArr(
            'adventure_stage',
            array(
                "`stageToken` = '{$_POST['token']}'"
            ),
            null,
            1
        );
        if($stage === false) {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.adventure',
                'redirect.message.editor.adventure.failed'
            );
            \fDie();
        }

        //组装提交的数据
        $submit = localSubmit();

        if($error == 0) {
            //插入数据
            $check = $db->insert(
                'adventures',
                $submit
            );

            //插入或更新 adventureName 对应的语言记录
            $adventureName = $db->getArr(
                'languages',
                array(
                    "`name` = 'adventureName.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($adventureName === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "adventureName.{$_POST['name']}",
                        'content' => \fEncode($_POST['adventureName']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['adventureName']),
                    ),
                    array(
                        "`name` = 'adventureName.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新 adventureDesc 对应的语言记录
            $adventureDesc = $db->getArr(
                'languages',
                array(
                    "`name` = 'adventureDesc.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($adventureDesc === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "adventureDesc.{$_POST['name']}",
                        'content' => \fEncode($_POST['adventureDesc']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['adventureDesc']),
                    ),
                    array(
                        "`name` = 'adventureDesc.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新 adventureProlog 对应的语言记录
            $adventureProlog = $db->getArr(
                'languages',
                array(
                    "`name` = 'adventureProlog.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($adventureProlog === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "adventureProlog.{$_POST['name']}",
                        'content' => \fEncode($_POST['adventureProlog']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['adventureProlog']),
                    ),
                    array(
                        "`name` = 'adventureProlog.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新 adventureEpilog 对应的语言记录
            $adventureEpilog = $db->getArr(
                'languages',
                array(
                    "`name` = 'adventureEpilog.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($adventureEpilog === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "adventureEpilog.{$_POST['name']}",
                        'content' => \fEncode($_POST['adventureEpilog']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['adventureEpilog']),
                    ),
                    array(
                        "`name` = 'adventureEpilog.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //进入重定向页
            $html->set('$adventureName', $_POST['name']);

            $html->redirect(
                'index.php',
                'pageTitle.editor.adventure',
                'redirect.message.editor.adventure.created'
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
                'editor/adventure/body.editor.html',
                'body'
            );

            localAssembler();
            $html->set('$nameReadonly', '');
            $html->output();
            \fDie();
        }
    }
}

else { //非提交行为
    if($_GET['name']) { //通过链接进入的已有冒险（GET方法从地址参数中获取）。读取已有冒险并准备编辑。
        //加载模板
        $html->loadTpl(
            'editor/adventure/body.editor.html',
            'body'
        );
        
        $html->set('$nameReadonly', 'readonly');
        $html->set('$editName', $_GET['name']);

        //检查token
        if($_GET['token']) { //如果有token，那么就取stage中的数据
            //获取stage的数据
            $data = $db->getArr(
                'adventure_stage',
                array(
                    "`name` = '{$_GET['name']}'",
                    "`editorId` = '{$user->uid}'",
                    "`stageToken` = '{$_GET['token']}'"
                ),
                null,
                1
            );
            if($data !== false) {
                $data = $data[0];
                $data['data'] = json_decode($data['data'], true);
                $data['loot'] = json_decode($data['loot'], true);
                $data['encounterText'] = json_decode($data['encounterText'], true);
                if(!empty($data['encounterText'])) {
                    $arr = array();
                    foreach ($data['encounterText'] as $k => $v) {
                        $arr[] = array(
                            'adventureEntrance' => \fDecode($v['adventureEntrance']),
                            'encounterApproach' => \fDecode($v['encounterApproach']),
                            'encounterProcess' => \fDecode($v['encounterProcess']),
                            'encounterSuccess' => \fDecode($v['encounterSuccess']),
                            'encounterFailure' => \fDecode($v['encounterFailure'])
                        );
                    }
                    $data['encounterText'] = $arr;
                }


                //把加载到的数据填入前端
                $html->set('$token', $_GET['token']);
                $html->set('$name', $data['name']);
                $html->set('$duration', $data['duration']);
                $html->set('$apCost', $data['apCost']);
                $html->set('$teamMin', $data['teamMin']);
                $html->set('$teamMax', $data['teamMax']);
                $html->set('$strengthMin', $data['strengthMin']);
                $html->set('$strengthMax', $data['strengthMax']);
                $html->set('$probabilityModifier', $data['probability']);
                $html->set('$adventureName', \fDecode($data['adventureName']));
                $html->set('$adventureDesc', \fDecode($data['adventureDesc']));
                $html->set('$adventureProlog', \fDecode($data['adventureProlog']));
                $html->set('$adventureEpilog', \fDecode($data['adventureEpilog']));
    
                $html->set('$nameReadonly', 'readonly');
    
                # 遍历已有遭遇，组装遭遇列表
                $comp = array();
                if(!empty($data['data']['scenes'])) {
                    foreach($data['data']['scenes'] as $k => $scene) {
                        $comp[] = array(
                            '--adventureName' => $data['name'],
                            '--encounterName' => $scene['encounter'],
                            '--token' => $_GET['token'],
                            '--sceneId' => $k,
                            '--adventureEntrance' => $data['encounterText'][$k]['adventureEntrance'],
                            '--encounterApproach' => $data['encounterText'][$k]['encounterApproach'],
                            '--encounterProcess' => $data['encounterText'][$k]['encounterProcess'],
                            '--encounterSuccess' => $data['encounterText'][$k]['encounterSuccess'],
                            '--encounterFailure' => $data['encounterText'][$k]['encounterFailure'],
                            '--nextSuccess' => json_encode($scene['next']['success']),
                            '--nextFailure' => json_encode($scene['next']['failure']),
                            '--nextDefault' => json_encode($scene['next']['default']),
                        );
                    }
                }
                $html->set(
                    '$encounters',
                    $html->duplicate(
                        'editor/adventure/dup.encounter.html',
                        $comp
                    )
                );

                ### 拼装结束事件
    
                $html->output();
                \fDie();
            } else { //stage数据获取不到则报错
                \fLog("Error: cannot fetch stage data");
                $html->redirect(
                    '',
                    'pageTitle.editor.adventure',
                    'redirect.message.editor.adventure.failed'
                );
                \fDie();
            }
        } else { //如果没有token，就读取模板数据
            $data = \meshal\xAdventure::getData($_GET['name']);
            if($data !== false) {
                //对旧的adventure_stage数据（该用户未保存的编辑）做清理
                $db->delete(
                    'adventure_stage',
                    array(
                        "`editorId` = '{$user->uid}'",
                    )
                );
    
                //遍历所有冒险并取它们的自定义语言
                $encounterText = array();
                if(!empty($data['data']['scenes'])) {
                    foreach($data['data']['scenes'] as $k => $v) {
                        $encounterText[] = array(
                            'adventureEntrance' => \fEncode($html->dbLang("adventureEntrance.{$data['name']}.{$v['encounter']}")),
                            'encounterApproach' => \fEncode($html->dbLang("encounterApproach.{$data['name']}.{$v['encounter']}")),
                            'encounterProcess' => \fEncode($html->dbLang("encounterProcess.{$data['name']}.{$v['encounter']}")),
                            'encounterSuccess' => \fEncode($html->dbLang("encounterSuccess.{$data['name']}.{$v['encounter']}")),
                            'encounterFailure' => \fEncode($html->dbLang("encounterFailure.{$data['name']}.{$v['encounter']}"))
                        );
                    }
                }
                $data['encounterText'] = json_encode($encounterText);
    
                //将加载到的数据存入adventure_stage用于编辑
                $token = \fGenGuid();
                $db->insert(
                    'adventure_stage',
                    array(
                        'editorId' => $user->uid,
                        'stageToken' => $token,
                        'name' => $data['name'],
                        'coverImage' => \fEncode($data['coverImage']),
                        'duration' => $data['duration'],
                        'apCost' => $data['apCost'],
                        'teamMin' => $data['teamMin'],
                        'teamMax' => $data['teamMax'],
                        'strengthMin' => $data['strengthMin'],
                        'strengthMax' => $data['strengthMax'],
                        'data' => json_encode($data['data']),
                        'loot' => json_encode($data['loot']),
                        'probabilityModifier' => $data['probability'],
                        'adventureName' => \fEncode($html->dbLang("adventureName.{$_GET['name']}")),
                        'adventureDesc' => \fEncode($html->dbLang("adventureDesc.{$_GET['name']}")),
                        'adventureProlog' => \fEncode($html->dbLang("adventureProlog.{$_GET['name']}")),
                        'adventureEpilog' => \fEncode($html->dbLang("adventureEpilog.{$_GET['name']}")),
                        'encounterText' => $data['encounterText']
                    )
                );
    
                //把加载到的数据填入前端
                $html->set('$token', $token);
                $html->set('$name', $data['name']);
                $html->set('$duration', $data['duration']);
                $html->set('$apCost', $data['apCost']);
                $html->set('$teamMin', $data['teamMin']);
                $html->set('$teamMax', $data['teamMax']);
                $html->set('$strengthMin', $data['strengthMin']);
                $html->set('$strengthMax', $data['strengthMax']);
                $html->set('$probabilityModifier', $data['probability']);
                $html->set('$adventureName', $html->dbLang("adventureName.{$_GET['name']}"));
                $html->set('$adventureDesc', $html->dbLang("adventureDesc.{$_GET['name']}"));
                $html->set('$adventureProlog', $html->dbLang("adventureProlog.{$_GET['name']}"));
                $html->set('$adventureEpilog', $html->dbLang("adventureEpilog.{$_GET['name']}"));
    
                $html->set('$nameReadonly', 'readonly');
    
                # 遍历已有遭遇，组装遭遇列表
                $comp = array();
                if(!empty($data['data']['scenes'])) {
                    foreach($data['data']['scenes'] as $k => $scene) {
                        $comp[] = array(
                            '--adventureName' => $data['name'],
                            '--encounterName' => $scene['encounter'],
                            '--token' => $token,
                            '--sceneId' => $k,
                            '--adventureEntrance' => \fDecode($encounterText[$k]['adventureEntrance']),
                            '--encounterApproach' => \fDecode($encounterText[$k]['encounterApproach']),
                            '--encounterProcess' => \fDecode($encounterText[$k]['encounterProcess']),
                            '--encounterSuccess' => \fDecode($encounterText[$k]['encounterSuccess']),
                            '--encounterFailure' => \fDecode($encounterText[$k]['encounterFailure']),
                            '--nextSuccess' => json_encode($scene['next']['success']),
                            '--nextFailure' => json_encode($scene['next']['failure']),
                            '--nextDefault' => json_encode($scene['next']['default']),
                        );
                    }
                }
                $html->set(
                    '$encounters',
                    $html->duplicate(
                        'editor/adventure/dup.encounter.html',
                        $comp
                    )
                );

                ### 处理结束事件
    
                $html->output();
                \fDie();
    
            } else { //没有查到这个冒险，那么从白板开始
                //清除占位变量
                localPreset();
                $html->output();
                \fDie();
            }
        }

        

        
    } else { //一个白板物品的创建页面
        $token = \fGenGuid();

        //删掉用户之前在adventure_stage中的信息
        $db->delete(
            'adventure_stage',
            array(
                "`editorId` = '{$user->uid}'"
            ),
            1
        );

        //为这次的白板编辑新建一个adventure_stage记录
        $db->insert(
            'adventure_stage',
            array(
                'editorId' => $user->uid,
                'stageToken' => $token
            )
        );

        $html->loadTpl(
            'editor/adventure/body.editor.html',
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
    $html->set('$duration', 0);
    $html->set('$apCost', 1);
    $html->set('$teamMin', 1);
    $html->set('$teamMax', 1);
    $html->set('$strengthMin', 0);
    $html->set('$strengthMax', 0);
    $html->set('$probabilityModifier', 0);

    $html->set('$adventureName', '');
    $html->set('$adventureDesc', '');
    $html->set('$adventureProlog', '');
    $html->set('$adventureEpilog', '');
}

//组装提交的信息为写入数据库的数据
function localSubmit() {
    global $db;
    global $user;

    $submit = array(
        'name' => $_POST['editName'] == '' ? $_POST['name'] : $_POST['editName'],
        'probabilityModifier' => intval($_POST['probabilityModifier']),
        'duration' => intval($_POST['duration']),
        'apCost' => intval($_POST['apCost']),
        'strengthMin' => \fPost('strengthMin', null) == 0 ? null : \fPost('strengthMin', null),
        'strengthMax' => \fPost('strengthMax', null) == 0 ? null : \fPost('strengthMax', null),
        'teamMin' => \fPost('teamMin', null) == 0 ? null : \fPost('teamMin', null),
        'teamMax' => \fPost('teamMax', null) == 0 ? null : \fPost('teamMax', null),
        'lastUpdate' => time(),
    );

    $submitType = \fLineToArray($_POST['type']);
    $submit['type'] = json_encode($submitType);


    $submitData = array();


    //遍历所有encounter数据并组装成数组
    if(!$_POST['encounter']) {
        foreach($_POST['encounter'] as $sceneId => $sceneData) {
            $submit['data']['scenes'][$sceneId] = array(
                'encounter' => $sceneData['encounterName'],
                'next' => array(
                    'success' => json_decode($sceneData['nextSuccess']),
                    'failure' => json_decode($sceneData['nextFailure']),
                    'default' => json_decode($sceneData['nextDefault'])
                )
            );
        }
    }

    //遍历所有encounter的loot数据并组装成数组
    
    $submit['data'] = json_encode($submitData);
    $submit['loot'] = localLoot($submitData);
    
    return $submit;
}

function localLoot(
    array $submitData
) {
    if(empty($submitData)) return array(); //为空直接返回
    $return = array();

    foreach($submitData as $entryGroup) { //遍历冒险的每个事件组
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