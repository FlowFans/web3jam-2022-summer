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

use \meshal\xItem as xItem;
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
    if($_POST['editName'] !== '') { //是提交对已有物品的编辑
        $data = \meshal\xItem::getData($_POST['editName']);
        $desc = $db->getArr(
            'languages',
            array(
                "`name` = 'itemDesc.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );
        $trans = $db->getArr(
            'languages',
            array(
                "`name` = 'itemName.{$_POST['editName']}'",
                "`lang` = '{$html->langCode}'"
            ),
            null,
            1
        );

        $submit = localSubmit();

        //检查是否有类型
        $stage = $db->getArr(
            'item_stage',
            array(
                "`stageToken` = '{$_POST['token']}'"
            ),
            null,
            1
        );
        $stageType = json_decode($stage[0]['type'], true);
        if(empty($stageType) || is_null($stageType)) {
            \fNotify('notify.editor.item.noType', 'warn');
            $error++;
        }

        if($error == 0) {
            if($data === false) {
                //假设数据库中不存在，那么就直接创建
                $check = $db->insert(
                    'items',
                    $submit
                );
    
                //插入或更新translation对应的语言记录
                if($trans === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "itemName.{$_POST['editName']}",
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
                            "`name` = 'itemName.{$_POST['editName']}'",
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
                            "`name` = 'itemDesc.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }
    
                //更新物品的类型记录
                $db->delete( //先删除原来的
                    'item_types',
                    array(
                        "`name` = '{$_POST['editName']}'"
                    )
                );
    
                $stage = $db->getArr( //取Stage数据
                    'item_stage',
                    array(
                        "`stageToken` = '{$_GET['token']}'"
                    )
                );
                $stageType = json_decode($stage[0]['type'], true);
                //遍历stage数据中的type并写入
                foreach ($stageType as $categoryName => $typeArr) {
                    foreach ($typeArr as $k => $type) {
                        $db->insert(
                            'item_types',
                            array(
                                'name' => $_POST['name'],
                                'category' => $categoryName,
                                'type' => $type
                            )
                        );
                    }
                }
                
            } else {
                //数据库中有记录，则更新数据
                $postEditName = $_POST['editName'];
                $check = $db->update(
                    'items',
                    $submit,
                    array(
                        "`name` = '{$postEditName}'"
                    ),
                    1
                );
    
                //插入或更新translation
                if($trans === false) {
                    $db->insert(
                        'languages',
                        array(
                            'name' => "itemName.{$_POST['editName']}",
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
                            "`name` = 'itemName.{$_POST['editName']}'",
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
                            "`name` = 'itemDesc.{$_POST['editName']}'",
                            "`lang` = '{$html->langCode}'"
                        ),
                        1
                    );
                }
    
                //更新物品的类型记录
                $db->delete( //先删除原来的
                    'item_types',
                    array(
                        "`name` = '{$_POST['editName']}'"
                    )
                );
    
                $stage = $db->getArr( //取Stage数据
                    'item_stage',
                    array(
                        "`stageToken` = '{$_POST['token']}'"
                    )
                );
                $stageType = json_decode($stage[0]['type'], true);
                //遍历stage数据中的type并写入
                foreach ($stageType as $categoryName => $typeArr) {
                    foreach ($typeArr as $k => $type) {
                        $db->insert(
                            'item_types',
                            array(
                                'name' => $_POST['name'],
                                'category' => $categoryName,
                                'type' => $type
                            )
                        );
                    }
                }
            }
        } else {
            //有错误提示，则不跳转页面，而是在当前页面显示错误提示
            $html->set('$editName', $_POST['editName']);

            //把已提交的数据重组回来
            foreach ($_POST as $k => $v) {
                $html->set("\${$k}", isset($v) ? $v : '');
            }
            $html->set('$ipCheckEquip', $_POST['ipCheckEquip'] ? 'checked' : '');
            $html->set('$ieCheckEquip', $_POST['ieCheckEquip'] ? 'checked' : '');
            $html->set('$ioCheckEquip', $_POST['ioCheckEquip'] ? 'checked' : '');
            $html->set('$ipCheckCarry', $_POST['ipCheckCarry'] ? 'checked' : '');
            $html->set('$ieCheckCarry', $_POST['ieCheckCarry'] ? 'checked' : '');
            $html->set('$ioCheckCarry', $_POST['ioCheckCarry'] ? 'checked' : '');

            //加载页面模板
            $html->loadTpl(
                'editor/item/body.editor.html',
                'body'
            );

            localAssembler($_POST['occupancy']);
            $html->set('$nameReadonly', 'readonly');
            $html->output();
            \fDie();
        }
        

        //进入重定向页
        $html->set('$itemName', $_POST['editName']);

        $html->redirect(
            'index.php',
            'pageTitle.editor.item',
            'redirect.message.editor.item.updated'
        );
        \fDie(); 
        
    } else { //是提交一个新物品的数据
        //检查数据有效性
        if($_POST['name'] == '') {
            \fNotify('notify.editor.item.nameRequired', 'warn');
            $error++;
        }

        //检查是否有重名项
        if(xItem::getData($_POST['name']) !== false) {
            \fNotify('notify.editor.item.nameTaken', 'warn');
            $error++;
        }

        //检查token
        if(!$_POST['token'] || $_POST['token'] == '') {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.item',
                'redirect.message.editor.item.failed'
            );
            \fDie();
        }

        //根据token取item_stage数据
        $stage = $db->getArr(
            'item_stage',
            array(
                "`stageToken` = '{$_POST['token']}'"
            ),
            null,
            1
        );
        if($stage === false) {
            $html->redirect(
                'edit.php',
                'pageTitle.editor.item',
                'redirect.message.editor.item.failed'
            );
            \fDie();
        }

        //检查是否有类型
        $stageType = json_decode($stage[0]['type'], true);
        if(empty($stageType) || is_null($stageType)) {
            \fNotify('notify.editor.item.noType', 'warn');
            $error++;
        }

        //组装提交的数据
        $submit = localSubmit();

        if($error == 0) {

            //插入数据
            $check = $db->insert(
                'items',
                $submit
            );

            //插入或更新translation对应的语言记录
            $trans = $db->getArr(
                'languages',
                array(
                    "`name` = 'itemName.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($trans === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "itemName.{$_POST['name']}",
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
                        "`name` = 'itemName.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //插入或更新description对应的语言记录
            $desc = $db->getArr(
                'languages',
                array(
                    "`name` = 'itemDesc.{$_POST['name']}'",
                    "`lang` = '{$html->langCode}'"
                ),
                null,
                1
            );
            if($desc === false) {
                $db->insert(
                    'languages',
                    array(
                        'name' => "itemDesc.{$_POST['name']}",
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
                        "`name` = 'itemDesc.{$_POST['name']}'",
                        "`lang` = '{$html->langCode}'"
                    ),
                    1
                );
            }

            //添加物品的类型记录
            $stageType = json_decode($stage[0]['type'], true);
            if(
                !empty($stageType)
                && !is_null($stageType)
            ) {
                foreach ($stageType as $categoryName => $typeArr) {
                    foreach ($typeArr as $k => $type) {
                        $db->insert(
                            'item_types',
                            array(
                                'name' => $_POST['name'],
                                'category' => $categoryName,
                                'type' => $type
                            )
                        );
                    }
                }
            }


            //进入重定向页
            $html->set('$itemName', $_POST['name']);

            $html->redirect(
                'index.php',
                'pageTitle.editor.item',
                'redirect.message.editor.item.created'
            );
            \fDie(); 
            
        } else {
            //有错误提示，则不跳转页面，而是在当前页面显示错误提示
            $html->set('$editName', '');

            //把已提交的数据重组回来
            foreach ($_POST as $k => $v) {
                $html->set("\${$k}", isset($v) ? $v : '');
            }
            $html->set('$ipCheckEquip', $_POST['ipCheckEquip'] ? 'checked' : '');
            $html->set('$ieCheckEquip', $_POST['ieCheckEquip'] ? 'checked' : '');
            $html->set('$ioCheckEquip', $_POST['ioCheckEquip'] ? 'checked' : '');
            $html->set('$ipCheckCarry', $_POST['ipCheckCarry'] ? 'checked' : '');
            $html->set('$ieCheckCarry', $_POST['ieCheckCarry'] ? 'checked' : '');
            $html->set('$ioCheckCarry', $_POST['ioCheckCarry'] ? 'checked' : '');

            //加载页面模板
            $html->loadTpl(
                'editor/item/body.editor.html',
                'body'
            );

            localAssembler($_POST['occupancy']);
            $html->set('$nameReadonly', '');
            $html->output();
            \fDie();
        }
    }
} else { //非提交行为
    if($_GET['name']) { //通过链接进入的已有物品（GET方法从地址参数中获取）。读取已有物品并准备编辑。
        //加载模板
        $html->loadTpl(
            'editor/item/body.editor.html',
            'body'
        );
        
        $html->set('$nameReadonly', 'readonly');
        $html->set('$editName', $_GET['name']);

        $data = \meshal\xItem::getData($_GET['name']);
        localAssembler($data['data']['occupancy']['type']);

        if($data !== false) {
            //获取stage的数据
            $stageData = $db->getArr(
                'item_stage',
                array(
                    "`name` = '{$_GET['name']}'",
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
                    && file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$stageImage)
                    && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$stageImage)
                    && $stageImage !== $data['image'] //只有stageImage不等于实际的image时才做删除
                ) {
                    unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$stageImage);
                }
            }
            
            //对旧的item_stage数据（该用户未保存的编辑）做清理
            $db->delete(
                'item_stage',
                array(
                    "`editorId` = '{$user->uid}'",
                )
            );

            //将加载到的数据存入item_stage用于编辑
            $token = \fGenGuid();
            $db->insert(
                'item_stage',
                array(
                    'editorId' => $user->uid,
                    'stageToken' => $token,
                    'type' => json_encode($data['type']),
                    'name' => $_GET['name'],
                    'icon' => $data['icon'],
                    'image' => \fEncode($data['image']),
                    // 'availableFeature' => json_encode($data['data']['availableFeature']),
                    // 'addFeature' => json_encode($data['data']['addFeature']),
                    // 'addAbility' => json_encode($data['data']['addAbility']),
                    'translation' => \fEncode($html->dbLang("itemName.{$_GET['name']}")),
                    'description' => \fEncode($html->dbLang("itemDesc.{$_GET['name']}")),
                    'loads' => $data['loads'],
                    'strengthEquip' => $data['strength']['equip'],
                    'strengthCarry' => $data['strength']['carry'],
                    'probabilityModifier' => $data['probabilityModifier']
                )
            );

            //把加载到的数据填入前端
            $html->set('$token', $token);
            $html->set('$name', $data['name']);
            $html->set('$translation', $html->dbLang("itemName.{$data['name']}"));
            $html->set('$description', $html->dbLang("itemDesc.{$data['name']}"));
            $html->set('$loads', $data['loads']);
            $html->set('$occupancySlots', $data['data']['occupancy']['slots']);
            $html->set('$probabilityModifier', $data['probabilityModifier']);
            //装备时的修正
            $html->set('$mModifierEquip', $data['data']['equip']['modifier']['m']);
            $html->set('$aModifierEquip', $data['data']['equip']['modifier']['a']);
            $html->set('$sModifierEquip', $data['data']['equip']['modifier']['s']);
            $html->set('$tModifierEquip', $data['data']['equip']['modifier']['t']);
            $html->set('$eModifierEquip', $data['data']['equip']['modifier']['e']);
            $html->set('$rModifierEquip', $data['data']['equip']['modifier']['r']);
            $html->set('$apModifierEquip', $data['data']['equip']['modifier']['ap']);
            $html->set('$ccModifierEquip', $data['data']['equip']['modifier']['cc']);
            $html->set('$prModifierEquip', $data['data']['equip']['modifier']['pr']);
            $html->set('$msModifierEquip', $data['data']['equip']['modifier']['ms']);
            $html->set('$mMultiplierEquip', $data['data']['equip']['multiplier']['m']);
            $html->set('$aMultiplierEquip', $data['data']['equip']['multiplier']['a']);
            $html->set('$sMultiplierEquip', $data['data']['equip']['multiplier']['s']);
            $html->set('$tMultiplierEquip', $data['data']['equip']['multiplier']['t']);
            $html->set('$eMultiplierEquip', $data['data']['equip']['multiplier']['e']);
            $html->set('$rMultiplierEquip', $data['data']['equip']['multiplier']['r']);
            $html->set('$apMultiplierEquip', $data['data']['equip']['multiplier']['ap']);
            $html->set('$ccMultiplierEquip', $data['data']['equip']['multiplier']['cc']);
            $html->set('$prMultiplierEquip', $data['data']['equip']['multiplier']['pr']);
            $html->set('$msMultiplierEquip', $data['data']['equip']['multiplier']['ms']);
            if($data['data']['equip']['modifier']['ip'] > 0) $html->set('$ipCheckEquip', 'checked');
            if($data['data']['equip']['modifier']['ie'] > 0) $html->set('$ieCheckEquip', 'checked');
            if($data['data']['equip']['modifier']['io'] > 0) $html->set('$ioCheckEquip', 'checked');
            //携带时的修正
            $html->set('$mModifierCarry', $data['data']['carry']['modifier']['m']);
            $html->set('$aModifierCarry', $data['data']['carry']['modifier']['a']);
            $html->set('$sModifierCarry', $data['data']['carry']['modifier']['s']);
            $html->set('$tModifierCarry', $data['data']['carry']['modifier']['t']);
            $html->set('$eModifierCarry', $data['data']['carry']['modifier']['e']);
            $html->set('$rModifierCarry', $data['data']['carry']['modifier']['r']);
            $html->set('$apModifierCarry', $data['data']['carry']['modifier']['ap']);
            $html->set('$ccModifierCarry', $data['data']['carry']['modifier']['cc']);
            $html->set('$prModifierCarry', $data['data']['carry']['modifier']['pr']);
            $html->set('$msModifierCarry', $data['data']['carry']['modifier']['ms']);
            $html->set('$mMultiplierCarry', $data['data']['carry']['multiplier']['m']);
            $html->set('$aMultiplierCarry', $data['data']['carry']['multiplier']['a']);
            $html->set('$sMultiplierCarry', $data['data']['carry']['multiplier']['s']);
            $html->set('$tMultiplierCarry', $data['data']['carry']['multiplier']['t']);
            $html->set('$eMultiplierCarry', $data['data']['carry']['multiplier']['e']);
            $html->set('$rMultiplierCarry', $data['data']['carry']['multiplier']['r']);
            $html->set('$apMultiplierCarry', $data['data']['carry']['multiplier']['ap']);
            $html->set('$ccMultiplierCarry', $data['data']['carry']['multiplier']['cc']);
            $html->set('$prMultiplierCarry', $data['data']['carry']['multiplier']['pr']);
            $html->set('$msMultiplierCarry', $data['data']['carry']['multiplier']['ms']);
            if($data['data']['carry']['modifier']['ip'] > 0) $html->set('$ipCheckCarry', 'checked');
            if($data['data']['carry']['modifier']['ie'] > 0) $html->set('$ieCheckCarry', 'checked');
            if($data['data']['carry']['modifier']['io'] > 0) $html->set('$ioCheckCarry', 'checked');
            
            //一些占位符初始化
            $html->set('$useCheckAll', '');
            $html->set('$useCheckAny', '');
            $html->set('$useEfx', '');
            
            //使用前提
            if(empty($data['data']['use']['checkAll'])) {
                $html->set('$useCheckAll', '');
            } else {
                $arr = array();
                foreach($data['data']['use']['checkAll'] as $k => $check) {
                    $arr[] = implode(',', $check); //把前提数组拼装回字符串配置格式
                }
                $html->set('$useCheckAll', implode(PHP_EOL, $arr));
            }

            if(empty($data['data']['use']['checkAny'])) {
                $html->set('$useCheckAny', '');
            } else {
                $arr = array();
                foreach($data['data']['use']['checkAny'] as $k => $check) {
                    $arr[] = implode(',', $check); //把前提数组拼装回字符串配置格式
                }
                $html->set('$useCheckAny', implode(PHP_EOL, $arr));
            }

            //使用效果
            if(empty($data['data']['use']['efx'])) {
                $html->set('$useEfx', '');
            } else {
                $arr = array();
                foreach($data['data']['use']['efx'] as $k => $efx) {
                    $arr[] = implode(',', $efx); //把效果数组拼装回字符串配置格式
                }
                $html->set('$useEfx', implode(PHP_EOL, $arr));
            }

            // $efxUse = implode("\n", $arr);
            // $html->set('$efxUse', $efxUse);
            
            
            // $html->set(
            //     '$addAbility', 
            //     empty($data['data']['addAbility']) 
            //         ? '' 
            //         : implode(PHP_EOL, $data['data']['addAbility'])
            // );

            $html->set('$nameReadonly', 'readonly');
            $html->output();
            \fDie();

        } else { //没有查到这个物品，那么从白板开始
            //清除占位变量
            localPreset();
            $html->output();
            \fDie();
        }
    } else { //一个白板物品的创建页面
        $token = \fGenGuid();

        //删掉用户之前在item_stage中的信息
        $db->delete(
            'item_stage',
            array(
                "`editorId` = '{$user->uid}'"
            ),
            1
        );

        //为这次的白板编辑新建一个item_stage记录
        $db->insert(
            'item_stage',
            array(
                'editorId' => $user->uid,
                'stageToken' => $token
            )
        );

        $html->loadTpl(
            'editor/item/body.editor.html',
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
    $currentOccupancy = null
) {
    global $html;

    // 组装装备位下拉列表
    $occupancies = array(
        array( //这是默认的不可装备
            '--occupancyType' => '',
            '--occupancyTypeName' => '{?common.none?}',
            '--selected' => (is_null($currentOccupancy) || $currentOccupancy == '') ? 'selected' : ''
        )
    );
    foreach ($GLOBALS['meshal']['equipmentContainer'] as $container => $data) {
        $occupancies[] = array(
            '--occupancyType' => $data['type'],
            '--occupancyTypeName' => "{?{$data['name']}?}",
            '--selected' => $data['type'] == $currentOccupancy ? 'selected' : ''
        );
    }

    $html->set(
        '$occupancyOptions',
        $html->duplicate(
            'editor/item/dup.option.occupancy.html',
            $occupancies
        )
    );
}

//预设变量
function localPreset() {
    global $html;

    $html->set('$token', '');
    $html->set('$nameReadonly', '');
    $html->set('$editName', '');
    $html->set('$origStrength', 0);
    $html->set('$origProbability', 0);
    $html->set('$name', '');
    $html->set('$translation', '');
    $html->set('$description', '');
    $html->set('$loads', 0);
    $html->set('$occupancySlots', 1);
    $html->set('$probabilityModifier', 0);
    $html->set('$mModifierEquip', 0);
    $html->set('$aModifierEquip', 0);
    $html->set('$sModifierEquip', 0);
    $html->set('$tModifierEquip', 0);
    $html->set('$eModifierEquip', 0);
    $html->set('$rModifierEquip', 0);
    $html->set('$apModifierEquip', 0);
    $html->set('$ccModifierEquip', 0);
    $html->set('$prModifierEquip', 0);
    $html->set('$msModifierEquip', 0);
    $html->set('$mMultiplierEquip', 1.0);
    $html->set('$aMultiplierEquip', 1.0);
    $html->set('$sMultiplierEquip', 1.0);
    $html->set('$tMultiplierEquip', 1.0);
    $html->set('$eMultiplierEquip', 1.0);
    $html->set('$rMultiplierEquip', 1.0);
    $html->set('$apMultiplierEquip', 1.0);
    $html->set('$ccMultiplierEquip', 1.0);
    $html->set('$prMultiplierEquip', 1.0);
    $html->set('$msMultiplierEquip', 1.0);
    $html->set('$ipCheckEquip', '');
    $html->set('$ieCheckEquip', '');
    $html->set('$ioCheckEquip', '');
    $html->set('$mModifierCarry', 0);
    $html->set('$aModifierCarry', 0);
    $html->set('$sModifierCarry', 0);
    $html->set('$tModifierCarry', 0);
    $html->set('$eModifierCarry', 0);
    $html->set('$rModifierCarry', 0);
    $html->set('$apModifierCarry', 0);
    $html->set('$ccModifierCarry', 0);
    $html->set('$prModifierCarry', 0);
    $html->set('$msModifierCarry', 0);
    $html->set('$mMultiplierCarry', 1.0);
    $html->set('$aMultiplierCarry', 1.0);
    $html->set('$sMultiplierCarry', 1.0);
    $html->set('$tMultiplierCarry', 1.0);
    $html->set('$eMultiplierCarry', 1.0);
    $html->set('$rMultiplierCarry', 1.0);
    $html->set('$apMultiplierCarry', 1.0);
    $html->set('$ccMultiplierCarry', 1.0);
    $html->set('$prMultiplierCarry', 1.0);
    $html->set('$msMultiplierCarry', 1.0);
    $html->set('$ipCheckCarry', '');
    $html->set('$ieCheckCarry', '');
    $html->set('$ioCheckCarry', '');

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

    $html->set('$useCheckAll', '');
    $html->set('$useCheckAny', '');
    $html->set('$useEfx', '');
}

//组装提交的信息为写入数据库的数据
function localSubmit() {
    global $db;
    global $user;

    $submit = array(
        'name' => $_POST['editName'] == '' ? $_POST['name'] : $_POST['editName'],
        'probabilityModifier' => intval($_POST['probabilityModifier']),
        'loads' => $_POST['loads'],
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
    
    //         $upload->process(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage']);
    //         $image = $upload->file_dst_name;
    //     }
    //     $submit['image'] = $image;
    // }

    $submitData = array(
        'carry' => array(
            'modifier' => array(
                'm' => intval($_POST['mModifierCarry']),
                'a' => intval($_POST['aModifierCarry']),
                's' => intval($_POST['sModifierCarry']),
                't' => intval($_POST['tModifierCarry']),
                'e' => intval($_POST['eModifierCarry']),
                'r' => intval($_POST['rModifierCarry']),
                'ip' => $_POST['ipCheckCarry'] ? 1 : 0,
                'ie' => $_POST['ieCheckCarry'] ? 1 : 0,
                'io' => $_POST['ioCheckCarry'] ? 1 : 0,
                'ap' => intval($_POST['apModifierCarry']),
                'cc' => intval($_POST['ccModifierCarry']),
                'pr' => intval($_POST['prModifierCarry']),
                'ms' => intval($_POST['msModifierCarry'])
            ),
            'multiplier' => array(
                'm' => floatval($_POST['mMultiplierCarry']),
                'a' => floatval($_POST['aMultiplierCarry']),
                's' => floatval($_POST['sMultiplierCarry']),
                't' => floatval($_POST['tMultiplierCarry']),
                'e' => floatval($_POST['eMultiplierCarry']),
                'r' => floatval($_POST['rMultiplierCarry']),
                'ap' => floatval($_POST['apMultiplierCarry']),
                'cc' => floatval($_POST['ccMultiplierCarry']),
                'pr' => floatval($_POST['prMultiplierCarry']),
                'ms' => floatval($_POST['msMultiplierCarry'])
            )
        ),
        'equip' => array(
            'modifier' => array(
                'm' => intval($_POST['mModifierEquip']),
                'a' => intval($_POST['aModifierEquip']),
                's' => intval($_POST['sModifierEquip']),
                't' => intval($_POST['tModifierEquip']),
                'e' => intval($_POST['eModifierEquip']),
                'r' => intval($_POST['rModifierEquip']),
                'ip' => $_POST['ipCheckEquip'] ? 1 : 0,
                'ie' => $_POST['ieCheckEquip'] ? 1 : 0,
                'io' => $_POST['ioCheckEquip'] ? 1 : 0,
                'ap' => intval($_POST['apModifierEquip']),
                'cc' => intval($_POST['ccModifierEquip']),
                'pr' => intval($_POST['prModifierEquip']),
                'ms' => intval($_POST['msModifierEquip'])
            ),
            'multiplier' => array(
                'm' => floatval($_POST['mMultiplierEquip']),
                'a' => floatval($_POST['aMultiplierEquip']),
                's' => floatval($_POST['sMultiplierEquip']),
                't' => floatval($_POST['tMultiplierEquip']),
                'e' => floatval($_POST['eMultiplierEquip']),
                'r' => floatval($_POST['rMultiplierEquip']),
                'ap' => floatval($_POST['apMultiplierEquip']),
                'cc' => floatval($_POST['ccMultiplierEquip']),
                'pr' => floatval($_POST['prMultiplierEquip']),
                'ms' => floatval($_POST['msMultiplierEquip'])
            )
        ),
    );

    //处理使用前提
    $useCheckAll = \fLineToArray(\fPost('useCheckAll',''));
    $submitData['use']['checkAll'] = array();
    if(!empty($useCheckAll)) {
        foreach ($useCheckAll as $k => $check) {
            $submitData['use']['checkAll'][] = explode(',', $check);
        }
    }

    $useCheckAny = \fLineToArray(\fPost('useCheckAny',''));
    $submitData['use']['checkAny'] = array();
    if(!empty($useCheckAny)) {
        foreach ($useCheckAny as $k => $check) {
            $submitData['use']['checkAny'][] = explode(',', $check);
        }
    }

    //处理使用效果
    $useEfx = \fLineToArray(\fPost('useEfx',''));
    $submitData['use']['efx'] = array();
    if(!empty($useEfx)) {
        foreach ($useEfx as $k => $efx) {
            $submitData['use']['efx'][] = explode(',', $efx);
        }
    }
    

    //处理装备占位
    if(\fPost('occupancy', '') == '') {
        $submitData['occupancy']['type'] = null;
        $submitData['occupancy']['slots'] = 0;
    } else {
        $submitData['occupancy']['type'] = $_POST['occupancy'];
        $submitData['occupancy']['slots'] = $_POST['occupancySlots'];
    }

    ################################################
    # 开始计算实力
    ################################################

    $strength = array();

    //加数部分(装备效果)
    $strength['equip']['modifier']['m'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['equip']['modifier']['m']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['equip']['modifier']['a'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['equip']['modifier']['a']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['equip']['modifier']['s'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['equip']['modifier']['s']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];


    if($submitData['equip']['modifier']['ip'] == 0) {
        $strength['equip']['modifier']['t'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['equip']['modifier']['t']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['equip']['modifier']['t'] = 0;
    }

    if($submitData['equip']['modifier']['ie'] == 0) {
        $strength['equip']['modifier']['e'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['equip']['modifier']['e']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['equip']['modifier']['e'] = 0;
    }

    if($submitData['equip']['modifier']['io'] == 0) {
        $strength['equip']['modifier']['r'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['equip']['modifier']['r']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['equip']['modifier']['r'] = 0;
    }

    $strength['equip']['modifier']['pr'] = $submitData['equip']['modifier']['pr'] * $GLOBALS['meshal']['character']['strength']['pr'];

    $strength['equip']['modifier']['ms'] = $submitData['equip']['modifier']['ms'] * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['equip']['modifier']['ap'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap'] + $submitData['equip']['modifier']['ap']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap']))
    ) * $GLOBALS['meshal']['character']['strength']['ap'];
    
    $strength['equip']['modifier']['cc'] = $submitData['equip']['modifier']['cc'] * $GLOBALS['meshal']['character']['strength']['cc'];

    //倍数部分(装备效果)
    $strength['equip']['multiplier']['m'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['equip']['multiplier']['m']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['equip']['multiplier']['a'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['equip']['multiplier']['a']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['equip']['multiplier']['s'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['equip']['multiplier']['s']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    if($submitData['equip']['multiplier']['ip'] == 0) {
        $strength['equip']['multiplier']['t'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['equip']['multiplier']['t']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['equip']['multiplier']['t'] = 0;
    }

    if($submitData['equip']['multiplier']['ie'] == 0) {
        $strength['equip']['multiplier']['e'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['equip']['multiplier']['e']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['equip']['multiplier']['e'] = 0;
    }

    if($submitData['equip']['multiplier']['ie'] == 0) {
        $strength['equip']['multiplier']['r'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['equip']['multiplier']['r']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['equip']['multiplier']['r'] = 0;
    }

    $strength['equip']['multiplier']['pr'] = (
        $GLOBALS['meshal']['character']['base']['pr'] * $submitData['equip']['multiplier']['pr'] 
        - $GLOBALS['meshal']['character']['base']['pr']
    ) * $GLOBALS['meshal']['character']['strength']['pr'];

    $strength['equip']['multiplier']['ms'] = (
        $GLOBALS['meshal']['character']['base']['ms'] * $submitData['equip']['multiplier']['ms'] 
        - $GLOBALS['meshal']['character']['base']['ms']
    ) * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['equip']['multiplier']['ap'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap'] * $submitData['equip']['multiplier']['ap']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap']))
    ) * $GLOBALS['meshal']['character']['strength']['ap'];

    $strength['equip']['multiplier']['cc'] = (
        $GLOBALS['meshal']['character']['base']['cc'] * $submitData['equip']['multiplier']['cc'] 
        - $GLOBALS['meshal']['character']['base']['cc']
    ) * $GLOBALS['meshal']['character']['strength']['cc'];

    //单独计算免疫部分：由于越多的免疫越接近无敌，所以这里用阶乘的方式计算
    $strengthEquipIp = $submitData['equip']['modifier']['ip'] == 0 ? 0 : 1;
    $strengthEquipIe = $submitData['equip']['modifier']['ie'] == 0 ? 0 : 1;
    $strengthEquipIo = $submitData['equip']['modifier']['io'] == 0 ? 0 : 1;
    $strength['equip']['immunity'] = \fPow(
        $GLOBALS['meshal']['character']['strength']['immune'],
        $strengthEquipIp + $strengthEquipIe + $strengthEquipIo
    ) - 1;

    //加数部分(携带效果)
    $strength['carry']['modifier']['m'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['carry']['modifier']['m']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['carry']['modifier']['a'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['carry']['modifier']['a']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['carry']['modifier']['s'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] + $submitData['carry']['modifier']['s']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];


    if($submitData['carry']['modifier']['ip'] == 0) {
        $strength['carry']['modifier']['t'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['carry']['modifier']['t']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['carry']['modifier']['t'] = 0;
    }

    if($submitData['carry']['modifier']['ie'] == 0) {
        $strength['carry']['modifier']['e'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['carry']['modifier']['e']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['carry']['modifier']['e'] = 0;
    }

    if($submitData['carry']['modifier']['io'] == 0) {
        $strength['carry']['modifier']['r'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] + $submitData['carry']['modifier']['r']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['carry']['modifier']['r'] = 0;
    }

    $strength['carry']['modifier']['pr'] = $submitData['carry']['modifier']['pr'] * $GLOBALS['meshal']['character']['strength']['pr'];

    $strength['carry']['modifier']['ms'] = $submitData['carry']['modifier']['ms'] * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['carry']['modifier']['ap'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap'] + $submitData['carry']['modifier']['ap']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap']))
    ) * $GLOBALS['meshal']['character']['strength']['ap'];
    
    $strength['carry']['modifier']['cc'] = $submitData['carry']['modifier']['cc'] * $GLOBALS['meshal']['character']['strength']['cc'];

    //倍数部分(携带效果)
    $strength['carry']['multiplier']['m'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['carry']['multiplier']['m']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['carry']['multiplier']['a'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['carry']['multiplier']['a']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    $strength['carry']['multiplier']['s'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr'] * $submitData['carry']['multiplier']['s']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['attr']))
    ) * $GLOBALS['meshal']['character']['strength']['attr'];

    if($submitData['carry']['multiplier']['ip'] == 0) {
        $strength['carry']['multiplier']['t'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['carry']['multiplier']['t']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['carry']['multiplier']['t'] = 0;
    }

    if($submitData['carry']['multiplier']['ie'] == 0) {
        $strength['carry']['multiplier']['e'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['carry']['multiplier']['e']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['carry']['multiplier']['e'] = 0;
    }

    if($submitData['carry']['multiplier']['ie'] == 0) {
        $strength['carry']['multiplier']['r'] = (
            array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect'] * $submitData['carry']['multiplier']['r']))
            - array_sum(range(0, $GLOBALS['meshal']['character']['base']['protect']))
        ) * $GLOBALS['meshal']['character']['strength']['protect'];
    } else {
        $strength['carry']['multiplier']['r'] = 0;
    }

    $strength['carry']['multiplier']['pr'] = (
        $GLOBALS['meshal']['character']['base']['pr'] * $submitData['carry']['multiplier']['pr'] 
        - $GLOBALS['meshal']['character']['base']['pr']
    ) * $GLOBALS['meshal']['character']['strength']['pr'];

    $strength['carry']['multiplier']['ms'] = (
        $GLOBALS['meshal']['character']['base']['ms'] * $submitData['carry']['multiplier']['ms'] 
        - $GLOBALS['meshal']['character']['base']['ms']
    ) * $GLOBALS['meshal']['character']['strength']['ms'];

    $strength['carry']['multiplier']['ap'] = (
        array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap'] * $submitData['carry']['multiplier']['ap']))
        - array_sum(range(0, $GLOBALS['meshal']['character']['base']['ap']))
    ) * $GLOBALS['meshal']['character']['strength']['ap'];

    $strength['carry']['multiplier']['cc'] = (
        $GLOBALS['meshal']['character']['base']['cc'] * $submitData['carry']['multiplier']['cc'] 
        - $GLOBALS['meshal']['character']['base']['cc']
    ) * $GLOBALS['meshal']['character']['strength']['cc'];

    //单独计算免疫部分：由于越多的免疫越接近无敌，所以这里用阶乘的方式计算
    $strengthCarryIp = $submitData['carry']['modifier']['ip'] == 0 ? 0 : 1;
    $strengthCarryIe = $submitData['carry']['modifier']['ie'] == 0 ? 0 : 1;
    $strengthCarryIo = $submitData['carry']['modifier']['io'] == 0 ? 0 : 1;
    $strength['carry']['immunity'] = \fPow(
        $GLOBALS['meshal']['character']['strength']['immune'],
        $strengthCarryIp + $strengthCarryIe + $strengthCarryIo
    ) - 1;

    $submit['strengthEquip'] = intval(
        array_sum($strength['equip']['modifier']) 
        + array_sum($strength['equip']['multiplier']) 
        + $strength['equip']['immunity']
    );

    $submit['strengthCarry'] = intval(
        + array_sum($strength['carry']['modifier']) 
        + array_sum($strength['carry']['multiplier']) 
        + $strength['carry']['immunity']
    );

    ################################################
    # 计算实力结束
    ################################################

    \fLog("item.{$submit['name']} strength modifier was set to {$submit['strength']}");
    \fLog(\fDump($strength), 1);
    \fLog(\fDump($submitData), 1);

    //从item_stage中取出临时编辑并整合
    $stage = $db->getArr(
        'item_stage',
        array(
            "`stageToken` = '{$_POST['token']}'"
        ),
        null,
        1
    );
    // $submitData['availableFeature'] = json_decode($stage[0]['availableFeature'], true);
    // $submitData['addFeature'] = json_decode($stage[0]['addFeature'], true);
    $submit['icon'] = $stage[0]['icon'];
    $submit['image'] = $stage[0]['image'];
    $submitData['addAbility'] = json_decode($stage[0]['addAbility'], true);
    

    $submit['data'] = json_encode($submitData);


    return $submit;
}
?>