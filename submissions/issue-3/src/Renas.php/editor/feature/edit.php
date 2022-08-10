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

use \meshal\xFeature as xFeature;
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
    if($_POST['editType'] !== '' && $_POST['editName'] !== '') { //是提交对已有特征的编辑
        $data = \meshal\xFeature::getData($_POST['editType'], $_POST['editName']);
        $desc = $db->getArr(
            'languages',
            array(
                "`name` = 'featureDesc.{$_POST['editType']}.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );
        $trans = $db->getArr(
            'languages',
            array(
                "`name` = 'featureName.{$_POST['editType']}.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );
        $weight = $db->getArr( //获取该特征类型的权重总和数据
            'feature_index',
            array(
                "`name` = '{$_POST['editType']}'"
            ),
            null,
            1
        );
        if($weight === false) { //如果没有权重总和记录，创建一个
            $db->insert(
                'feature_index',
                array(
                    'name' => "{$_POST['editType']}"
                )
            );
            $weight = $db->getArr( //重新获取该特征类型的权重总和数据
                'feature_index',
                array(
                    "`name` = '{$_POST['editType']}'"
                ),
                null,
                1
            );
        }

        $submit = localSubmit();

        if($data === false) {
            //假设数据库中不存在，那么就直接创建
            $check = $db->insert(
                'features',
                $submit
            );

            //增加总权重
            $db->update(
                'feature_index',
                array(
                    'probabilityModifier' => $weight[0]['probabilityModifier'] + $submit['probabilityModifier'],
                    'strength' => $weight[0]['strength'] + abs($submit['strength']),
                    'count' => $weight[0]['count'] + 1
                ),
                array(
                    "`name` = '{$_POST['editType']}'"
                ),
                1
            );

            //插入或更新translation对应的语言记录
            if($trans === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "featureName.{$_POST['editType']}.{$_POST['editName']}",
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
                        "`name` = 'featureName.{$_POST['editType']}.{$_POST['editName']}'",
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
                        'name' => "featureDesc.{$_POST['editType']}.{$_POST['editName']}",
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
                        "`name` = 'featureDesc.{$_POST['editType']}.{$_POST['editName']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }
            
        } else {
            //数据库中有记录，则更新数据
            $postEditName = $_POST['editName'];
            $check = $db->update(
                'features',
                $submit,
                array(
                    "`type` = '{$_POST['editType']}'",
                    "`name` = '{$postEditName}'"
                ),
                1
            );

            if($check !== false) {
                $db->update( //更新总权重
                    'feature_index',
                    array(
                        'probabilityModifier' => $weight[0]['probabilityModifier']
                            - $data['probability']['modifier']
                            + $submit['probabilityModifier'],
                        'strength' => $weight[0]['strength']
                            - abs($data['strength'])
                            + abs($submit['strength'])
                    ),
                    array(
                        "`name` = '{$_POST['editType']}'"
                    ),
                    1
                );
            }

            //插入或更新translation
            if($trans === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "featureName.{$_POST['editType']}.{$_POST['editName']}",
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
                        "`name` = 'featureName.{$_POST['editType']}.{$_POST['editName']}'",
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
                        'name' => "featureDesc.{$_POST['editType']}.{$_POST['editName']}",
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
                        "`name` = 'featureDesc.{$_POST['editType']}.{$_POST['editName']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }
        }

        //进入重定向页
        $html->set('$featureType', $_POST['editType']);
        $html->set('$featureName', $_POST['editName']);

        $html->redirect(
            'index.php',
            'pageTitle.editor.feature',
            'redirect.message.editor.feature.updated'
        );
        \fDie(); 
        
    } else { //是提交一个新特征的数据
        //检查数据有效性
        if($_POST['name'] == '') {
            \fNotify('notify.editor.feature.nameRequired', 'warn');
            $error++;
        }

        //检查是否有重名项
        if(xFeature::getData($_POST['type'], $_POST['name']) !== false) {
            \fNotify('notify.editor.feature.nameTaken', 'warn');
            $error++;
        }

        //检查token
        if(!$_POST['token'] || $_POST['token'] == '') {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.feature',
                'redirect.message.editor.feature.failed'
            );
            \fDie();
        }

        //根据token取feature_stage数据
        $stage = $db->getArr(
            'feature_stage',
            array(
                "`stageToken` = '{$_POST['token']}'"
            ),
            null,
            1
        );
        if($stage === false) {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.feature',
                'redirect.message.editor.feature.failed'
            );
            \fDie();
        }

        //组装提交的数据
        $submit = localSubmit();

        if($error == 0) {
            $weight = $db->getArr( //获取该特征类型的权重总和数据
                'feature_index',
                array(
                    "`name` = '{$_POST['type']}'"
                ),
                null,
                1
            );
            if($weight === false) { //如果没有权重总和记录，创建一个
                $db->insert(
                    'feature_index',
                    array(
                        'name' => "{$_POST['type']}"
                    )
                );
                $weight = $db->getArr( //重新获取该特征类型的权重总和数据
                    'feature_index',
                    array(
                        "`name` = '{$_POST['type']}'"
                    ),
                    null,
                    1
                );
            }

            //插入数据
            $check = $db->insert(
                'features',
                $submit
            );

            //更新总权重
            if($check !== false) {
                $db->update(
                    'feature_index',
                    array(
                        'probabilityModifier' => $weight[0]['probabilityModifier'] 
                            + $submit['probabilityModifier'],
                        'strength' => $weight[0]['strength']
                            + abs($submit['strength']), //这里做绝对值处理
                        'count' => $weight[0]['count'] + 1
                    ),
                    array(
                        "`name` = '{$_POST['type']}'"
                    ),
                    1
                );
            }

            //插入或更新translation对应的语言记录
            $trans = $db->getArr(
                'languages',
                array(
                    "`name` = 'featureName.{$_POST['type']}.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($trans === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "featureName.{$_POST['type']}.{$_POST['name']}",
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
                        "`name` = 'featureName.{$_POST['type']}.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新description对应的语言记录
            $desc = $db->getArr(
                'languages',
                array(
                    "`name` = 'featureDesc.{$_POST['type']}.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($desc === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "featureDesc.{$_POST['type']}.{$_POST['name']}",
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
                        "`name` = 'featureDesc.{$_POST['type']}.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }


            //进入重定向页
            $html->set('$featureType', $_POST['type']);
            $html->set('$featureName', $_POST['name']);

            $html->redirect(
                'index.php',
                'pageTitle.editor.feature',
                'redirect.message.editor.feature.created'
            );
            \fDie(); 
            
        } else {
            //有错误提示，则不跳转页面，而是在当前页面显示错误提示
            $html->set('$editType', '');
            $html->set('$editName', '');

            //把已提交的数据重组回来
            foreach ($_POST as $k => $v) {
                $html->set("\${$k}", isset($v) ? $v : '');
            }
            $html->set('$ipCheck', $_POST['ipCheck'] ? 'checked' : '');
            $html->set('$ieCheck', $_POST['ieCheck'] ? 'checked' : '');
            $html->set('$ioCheck', $_POST['ioCheck'] ? 'checked' : '');

            //加载页面模板
            $html->loadTpl(
                'editor/feature/body.editor.html',
                'body'
            );

            localAssembler($_POST['type']);
            $html->set('$nameReadonly', '');
            $html->output();
            \fDie();
        }
    }
} else { //非提交行为
    if($_GET['type'] && $_GET['name']) { //通过链接进入的已有特征（GET方法从地址参数中获取）。读取已有特征并准备编辑。
        //加载模板
        $html->loadTpl(
            'editor/feature/body.editor.html',
            'body'
        );

        localAssembler($_GET['type']);
        $html->set('$typeDisabled', 'disabled');
        $html->set('$nameReadonly', 'readonly');

        $html->set('$editType', $_GET['type']);
        $html->set('$editName', $_GET['name']);

        $data = xFeature::getData($_GET['type'], $_GET['name']);

        if($data !== false) {
            //对旧的feature_stage数据（该用户未保存的编辑）做清理
            $db->delete(
                'feature_stage',
                array(
                    "`editorId` = '{$user->uid}'",
                )
            );

            //将加载到的数据存入feature_stage用于编辑
            $token = \fGenGuid();
            $db->insert(
                'feature_stage',
                array(
                    'editorId' => $user->uid,
                    'stageToken' => $token,
                    'type' => $_GET['type'],
                    'name' => $_GET['name'],
                    'availableFeature' => json_encode($data['data']['availableFeature']),
                    'addFeature' => json_encode($data['data']['addFeature']),
                    'addAbility' => json_encode($data['data']['addAbility']),
                    'translation' => \fEncode($html->dbLang("featureName.{$_GET['type']}.{$_GET['name']}")),
                    'description' => \fEncode($html->dbLang("featureDesc.{$_GET['type']}.{$_GET['name']}")),
                    'strength' => $data['strength'],
                    'probabilityModifier' => $data['probability']['modifier']
                )
            );

            //把加载到的数据填入前端
            $html->set('$token', $token);
            $html->set('$name', $data['name']);
            $html->set('$translation', $html->dbLang("featureName.{$data['type']}.{$data['name']}"));
            $html->set('$description', $html->dbLang("featureDesc.{$data['type']}.{$data['name']}"));
            $html->set('$probabilityModifier', $data['probability']['modifier']);
            $html->set('$mModifier', $data['data']['modifier']['m']);
            $html->set('$aModifier', $data['data']['modifier']['a']);
            $html->set('$sModifier', $data['data']['modifier']['s']);
            $html->set('$tModifier', $data['data']['modifier']['t']);
            $html->set('$eModifier', $data['data']['modifier']['e']);
            $html->set('$rModifier', $data['data']['modifier']['r']);
            $html->set('$apModifier', $data['data']['modifier']['ap']);
            $html->set('$ccModifier', $data['data']['modifier']['cc']);
            $html->set('$prModifier', $data['data']['modifier']['pr']);
            $html->set('$msModifier', $data['data']['modifier']['ms']);
            $html->set('$mMultiplier', $data['data']['multiplier']['m']);
            $html->set('$aMultiplier', $data['data']['multiplier']['a']);
            $html->set('$sMultiplier', $data['data']['multiplier']['s']);
            $html->set('$tMultiplier', $data['data']['multiplier']['t']);
            $html->set('$eMultiplier', $data['data']['multiplier']['e']);
            $html->set('$rMultiplier', $data['data']['multiplier']['r']);
            $html->set('$apMultiplier', $data['data']['multiplier']['ap']);
            $html->set('$ccMultiplier', $data['data']['multiplier']['cc']);
            $html->set('$prMultiplier', $data['data']['multiplier']['pr']);
            $html->set('$msMultiplier', $data['data']['multiplier']['ms']);
            if($data['data']['modifier']['ip'] > 0) $html->set('$ipCheck', 'checked');
            if($data['data']['modifier']['ie'] > 0) $html->set('$ieCheck', 'checked');
            if($data['data']['modifier']['io'] > 0) $html->set('$ioCheck', 'checked');
            if($data['type'] == 'size') {
                $html->set('$sizeLevel', $data['data']['sizeLevel']);
            } else {
                $html->set('$sizeLevel', '');
            }

            foreach ($GLOBALS['meshal']['equipmentContainer'] as $containerCode => $settings) {
                $html->set('$slots.'.$containerCode, $data['data']['equipmentSlots'][$containerCode]);
            }
            
            
            // $html->set(
            //     '$addAbility', 
            //     empty($data['data']['addAbility']) 
            //         ? '' 
            //         : implode(PHP_EOL, $data['data']['addAbility'])
            // );

            $html->set('$nameReadonly', 'readonly');
            $html->set('$typeDisabled', 'disabled');
            $html->output();
            \fDie();

        } else { //没有查到这个特征，那么从白板开始
            //清除占位变量
            localPreset();
            $html->output();
            \fDie();
        }
    } else { //一个白板特征的创建页面
        $token = \fGenGuid();

        //删掉用户之前在feature_stage中的信息
        $db->delete(
            'feature_stage',
            array(
                "`editorId` = '{$user->uid}'"
            ),
            1
        );

        //为这次的白板编辑新建一个feature_stage记录
        $db->insert(
            'feature_stage',
            array(
                'editorId' => $user->uid,
                'stageToken' => $token
            )
        );

        $html->loadTpl(
            'editor/feature/body.editor.html',
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
function localAssembler($currentType='') {
    global $html;

    // 组装特征下拉列表
    $featureTypes = array();
    foreach ($GLOBALS['meshal']['featureType'] as $featureType => $settings) {
        $featureTypes[] = array(
            '--featureType' => $featureType,
            '--featureTypeName' => "{?{$settings['name']}?}",
            '--selected' => $featureType == $currentType ? 'selected' : ''
        );
    }

    $html->set(
        '$featureTypeOptions',
        $html->duplicate(
            'editor/feature/dup.option.featureType.html',
            $featureTypes
        )
    );

    //组装装备位置表单
    $equipmentContainer = array();
    foreach ($GLOBALS['meshal']['equipmentContainer'] as $containerCode => $settings) {
        $equipmentContainer[] = array(
            '--containerName' => "{?{$settings['name']}?}",
            '--containerCode' => $containerCode
        );
    }

    $html->set(
        '$equipmentContainers',
        $html->duplicate(
            'editor/feature/dup.equipmentContainer.html',
            $equipmentContainer
        )
    );
}

//预设变量
function localPreset() {
    global $html;

    $html->set('$token', '');
    $html->set('$nameReadonly', '');
    $html->set('$typeDisabled', '');
    $html->set('$editType', '');
    $html->set('$editName', '');
    $html->set('$origStrength', 0);
    $html->set('$origProbability', 0);
    $html->set('$name', '');
    $html->set('$translation', '');
    $html->set('$description', '');
    $html->set('$probabilityModifier', 0);
    $html->set('$mModifier', 0);
    $html->set('$aModifier', 0);
    $html->set('$sModifier', 0);
    $html->set('$tModifier', 0);
    $html->set('$eModifier', 0);
    $html->set('$rModifier', 0);
    $html->set('$apModifier', 0);
    $html->set('$ccModifier', 0);
    $html->set('$prModifier', 0);
    $html->set('$msModifier', 0);
    $html->set('$mMultiplier', 1.0);
    $html->set('$aMultiplier', 1.0);
    $html->set('$sMultiplier', 1.0);
    $html->set('$tMultiplier', 1.0);
    $html->set('$eMultiplier', 1.0);
    $html->set('$rMultiplier', 1.0);
    $html->set('$apMultiplier', 1.0);
    $html->set('$ccMultiplier', 1.0);
    $html->set('$prMultiplier', 1.0);
    $html->set('$msMultiplier', 1.0);
    $html->set('$ipCheck', '');
    $html->set('$ieCheck', '');
    $html->set('$ioCheck', '');
    $html->set('$sizeLevel', '');

    foreach ($GLOBALS['meshal']['equipmentContainer'] as $containerCode => $settings) {
        $html->set('$slots.'.$containerCode, 0);
    }

    $html->set('$maxModifier.attr', is_null($GLOBALS['meshal']['maxModifier']['attr']) ? '' : $GLOBALS['meshal']['maxModifier']['attr']);
    $html->set('$maxModifier.protect', is_null($GLOBALS['meshal']['maxModifier']['protect']) ? '' : $GLOBALS['meshal']['maxModifier']['protect']);
    $html->set('$maxModifier.ap', is_null($GLOBALS['meshal']['maxModifier']['ap']) ? '' : $GLOBALS['meshal']['maxModifier']['ap']);
    $html->set('$maxModifier.cc', is_null($GLOBALS['meshal']['maxModifier']['cc']) ? '' : $GLOBALS['meshal']['maxModifier']['cc']);
    $html->set('$maxModifier.pr', is_null($GLOBALS['meshal']['maxModifier']['pr']) ? '' : $GLOBALS['meshal']['maxModifier']['pr']);
    $html->set('$maxModifier.ms', is_null($GLOBALS['meshal']['maxModifier']['ms']) ? '' : $GLOBALS['meshal']['maxModifier']['ms']);
    $html->set('$maxMultiplier.attr', is_null($GLOBALS['meshal']['maxMultiplier']['attr']) ? '' : $GLOBALS['meshal']['maxMultiplier']['attr']);
    $html->set('$maxMultiplier.protect', is_null($GLOBALS['meshal']['maxMultiplier']['protect']) ? '' : $GLOBALS['meshal']['maxMultiplier']['protect']);
    $html->set('$maxMultiplier.ap', is_null($GLOBALS['meshal']['maxMultiplier']['ap']) ? '' : $GLOBALS['meshal']['maxMultiplier']['ap']);
    $html->set('$maxMultiplier.cc', is_null($GLOBALS['meshal']['maxMultiplier']['cc']) ? '' : $GLOBALS['meshal']['maxMultiplier']['cc']);
    $html->set('$maxMultiplier.pr', is_null($GLOBALS['meshal']['maxMultiplier']['pr']) ? '' : $GLOBALS['meshal']['maxMultiplier']['pr']);
    $html->set('$maxMultiplier.ms', is_null($GLOBALS['meshal']['maxMultiplier']['ms']) ? '' : $GLOBALS['meshal']['maxMultiplier']['ms']);
    // $html->set('$addAbility', '');

}

//组装提交的信息为写入数据库的数据
function localSubmit() {
    global $db;
    global $user;

    $submit = array(
        'name' => $_POST['editName'] == '' ? $_POST['name'] : $_POST['editName'],
        'type' => $_POST['editType'] == '' ? $_POST['type'] : $_POST['editType'],
        'probabilityModifier' => intval($_POST['probabilityModifier']),
        'lastUpdate' => time()
    );

    $submitData = array(
        'modifier' => array(
            'm' => intval($_POST['mModifier']),
            'a' => intval($_POST['aModifier']),
            's' => intval($_POST['sModifier']),
            't' => intval($_POST['tModifier']),
            'e' => intval($_POST['eModifier']),
            'r' => intval($_POST['rModifier']),
            'ip' => $_POST['ipCheck'] ? 1 : 0,
            'ie' => $_POST['ieCheck'] ? 1 : 0,
            'io' => $_POST['ioCheck'] ? 1 : 0,
            'ap' => intval($_POST['apModifier']),
            'cc' => intval($_POST['ccModifier']),
            'pr' => intval($_POST['prModifier']),
            'ms' => intval($_POST['msModifier'])
        ),
        'multiplier' => array(
            'm' => floatval($_POST['mMultiplier']),
            'a' => floatval($_POST['aMultiplier']),
            's' => floatval($_POST['sMultiplier']),
            't' => floatval($_POST['tMultiplier']),
            'e' => floatval($_POST['eMultiplier']),
            'r' => floatval($_POST['rMultiplier']),
            'ap' => floatval($_POST['apMultiplier']),
            'cc' => floatval($_POST['ccMultiplier']),
            'pr' => floatval($_POST['prMultiplier']),
            'ms' => floatval($_POST['msMultiplier'])
        ),
        'availableFeature' => array(),
        'addFeature' => array(),
    );

    if($submit['type'] == 'size') {
        $submitData['sizeLevel'] = $_POST['sizeLevel'];
    }

    foreach ($GLOBALS['meshal']['equipmentContainer'] as $containerCode => $settings) {
        $submitData['equipmentSlots'][$containerCode] = $_POST['slots-'.$containerCode];
    }


    ################################################
    # 开始计算实力
    ################################################

    $strength = array();

    //加数部分
    $strength['modifier']['m'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['modifier']['m']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['modifier']['a'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['modifier']['a']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['modifier']['s'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['modifier']['s']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];


    if($submitData['modifier']['ip'] == 0) {
        $strength['modifier']['t'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['modifier']['t']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['modifier']['t'] = 0;
    }

    if($submitData['modifier']['ie'] == 0) {
        $strength['modifier']['e'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['modifier']['e']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['modifier']['e'] = 0;
    }

    if($submitData['modifier']['io'] == 0) {
        $strength['modifier']['r'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['modifier']['r']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['modifier']['r'] = 0;
    }

    $strength['modifier']['pr'] = $submitData['modifier']['pr'] * $GLOBALS['meshal']['character']['strength']['pr'];

    $strength['modifier']['ms'] = $submitData['modifier']['ms'] * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['modifier']['ap'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap'] + $submitData['modifier']['ap']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap']))
    ) * $GLOBALS['meshal']['character']['strength']['ap'];
    
    $strength['modifier']['cc'] = $submitData['modifier']['cc'] * $GLOBALS['meshal']['character']['strength']['cc'];

    //倍数部分
    $strength['multiplier']['m'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['multiplier']['m']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['multiplier']['a'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['multiplier']['a']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['multiplier']['s'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['multiplier']['s']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    if($submitData['multiplier']['ip'] == 0) {
        $strength['multiplier']['t'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['multiplier']['t']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['multiplier']['t'] = 0;
    }

    if($submitData['multiplier']['ie'] == 0) {
        $strength['multiplier']['e'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['multiplier']['e']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['multiplier']['e'] = 0;
    }

    if($submitData['multiplier']['ie'] == 0) {
        $strength['multiplier']['r'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['multiplier']['r']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['multiplier']['r'] = 0;
    }

    $strength['multiplier']['pr'] = (
        $GLOBALS['meshal']['character']['base']['pr'] * $submitData['multiplier']['pr'] 
        - $GLOBALS['meshal']['character']['base']['pr']
    ) * $GLOBALS['meshal']['character']['strength']['pr'];

    $strength['multiplier']['ms'] = (
        $GLOBALS['meshal']['character']['base']['ms'] * $submitData['multiplier']['ms'] 
        - $GLOBALS['meshal']['character']['base']['ms']
    ) * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['multiplier']['ap'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap'] * $submitData['multiplier']['ap']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap']))
    ) * $GLOBALS['meshal']['character']['strength']['ap'];

    $strength['multiplier']['cc'] = (
        $GLOBALS['meshal']['character']['base']['cc'] * $submitData['multiplier']['cc'] 
        - $GLOBALS['meshal']['character']['base']['cc']
    ) * $GLOBALS['meshal']['character']['strength']['cc'];

    //单独计算免疫部分：由于越多的免疫越接近无敌，所以这里用阶乘的方式计算
    $strengthIp = $submitData['modifier']['ip'] == 0 ? 0 : 1;
    $strengthIe = $submitData['modifier']['ie'] == 0 ? 0 : 1;
    $strengthIo = $submitData['modifier']['io'] == 0 ? 0 : 1;

    $strength['immunity'] = \fPow(
        $GLOBALS['meshal']['character']['strength']['immune'],
        $strengthIp + $strengthIe + $strengthIo
    ) - 1;

    $submit['strength'] = intval(
        array_sum($strength['modifier']) 
        + array_sum($strength['multiplier']) 
        + $strength['immunity']
    );

    ################################################
    # 计算实力结束
    ################################################

    \fLog("{$submit['type']}.{$submit['name']} strength modifier was set to {$submit['strength']}");
    \fLog(\fDump($strength), 1);
    \fLog(\fDump($submitData), 1);

    //从feature_stage中取出临时编辑并整合
    $stage = $db->getArr(
        'feature_stage',
        array(
            "`stageToken` = '{$_POST['token']}'"
        ),
        null,
        1
    );
    $submitData['availableFeature'] = json_decode($stage[0]['availableFeature'], true);
    $submitData['addFeature'] = json_decode($stage[0]['addFeature'], true);
    $submitData['addAbility'] = json_decode($stage[0]['addAbility'], true);

    $submit['data'] = json_encode($submitData);


    return $submit;
}
?>