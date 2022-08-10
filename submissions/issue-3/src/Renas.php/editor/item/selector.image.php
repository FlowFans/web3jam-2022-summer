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

$GLOBALS['debug']['log'] = FALSE; //在本脚本中，临时关闭debug日志记录，以避免产生无意义的数据库查询记录。

################################################
# 初始化结束
################################################

/** 
 * GET方法传入参数
 * 
 * token
 * 通过这个选择器给什么物品赋予类型，token对应的是item_stage中的数据记录
 * 
 * category
 * 是哪个父类下的物品类型
 * 通常物品类型都有一个父类，比如刀剑和盾牌的父类都属于武器
 * 
 * type
 * 为给物品赋予类型的名字
 * 
 * action
 * 操作参数，须和type参数一同使用
 * - 添加特征则action=add
 * - 移除特征则action=remove
 */


$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/embeded.css');
$html->loadCss('css/meshal.css');
$html->loadTpl('editor/item/dup.imageSelector.html', 'body');

$user->challengeRole('admin', 'editor');

//从item_stage读取记录
//检查是否有token
if(!$_GET['token']) {
    \fLog("Error: no token is given");
//还需要设计一个内容为空的页面
    \fDie();
}

$stage = $db->getArr(
    'item_stage',
    array(
        "`stageToken` = '{$_GET['token']}'"
    ),
    null,
    1
);
if($stage === false) {
    \fLog("Error: no item stage data fetched");
//还需要设计一个内容为空的页面
    \fDie();
} else {
    $stage = $stage[0];
    $stageImage = \fDecode($stage['image']);
}

$origData = \meshal\xItem::getData($stage['name']);

if($_POST['token']) {
    if(!empty($_FILES['image'])) {
        $upload = new \xUpload($_FILES['image']);

        if($upload->uploaded) {
            $upload->file_new_name_body = \fGenGuid();
            $upload->image_resize = true;
            $upload->image_x = $GLOBALS['meshal']['item']['width'];
            $upload->image_y = $GLOBALS['meshal']['item']['height'];
            $upload->image_ratio_crop = true;
    
            $upload->process(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage']);

            //把原来的stage图片删除
            if(
                file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$stageImage)
                && !is_dir(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$stageImage)
                && $origData['image'] !== $stageImage
            ) {
                unlink(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$stageImage);
            }

            $imageFile = $upload->file_dst_name;
            
            $db->update(
                'item_stage',
                array(
                    'image' => \fEncode($imageFile)
                ),
                array(
                    "`stageToken` = '{$_GET['token']}'"
                ),
                1
            );
        }
    }
}


//重新获取更新后的数据
$stage = $db->getArr(
    'item_stage',
    array(
        "`stageToken` = '{$_GET['token']}'"
    ),
    null,
    1
);
if($stage === false) {
    \fLog("Error: no item stage data fetched");
//还需要设计一个内容为空的页面
    \fDie();
} else {
    $stage = $stage[0];
    $imageFile = \fDecode($stage['image']);
}

//拼装物品图片数据
$html->set(
    '$image',
    (
        is_null($imageFile)
        || $imageFile == '' 
        || !file_exists(_ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$imageFile)
    ) 
        ? "{?!dirImg?}cardBg.default.item.jpg" 
        : _ROOT.DIR_UPLOAD.$GLOBALS['deploy']['upload']['itemImage'].$imageFile
);

$html->set('$stageToken', $_GET['token']);

$html->output('embed');
\fDie();



?>