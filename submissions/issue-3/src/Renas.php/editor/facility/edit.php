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
    if(
        $_POST['editName'] !== '' 
        && $_POST['editLevel'] !== ''
    ) { //是提交对已有设施的编辑
        $query = $db->getArr(
            'facilities',
            array(
                "`name` = '{$_POST['editName']}'",
                "`level` = '{$_POST['editLevel']}'"
            ),
            null,
            1
        );
        if($query !== false) {
            $facility = $query[0];
            $facility['data'] = json_decode($query[0]['data']);
        }

        $desc = $db->getArr(
            'languages',
            array(
                "`name` = 'facilityDesc.{$_POST['editName']}.{$_POST['editLevel']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        $trans = $db->getArr(
            'languages',
            array(
                "`name` = 'facilityName.{$_POST['editName']}.{$_POST['editLevel']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        $submit = localSubmit();

        if($query === false) {
            //假设数据库中不存在，那么就直接创建
            $check = $db->insert(
                'facilities',
                $submit
            );

            //插入或更新translation对应的语言记录
            if($trans === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "facilityName.{$_POST['editName']}.{$_POST['editLevel']}",
                        'content' => \fEncode($_POST['translation']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['translation']),
                    ),
                    array(
                        "`name` = 'facilityName.{$_POST['editName']}.{$_POST['editLevel']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新description对应的语言记录
            if($desc === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "itemDesc.{$_POST['editName']}",
                        'content' => \fEncode($_POST['description']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['description']),
                    ),
                    array(
                        "`name` = 'facilityDesc.{$_POST['editName']}.{$_POST['editLevel']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }
        } else {
            //数据库中有记录，则更新数据
            $postEditName = $_POST['editName'];
            $postEditLevel = $_POST['editLevel'];
            $check = $db->update(
                'facilities',
                $submit,
                array(
                    "`name` = '{$postEditName}'",
                    "`level` = '{$postEditLevel}'"
                ),
                1
            );

            //插入或更新translation
            if($trans === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "facilityName.{$_POST['editName']}.{$_POST['editLevel']}",
                        'content' => \fEncode($_POST['translation']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['translation']),
                    ),
                    array(
                        "`name` = 'facilityName.{$_POST['editName']}.{$_POST['editLevel']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新descrpition
            if($desc === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "facilityDesc.{$_POST['editName']}.{$_POST['editLevel']}",
                        'content' => \fEncode($_POST['description']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['description']),
                    ),
                    array(
                        "`name` = 'facilityDesc.{$_POST['editName']}.{$_POST['editLevel']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }
        }        

        //进入重定向页
        $html->set('$facilityName', $_POST['editName']);
        $html->set('$facilityLevel', $_POST['editLevel']);

        $html->redirect(
            'index.php',
            'pageTitle.editor.facility',
            'redirect.message.editor.facility.updated'
        );
        \fDie(); 
        
    } else { //是提交一个新设施的数据
        //检查数据有效性
        if($_POST['name'] == '') {
            \fNotify('notify.editor.facility.nameRequired', 'warn');
            $error++;
        }

        if($_POST['level'] == '' || $_POST['level'] < 1) {
            \fNotify('notify.editor.facility.levelInvalid', 'warn');
            $error++;
        }

        $query = $db->getArr(
            'facilities',
            array(
                "`name` = {$_POST['name']}",
                "`level` = {$_POST['level']}"
            ), null, 1
        );

        //检查是否有重名项
        if($query !== false) {
            \fNotify('notify.editor.facility.entryExists', 'warn');
            $error++;
        }

        //检查token
        if(!$_POST['token'] || $_POST['token'] == '') {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.facility',
                'redirect.message.editor.facility.failed'
            );
            \fDie();
        }

        //根据token取facility_stage数据
        $stage = $db->getArr(
            'facility_stage',
            array(
                "`stageToken` = '{$_POST['token']}'"
            ),
            null,
            1
        );
        if($stage === false) {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.facility',
                'redirect.message.editor.facility.failed'
            );
            \fDie();
        }

        //组装提交的数据
        $submit = localSubmit();

        if($error == 0) {

            //插入数据
            $check = $db->insert(
                'facilities',
                $submit
            );

            //插入或更新translation对应的语言记录
            $trans = $db->getArr(
                'languages',
                array(
                    "`name` = 'facilityName.{$_POST['name']}.{$_POST['level']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($trans === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "facilityName.{$_POST['name']}.{$_POST['level']}",
                        'content' => \fEncode($_POST['translation']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['translation']),
                    ),
                    array(
                        "`name` = 'facilityName.{$_POST['name']}.{$_POST['level']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新description对应的语言记录
            $desc = $db->getArr(
                'languages',
                array(
                    "`name` = 'facilityDesc.{$_POST['name']}.{$_POST['level']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($desc === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "facilityDesc.{$_POST['name']}.{$_POST['level']}",
                        'content' => \fEncode($_POST['description']),
                        'lang' => $html->langCode
                    )
                );
            } else {
                $db->update(
                    'languages',
                    array(
                        'content' => \fEncode($_POST['description']),
                    ),
                    array(
                        "`name` = 'facilityDesc.{$_POST['name']}.{$_POST['level']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //进入重定向页
            $html->set('$facilityName', $_POST['name']);
            $html->set('$facilityLevel', $_POST['level']);

            $html->redirect(
                'index.php',
                'pageTitle.editor.facility',
                'redirect.message.editor.facility.created'
            );
            \fDie(); 
            
        } else {
            //有错误提示，则不跳转页面，而是在当前页面显示错误提示
            $html->set('$editName', '');
            $html->set('$editLevel', '');

            //把已提交的数据重组回来
            foreach ($_POST as $k => $v) {
                $html->set("\${$k}", isset($v) ? $v : '');
            }

            //加载页面模板
            $html->loadTpl(
                'editor/facility/body.editor.html',
                'body'
            );

            $html->set('$nameReadonly', '');
            $html->set('$levelReadonly', '');
            $html->output();
            \fDie();
        }
    }
} else { //非提交行为
    if($_GET['name'] && $_GET['level']) { //通过链接进入的已有设施（GET方法从地址参数中获取）。读取已有设施并准备编辑。
        //加载模板
        $html->loadTpl(
            'editor/facility/body.editor.html',
            'body'
        );
        
        $query = $db->getArr(
            'facilities',
            array(
                "`name` = '{$_GET['name']}'",
                "`level` = '{$_GET['level']}'"
            ),
            null,
            1
        );

        if($query === false) {//没有查到这个物品，那么从白板开始
            //清除占位变量
            localPreset();
            $html->output();
            \fDie();
        } else {
            $data = \meshal\xFacility::getData($_GET['name'], $_GET['level']);
            //获取stage的数据
            $stageData = $db->getArr(
                'facility_stage',
                array(
                    "`name` = '{$_GET['name']}'",
                    "`level` = '{$_GET['level']}'",
                    "`editorId` = '{$user->uid}'"
                ),
                null,
                1
            );
            if($stageData !== false) {
                $stageImage = \fDecode($stageData[0]['image']);
                //删除stage中的image
                if(
                    (!is_null($stageImage) || $stageImage == '')
                    && file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['facilityImage'].$stageImage)
                    && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['facilityImage'].$stageImage)
                    && $stageImage !== $data['image'] //只有stageImage不等于实际的image时才做删除
                ) {
                    unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['facilityImage'].$stageImage);
                }
            }
            //对旧的item_stage数据（该用户未保存的编辑）做清理
            $db->delete(
                'facility_stage',
                array(
                    "`editorId` = '{$user->uid}'",
                )
            );

            //将加载到的数据存入facility_stage用于编辑
            $token = \fGenGuid();
            $db->insert(
                'facility_stage',
                array(
                    'editorId' => $user->uid,
                    'stageToken' => $token,
                    'name' => $_GET['name'],
                    'level' => $_GET['level'],
                    'image' => \fEncode($data['image']),
                    'translation' => \fEncode($html->dbLang("facilityName.{$_GET['name']}.{$_GET['level']}")),
                    'description' => \fEncode($html->dbLang("facilityDesc.{$_GET['name']}.{$_GET['level']}")),
                    'data' => $data['data']
                )
            );

            //把加载到的数据填入前端
            $html->set('$token', $token);
            $html->set('$name', $data['name']);
            $html->set('$level', $data['level']);
            $html->set('$translation', $html->dbLang("facilityName.{$data['name']}.{$data['level']}"));
            $html->set('$description', $html->dbLang("facilityDesc.{$data['name']}.{$data['level']}"));

            //一些占位符初始化
            $html->set('$buildCheckAll', '');
            $html->set('$buildCheckAny', '');
            $html->set('$buildMaterial', '');
            $html->set('$facilityEfx', '');

            //建造前提
            if(empty($data['data']['build']['checkAll'])) {
                $html->set('$buildCheckAll', '');
            } else {
                $arr = array();
                foreach($data['data']['build']['checkAll'] as $k => $check) {
                    $arr[] = implode(',', $check); //把前提数组拼装回字符串配置格式
                }
                $html->set('$buildCheckAll', implode(PHP_EOL, $arr));
            }

            if(empty($data['data']['build']['checkAny'])) {
                $html->set('$buildCheckAny', '');
            } else {
                $arr = array();
                foreach($data['data']['build']['checkAny'] as $k => $check) {
                    $arr[] = implode(',', $check); //把前提数组拼装回字符串配置格式
                }
                $html->set('$buildCheckAny', implode(PHP_EOL, $arr));
            }

            if(empty($data['data']['build']['material'])) {
                $html->set('$buildMaterial', '');
            } else {
                $arr = array();
                foreach($data['data']['build']['material'] as $k => $check) {
                    $arr[] = implode(',', $check); //把材料数组拼装回字符串配置格式
                }
                $html->set('$buildMaterial', implode(PHP_EOL, $arr));
            }

            //设施效果
            if(empty($data['data']['efx'])) {
                $html->set('$facilityEfx', '');
            } else {
                $arr = array();
                foreach($data['data']['efx'] as $k => $efx) {
                    $arr[] = implode(',', $efx); //把效果数组拼装回字符串配置格式
                }
                $html->set('$facilityEfx', implode(PHP_EOL, $arr));
            }

            $html->set('$buildTime', $data['data']['build']['time']);
            $html->set('$buildChar', $data['data']['build']['char']);
            $html->set('$buildAP', $data['data']['build']['ap']);
            $html->set('$nameReadonly', 'readonly');
            $html->set('$levelReadonly', 'readonly');
            $html->set('$editName', $_GET['name']);
            $html->set('$editLevel', $_GET['level']);
            $html->output();
            \fDie();
        }
    } else { //一个白板设施的创建页面
        $token = \fGenGuid();

        //删掉用户之前在facility_stage中的信息
        $db->delete(
            'facility_stage',
            array(
                "`editorId` = '{$user->uid}'"
            ),
            1
        );

        //为这次的白板编辑新建一个facility_stage记录
        $db->insert(
            'facility_stage',
            array(
                'editorId' => $user->uid,
                'stageToken' => $token
            )
        );

        $html->loadTpl(
            'editor/facility/body.editor.html',
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
function localAssembler(
) {
    global $html;
}

//预设变量
function localPreset() {
    global $html;

    $html->set('$token', '');
    $html->set('$nameReadonly', '');
    $html->set('$editName', '');
    $html->set('$editLevel', '');
    $html->set('$name', '');
    $html->set('$level', 1);
    $html->set('$translation', '');
    $html->set('$description', '');
    $html->set('$buildTime', 0);
    $html->set('$buildChar', 0);
    $html->set('$buildAP', 0);

    $html->set('$buildCheckAll', '');
    $html->set('$buildCheckAny', '');
    $html->set('$buildMaterial', '');
    $html->set('$facilityEfx', '');
}

//组装提交的信息为写入数据库的数据
function localSubmit() {
    global $db;
    global $user;

    $submit = array(
        'name' => $_POST['editName'] == '' ? $_POST['name'] : $_POST['editName'],
        'level' => $_POST['editLevel'] == '' ? $_POST['level'] : $_POST['editLevel'],
        'lastUpdate' => time()
    );

    // if(empty($_FILES['image'])) {
    //     $submit['image'] = null;
    // } else {
    //     $upload = new \xUpload($_FILES['image']);
    //     if($upload->uploaded) {
    //         $upload->file_new_name_body = \fGenGuid();
    //         $upload->image_resize = true;
    //         $upload->image_x = $GLOBALS['meshal']['portrait']['width'];
    //         $upload->image_y = $GLOBALS['meshal']['portrait']['height'];
    //         $upload->image_ratio_crop = true;
    
    //         $upload->process(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['facilityImage']);
    //         $image = $upload->file_dst_name;
    //     }
    //     $submit['image'] = $image;
    // }

    $submitData = array();
    $submitData['build']['time'] = \fPost('buildTime', 0);
    $submitData['build']['char'] = \fPost('buildChar', 0);
    $submitData['build']['ap'] = \fPost('buildAP', 0);

    //处理使用前提
    $buildCheckAll = \fLineToArray(\fPost('buildCheckAll',''));
    $submitData['build']['checkAll'] = array();
    if(!empty($buildCheckAll)) {
        foreach ($buildCheckAll as $k => $check) {
            $submitData['build']['checkAll'][] = explode(',', $check);
        }
    }

    $buildCheckAny = \fLineToArray(\fPost('buildCheckAny',''));
    $submitData['build']['checkAny'] = array();
    if(!empty($buildCheckAny)) {
        foreach ($buildCheckAny as $k => $check) {
            $submitData['build']['checkAny'][] = explode(',', $check);
        }
    }

    //处理材料消耗
    $buildMaterial = \fLineToArray(\fPost('buildMaterial',''));
    $submitData['build']['material'] = array();
    if(!empty($buildMaterial)) {
        foreach ($buildMaterial as $k => $material) {
            $submitData['build']['material'][] = explode(',', $material);
        }
    }

    //处理建筑效果
    $facilityEfx = \fLineToArray(\fPost('facilityEfx',''));
    $submitData['efx'] = array();
    if(!empty($facilityEfx)) {
        foreach ($facilityEfx as $k => $efx) {
            $submitData['efx'][] = explode(',', $efx);
        }
    }
    
    //从facility_stage中取出临时编辑并整合
    $stage = $db->getArr(
        'facility_stage',
        array(
            "`stageToken` = '{$_POST['token']}'"
        ),
        null,
        1
    );
    $submit['image'] = $stage[0]['image'];
    $submit['data'] = json_encode($submitData);

    return $submit;
}
?>