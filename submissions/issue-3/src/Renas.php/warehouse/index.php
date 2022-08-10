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
$html = new \xHtml;
$user = new \xUser;

$html->loadCss('css/meshal.css');
$html->loadTpl('warehouse/body.frame.html', 'body');

$stockCount = $db->getCount(
    'user_items',
    array(
        "`uid` = '{$user->uid}'",
        "`amount` > 0"
    )
);

if($stockCount > 0) {
    $html->set('$emptyWarehouseGuide', '');
    # 根据当前页取角色
    $rowStart = (\fGet('page', 1) - 1) * $GLOBALS['setting']['pager']['warehouse']['itemsPerPage'];

    $queryItems = $db->getArr(
        'user_items',
        array(
            "`uid` = '{$user->uid}'",
            "`amount` > 0"
        ),
        null,
        "{$rowStart},{$GLOBALS['setting']['pager']['warehouse']['itemsPerPage']}",
        MYSQLI_NUM,
        'lastUpdate',
        'DESC'
    );

    #组装物品列表
    $itemRenderer = new \meshal\xItem;
    $itemList = '';
    if($queryItems !== false) {
        //拼装物品数据
        $comp = array();
        foreach ($queryItems as $cur) {
            $itemRenderer->load($cur['name']);
            $itemRenderer->amount = $cur['amount'];

            #添加物品操作

            //使用
            if(!empty($itemRenderer->data['use']['efx'])) {
                $itemRenderer->addCtrl(
                    _ROOT."item/use/?item={$itemRenderer->name}".\fBackUrl(),
                    'button.item.use',
                    'bgOpaGreen1 colorWhite1'
                );
            }

            //装备
            if(!is_null($itemRenderer->data['occupancy']['type'])) {
                $itemRenderer->addCtrl(
                    _ROOT."item/equip/?item={$itemRenderer->name}".\fBackUrl(),
                    'button.item.equip',
                    'bgOpaGreen1 colorWhite1'
                );
            }

            //给予角色
            $itemRenderer->addCtrl(
                _ROOT."item/transfer/?item={$itemRenderer->name}".\fBackUrl(),
                'button.item.transfer',
                'bgOpaBlue1 colorWhite1'
            );
            $itemList .= $itemRenderer->render();


            // $curData = \meshal\xItem::getData($cur['name']);

            // $curType = array();
            // foreach ($curData['type'] as $categoryName => $types) {
            //     foreach ($types as $k => $typeName) {
            //         $curType[] = "{?itemType.{$categoryName}.{$typeName}?}";
            //     }
            // }

            // $comp[] = array(
            //     '--category' => $cur['category'],
            //     '--types' => implode('{?common.itemType.separator?}', $curType),
            //     '--name' => $cur['name'],
            //     '--amount' => $cur['amount'],
            //     '--icon' => $curData['iconFile'],
            //     '--equipStrength' => $curData['strength']['equip'],
            //     '--carryStrength' => $curData['strength']['carry']
            // );
        }

        $html->set(
            '$itemList', 
            $itemList
        );
    }

    //组装翻页器
    $html->set(
        '$pager',
        $html->pager(
            \fGet('page', 1),
            $stockCount,
            $GLOBALS['setting']['pager']['warehouse']['itemsPerPage'],
            '?page={?$page?}'
        )
    );
} else {
    $html->set('$emptyWarehouseGuide', $html->readTpl('guide/warehouseEmpty.html'));
    $html->set('$itemList', '');
    $html->set('$pager', '');
}



$html->output();

\fDie();
?>