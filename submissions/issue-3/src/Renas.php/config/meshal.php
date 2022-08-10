<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# MESHAL设置相关参数
################################################

# 版本信息
$GLOBALS['meshal']['version']['character'] = '0.2.0';
$GLOBALS['meshal']['version']['adventure'] = '0.2.0';

# 肖像设置
$GLOBALS['meshal']['portrait']['width'] = 513;
$GLOBALS['meshal']['portrait']['height'] = 738;
$GLOBALS['meshal']['item']['width'] = 513;
$GLOBALS['meshal']['item']['height'] = 738;

# Epoch相关
$GLOBALS['setting']['epoch']['begin'] = 1654041600; //Epoch开始时间
$GLOBALS['setting']['epoch']['span'] = 86400; //Epoch周期
$GLOBALS['setting']['period']['epochPerPeriod'] = 14;
$GLOBALS['setting']['period']['epochOffset'] = 3; //从第几个Epoch开始统计，这是为了贴合每周统计使用的

# Automator相关
$GLOBALS['setting']['automator']['balancePerBatch'] = 12; //每次处理多少个链上存取上账数据
$GLOBALS['setting']['automator']['facilityPerBatch'] = 30; //每次处理多少个facility建造数据
$GLOBALS['setting']['automator']['stakingPerBatch'] = 30; //每次处理多少个staking上账
$GLOBALS['setting']['automator']['incentivePerBatch']['adventure'] = 10; //每次处理多少个冒险激励

# 功能性设置
$GLOBALS['setting']['character']['generateInterval'] = 0; //生成角色的时间间隔
$GLOBALS['setting']['character']['recoverInterval'] = 1; //角色恢复至满的周期，以秒为单位

$GLOBALS['setting']['facility']['buildSpeedFactor'] = 100; //建造加速因子

$GLOBALS['setting']['adventure']['adventureSpeedFactor'] = 10000; //冒险加速因子
$GLOBALS['setting']['adventure']['randomAdventureMin'] = 3; //随机冒险的数量（最小）
$GLOBALS['setting']['adventure']['randomAdventureMax'] = 6; //随机冒险的数量（最大）
$GLOBALS['setting']['adventure']['regenerateInterval'] = 60; //重新随机可选冒险的周期，以秒为单位
$GLOBALS['setting']['adventure']['strengthToleranceMax'] = 1.3; //冒险的实力要求（strengthMax）最高超过角色多少倍
$GLOBALS['setting']['adventure']['referenceAdventures'] = 3; //冒险参考log的数量


# 角色排序参数配置
$GLOBALS['setting']['character']['sort']['time'] = 100; //时间因子
$GLOBALS['setting']['character']['sort']['version'] = 5; //版本因子（新版本的加权）
$GLOBALS['setting']['character']['sort']['like'] = 2; //点赞因子
$GLOBALS['setting']['character']['sort']['bio'] = 4; //传记因子
$GLOBALS['setting']['character']['sort']['portrait'] = 7; //头像因子
$GLOBALS['setting']['character']['sort']['adventureCount'] = 30; //取几场冒险计算冒险因子
$GLOBALS['setting']['character']['sort']['adventures'] = 120; //冒险因子

# 角色槽位默认数量（免费可以获得的角色数量）
$GLOBALS['meshal']['character']['initialSlot'] = 0;

# 角色实力因子配置
$GLOBALS['meshal']['character']['strength']['attr'] = 3; //属性
$GLOBALS['meshal']['character']['strength']['protect'] = 2; //防护
$GLOBALS['meshal']['character']['strength']['immune'] = 30; //免疫
$GLOBALS['meshal']['character']['strength']['pr'] = 0.03; //感知范围
$GLOBALS['meshal']['character']['strength']['ms'] = 3; //移动速度
$GLOBALS['meshal']['character']['strength']['ap'] = 6; //机动
$GLOBALS['meshal']['character']['strength']['cc'] = 0.16; //负重

$GLOBALS['meshal']['character']['base']['attr'] = 4; //属性
$GLOBALS['meshal']['character']['base']['protect'] = 0; //防护
$GLOBALS['meshal']['character']['base']['pr'] = 100; //感知范围
$GLOBALS['meshal']['character']['base']['ms'] = 3; //移动速度
$GLOBALS['meshal']['character']['base']['ap'] = 3; //机动
$GLOBALS['meshal']['character']['base']['cc'] = 24; //负重

//物品携带时实力加成的额外倍数
$GLOBALS['meshal']['character']['carry']['attr'] = 3; //属性
$GLOBALS['meshal']['character']['carry']['protect'] = 3; //防护
$GLOBALS['meshal']['character']['carry']['immune'] = 2; //免疫
$GLOBALS['meshal']['character']['carry']['pr'] = 1.5; //感知范围
$GLOBALS['meshal']['character']['carry']['ms'] = 2; //移动速度
$GLOBALS['meshal']['character']['carry']['ap'] = 2; //机动
$GLOBALS['meshal']['character']['carry']['cc'] = 1.5; //负重

# 编辑器最大值设置，为null时表示不限制
$GLOBALS['meshal']['maxModifier']['attr'] = null; //属性最大修正
$GLOBALS['meshal']['maxModifier']['protect'] = null; //防护最大修正
$GLOBALS['meshal']['maxModifier']['ap'] = null; //机动最大修正
$GLOBALS['meshal']['maxModifier']['cc'] = null; //负重最大修正
$GLOBALS['meshal']['maxModifier']['pr'] = null; //感知距离最大修正
$GLOBALS['meshal']['maxModifier']['ms'] = null; //移动速度最大修正

$GLOBALS['meshal']['maxMultiplier']['attr'] = 10; //属性最大倍数
$GLOBALS['meshal']['maxMultiplier']['protect'] = 10; //防护最大倍数
$GLOBALS['meshal']['maxMultiplier']['ap'] = 10; //机动最大倍数
$GLOBALS['meshal']['maxMultiplier']['cc'] = 100; //负重最大倍数
$GLOBALS['meshal']['maxMultiplier']['pr'] = 100; //感知距离最大倍数
$GLOBALS['meshal']['maxMultiplier']['ms'] = 10; //移动速度最大倍数

# 攻击与防护对照关系
$GLOBALS['meshal']['attack']['p']['protection'] = 't'; //物理攻击对应物理防护
$GLOBALS['meshal']['attack']['e']['protection'] = 'e'; //侵蚀攻击对应侵蚀防护
$GLOBALS['meshal']['attack']['o']['protection'] = 'r'; //灵异攻击对应灵异防护
$GLOBALS['meshal']['attack']['r']['protection'] = null; //真实攻击无法防护
$GLOBALS['meshal']['attack']['p']['immunity'] = 'ip'; //物理攻击对应物理免疫
$GLOBALS['meshal']['attack']['e']['immunity'] = 'ie'; //侵蚀攻击对应侵蚀免疫
$GLOBALS['meshal']['attack']['o']['immunity'] = 'io'; //灵异攻击对应灵异免疫
$GLOBALS['meshal']['attack']['r']['immunity'] = null; //真实攻击无法免疫

# 随机角色生成相关设置
$GLOBALS['meshal']['generate'] = array();
    $GLOBALS['meshal']['generate']['species']['min'] = 1;
    $GLOBALS['meshal']['generate']['species']['dice'] = 0;
    $GLOBALS['meshal']['generate']['ethnicity']['min'] = 1;
    $GLOBALS['meshal']['generate']['ethnicity']['dice'] = 1;
    $GLOBALS['meshal']['generate']['faction']['min'] = 1;
    $GLOBALS['meshal']['generate']['faction']['dice'] = 0;
    $GLOBALS['meshal']['generate']['gender']['min'] = 1;
    $GLOBALS['meshal']['generate']['gender']['dice'] = 0;
    $GLOBALS['meshal']['generate']['size']['min'] = 1;
    $GLOBALS['meshal']['generate']['size']['dice'] = 0;
    $GLOBALS['meshal']['generate']['form']['min'] = 1;
    $GLOBALS['meshal']['generate']['form']['dice'] = 0;

    $GLOBALS['meshal']['generate']['m']['min'] = 1;
    $GLOBALS['meshal']['generate']['m']['dice'] = 6;
    $GLOBALS['meshal']['generate']['a']['min'] = 1;
    $GLOBALS['meshal']['generate']['a']['dice'] = 6;
    $GLOBALS['meshal']['generate']['s']['min'] = 1;
    $GLOBALS['meshal']['generate']['s']['dice'] = 6;

    $GLOBALS['meshal']['generate']['t']['min'] = -2;
    $GLOBALS['meshal']['generate']['t']['dice'] = 4;
    $GLOBALS['meshal']['generate']['e']['min'] = -2;
    $GLOBALS['meshal']['generate']['e']['dice'] = 4;
    $GLOBALS['meshal']['generate']['r']['min'] = -2;
    $GLOBALS['meshal']['generate']['r']['dice'] = 4;

    $GLOBALS['meshal']['generate']['pr']['min'] = 50;
    $GLOBALS['meshal']['generate']['pr']['diceNum'] = 4;
    $GLOBALS['meshal']['generate']['pr']['dicePt'] = 10;
    $GLOBALS['meshal']['generate']['ms']['min'] = 2;
    $GLOBALS['meshal']['generate']['ms']['dice'] = 3;

    $GLOBALS['meshal']['generate']['ap']['min'] = 2;
    $GLOBALS['meshal']['generate']['ap']['dice'] = 2;

# 消息类型设置
$GLOBALS['meshal']['messageType'] = array();

    #event
    $GLOBALS['meshal']['messageType']['event'] = array(
        'name' => 'messageType.event'
    );

    #reward
    $GLOBALS['meshal']['messageType']['reward'] = array(
        'name' => 'messageType.reward'
    );

    #balance
    $GLOBALS['meshal']['messageType']['balance'] = array(
        'name' => 'messageType.balance'
    );

    #adventure
    $GLOBALS['meshal']['messageType']['adventure'] = array(
        'name' => 'messageType.adventure'
    );

    #facility
    $GLOBALS['meshal']['messageType']['facility'] = array(
        'name' => 'messageType.facility'
    );

    #recruit
    $GLOBALS['meshal']['messageType']['recruit'] = array(
        'name' => 'messageType.recruit'
    );

    #like
    $GLOBALS['meshal']['messageType']['like'] = array(
        'name' => 'messageType.like'
    );

# 特征类型设置
$GLOBALS['meshal']['featureType'] = array();

    #物种
    $GLOBALS['meshal']['featureType']['species'] = array(
        'name' => 'featureType.species'
    );

    #族群
    $GLOBALS['meshal']['featureType']['ethnicity'] = array(
        'name' => 'featureType.ethnicity'
    );

    #阵营
    $GLOBALS['meshal']['featureType']['faction'] = array(
        'name' => 'featureType.faction'
    );

    #性别
    $GLOBALS['meshal']['featureType']['gender'] = array(
        'name' => 'featureType.gender'
    );

    #体型
    $GLOBALS['meshal']['featureType']['size'] = array(
        'name' => 'featureType.size'
    );

    #形态
    $GLOBALS['meshal']['featureType']['form'] = array(
        'name' => 'featureType.form'
    );

    #感知方式
    $GLOBALS['meshal']['featureType']['perception'] = array(
        'name' => 'featureType.perception'
    );

    #移动方式
    $GLOBALS['meshal']['featureType']['mobility'] = array(
        'name' => 'featureType.mobility'
    );

    #派系
    $GLOBALS['meshal']['featureType']['faction'] = array(
        'name' => 'featureType.faction'
    );

    #个性
    $GLOBALS['meshal']['featureType']['personality'] = array(
        'name' => 'featureType.personality'
    );

    #性取向
    $GLOBALS['meshal']['featureType']['sexuality'] = array(
        'name' => 'featureType.sexuality'
    );

    #癖好
    $GLOBALS['meshal']['featureType']['hobby'] = array(
        'name' => 'featureType.hobby'
    );

# 物品类型设置
$GLOBALS['meshal']['itemType'] = array();

    #武器
    $GLOBALS['meshal']['itemType']['weapon'] = array();
        //缠斗
        $GLOBALS['meshal']['itemType']['weapon']['wrestle'] = array(
            'name' => 'itemType.weapon.wrestle'
        );

        //刀剑
        $GLOBALS['meshal']['itemType']['weapon']['blade'] = array(
            'name' => 'itemType.weapon.blade'
        );

        //打击
        $GLOBALS['meshal']['itemType']['weapon']['strike'] = array(
            'name' => 'itemType.weapon.strike'
        );

        //挥甩
        $GLOBALS['meshal']['itemType']['weapon']['whip'] = array(
            'name' => 'itemType.weapon.whip'
        );

        //长柄
        $GLOBALS['meshal']['itemType']['weapon']['pole'] = array(
            'name' => 'itemType.weapon.pole'
        );

        //盾牌
        $GLOBALS['meshal']['itemType']['weapon']['shield'] = array(
            'name' => 'itemType.weapon.shield'
        );

        //弹道武器
        $GLOBALS['meshal']['itemType']['weapon']['projectile'] = array(
            'name' => 'itemType.weapon.projectile'
        );

        //射线武器
        $GLOBALS['meshal']['itemType']['weapon']['beam'] = array(
            'name' => 'itemType.weapon.beam'
        );

        //投掷
        $GLOBALS['meshal']['itemType']['weapon']['throwing'] = array(
            'name' => 'itemType.weapon.throwing'
        );

        //隔空武器
        $GLOBALS['meshal']['itemType']['weapon']['teleknesy'] = array(
            'name' => 'itemType.weapon.teleknesy'
        );

        //弹药
        $GLOBALS['meshal']['itemType']['weapon']['ammo'] = array(
            'name' => 'itemType.weapon.ammo'
        );

        //异种武器
        $GLOBALS['meshal']['itemType']['weapon']['exotic'] = array(
            'name' => 'itemType.weapon.exotic'
        );

        //天生武器
        $GLOBALS['meshal']['itemType']['weapon']['natural'] = array(
            'name' => 'itemType.weapon.natural'
        );

    #防具
    $GLOBALS['meshal']['itemType']['protectionWear'] = array();
        //头盔
        $GLOBALS['meshal']['itemType']['protectionWear']['helmet'] = array(
            'name' => 'itemType.protectionWear.helmet'
        );

        //护甲
        $GLOBALS['meshal']['itemType']['protectionWear']['armor'] = array(
            'name' => 'itemType.protectionWear.armor'
        );

        //鞋靴
        $GLOBALS['meshal']['itemType']['protectionWear']['boots'] = array(
            'name' => 'itemType.protectionWear.boots'
        );

        //鞍具
        $GLOBALS['meshal']['itemType']['protectionWear']['saddle'] = array(
            'name' => 'itemType.protectionWear.saddle'
        );

        //异种防具
        $GLOBALS['meshal']['itemType']['protectionWear']['exotic'] = array(
            'name' => 'itemType.protectionWear.exotic'
        );

        //天生防具
        $GLOBALS['meshal']['itemType']['protectionWear']['natural'] = array(
            'name' => 'itemType.protectionWear.natural'
        );

    #饰品
    $GLOBALS['meshal']['itemType']['accessory'] = array();
        //耳饰
        $GLOBALS['meshal']['itemType']['accessory']['eardrop']= array(
            'name' => 'itemType.accessory.eardrop'
        );

        //项链
        $GLOBALS['meshal']['itemType']['accessory']['necklace']= array(
            'name' => 'itemType.accessory.necklace'
        );

        //指环
        $GLOBALS['meshal']['itemType']['accessory']['ring']= array(
            'name' => 'itemType.accessory.ring'
        );

        //臂环
        $GLOBALS['meshal']['itemType']['accessory']['armband']= array(
            'name' => 'itemType.accessory.armband'
        );

        //手套
        $GLOBALS['meshal']['itemType']['accessory']['glove']= array(
            'name' => 'itemType.accessory.glove'
        );

        //足饰
        $GLOBALS['meshal']['itemType']['accessory']['footwear']= array(
            'name' => 'itemType.accessory.footwear'
        );

        //腰饰
        $GLOBALS['meshal']['itemType']['accessory']['waistband']= array(
            'name' => 'itemType.accessory.waistband'
        );

        //尾饰
        $GLOBALS['meshal']['itemType']['accessory']['tailpiece']= array(
            'name' => 'itemType.accessory.tailpiece'
        );

        //披肩
        $GLOBALS['meshal']['itemType']['accessory']['cape']= array(
            'name' => 'itemType.accessory.cape'
        );

        //服装
        $GLOBALS['meshal']['itemType']['accessory']['costume']= array(
            'name' => 'itemType.accessory.costume'
        );

        //妆饰
        $GLOBALS['meshal']['itemType']['accessory']['adornment']= array(
            'name' => 'itemType.accessory.adornment'
        );

        //配饰
        $GLOBALS['meshal']['itemType']['accessory']['attachment']= array(
            'name' => 'itemType.accessory.attachment'
        );

        //异种饰品
        $GLOBALS['meshal']['itemType']['accessory']['exotic']= array(
            'name' => 'itemType.accessory.exotic'
        );

    #其他物品
    $GLOBALS['meshal']['itemType']['misc'] = array();
        //食物
        $GLOBALS['meshal']['itemType']['misc']['food'] = array(
            'name' => 'itemType.misc.food'
        );

        //饮料
        $GLOBALS['meshal']['itemType']['misc']['drink'] = array(
            'name' => 'itemType.misc.drink'
        );

        //药物
        $GLOBALS['meshal']['itemType']['misc']['medicine'] = array(
            'name' => 'itemType.misc.medicine'
        );

        //文件
        $GLOBALS['meshal']['itemType']['misc']['document'] = array(
            'name' => 'itemType.misc.document'
        );

        //工具
        $GLOBALS['meshal']['itemType']['misc']['tool'] = array(
            'name' => 'itemType.misc.tool'
        );

        //附件
        $GLOBALS['meshal']['itemType']['misc']['addon'] = array(
            'name' => 'itemType.misc.addon'
        );

        //货币
        $GLOBALS['meshal']['itemType']['misc']['currency'] = array(
            'name' => 'itemType.misc.currency'
        );

        //材料
        $GLOBALS['meshal']['itemType']['misc']['ingredient'] = array(
            'name' => 'itemType.misc.ingredient'
        );

        //杂物
        $GLOBALS['meshal']['itemType']['misc']['sundry'] = array(
            'name' => 'itemType.misc.sundry'
        );

//装备位容器注册
$GLOBALS['meshal']['equipmentContainer'] = array();

    $GLOBALS['meshal']['equipmentContainer']['weapon'] = array(
        'name' => 'itemType.weapon',
        'type' => 'weapon'
    );

    // $GLOBALS['meshal']['equipmentContainer']['naturalWeapon'] = array(
    //     'name' => 'itemType.weapon.natural',
    //     'type' => 'naturalWeapon'
    // );

    $GLOBALS['meshal']['equipmentContainer']['helmet'] = array(
        'name' => 'itemType.protectionWear.helmet',
        'type' => 'helmet'
    );

    $GLOBALS['meshal']['equipmentContainer']['armor'] = array(
        'name' => 'itemType.protectionWear.armor',
        'type' => 'armor'
    );

    $GLOBALS['meshal']['equipmentContainer']['boots'] = array(
        'name' => 'itemType.protectionWear.boots',
        'type' => 'boots'
    );

    $GLOBALS['meshal']['equipmentContainer']['saddle'] = array(
        'name' => 'itemType.protectionWear.saddle',
        'type' => 'saddle'
    );

    $GLOBALS['meshal']['equipmentContainer']['exoticProtection'] = array(
        'name' => 'itemType.protectionWear.exotic',
        'type' => 'exoticProtection'
    );

    $GLOBALS['meshal']['equipmentContainer']['naturalProtection'] = array(
        'name' => 'itemType.protectionWear.natural',
        'type' => 'naturalProtection'
    );

    $GLOBALS['meshal']['equipmentContainer']['necklace'] = array(
        'name' => 'itemType.accessory.necklace',
        'type' => 'necklace'
    );

    $GLOBALS['meshal']['equipmentContainer']['ring'] = array(
        'name' => 'itemType.accessory.ring',
        'type' => 'ring'
    );

    $GLOBALS['meshal']['equipmentContainer']['armband'] = array(
        'name' => 'itemType.accessory.armband',
        'type' => 'armband'
    );

    $GLOBALS['meshal']['equipmentContainer']['glove'] = array(
        'name' => 'itemType.accessory.glove',
        'type' => 'glove'
    );

    $GLOBALS['meshal']['equipmentContainer']['footwear'] = array(
        'name' => 'itemType.accessory.footwear',
        'type' => 'footwear'
    );

    $GLOBALS['meshal']['equipmentContainer']['waistband'] = array(
        'name' => 'itemType.accessory.waistband',
        'type' => 'waistband'
    );

    $GLOBALS['meshal']['equipmentContainer']['tailpiece'] = array(
        'name' => 'itemType.accessory.tailpiece',
        'type' => 'tailpiece'
    );

    $GLOBALS['meshal']['equipmentContainer']['cape'] = array(
        'name' => 'itemType.accessory.cape',
        'type' => 'cape'
    );

    $GLOBALS['meshal']['equipmentContainer']['costume'] = array(
        'name' => 'itemType.accessory.costume',
        'type' => 'costume'
    );

    $GLOBALS['meshal']['equipmentContainer']['adornment'] = array(
        'name' => 'itemType.accessory.adornment',
        'type' => 'adornment'
    );

    $GLOBALS['meshal']['equipmentContainer']['attachment'] = array(
        'name' => 'itemType.accessory.attachment',
        'type' => 'attachment'
    );

    $GLOBALS['meshal']['equipmentContainer']['exoticAccessory'] = array(
        'name' => 'itemType.accessory.exotic',
        'type' => 'exoticAccessory'
    );

//特征容器注册器
$GLOBALS['meshal']['featureContainer'] = array();

    /**
     * 每个容器的属性规范：
     * name: 容器所使用的名称
     * type: 容器接受的特征类型
     * poor: 容器是否有另一个较弱对应特征的容器（比如perceptionPoor, mobilityPoor）为null表示无，否则用字符串记录较弱特征容器的名称
     * major: 如果这是一个较弱特征的容器，那么这里须设置它是哪一个特征容器的较弱级（比如perceptionPoor的major应当设为perception），非较弱级容器此处设为null
     * limit: 容器可接受的特征数量，为null时不限
     */


    $GLOBALS['meshal']['featureContainer']['species'] = array(
        'name' => 'featureType.species',
        'type' => 'species',
        'poor' => null,
        'major' => null,
        'limit' => 1
    );

    $GLOBALS['meshal']['featureContainer']['ethnicity'] = array(
        'name' => 'featureType.ethnicity',
        'type' => 'ethnicity',
        'poor' => null,
        'major' => null,
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['faction'] = array(
        'name' => 'featureType.faction',
        'type' => 'faction',
        'poor' => null,
        'major' => null,
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['gender'] = array(
        'name' => 'featureType.gender',
        'type' => 'gender',
        'poor' => null,
        'major' => null,
        'limit' => 1
    );

    $GLOBALS['meshal']['featureContainer']['size'] = array(
        'name' => 'featureType.size',
        'type' => 'size',
        'poor' => null,
        'major' => null,
        'limit' => 1
    );

    $GLOBALS['meshal']['featureContainer']['form'] = array(
        'name' => 'featureType.form',
        'type' => 'form',
        'poor' => null,
        'major' => null,
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['perception'] = array(
        'name' => 'featureType.perception',
        'type' => 'perception',
        'poor' => 'perceptionPoor',
        'major' => null,
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['perceptionPoor'] = array(
        'name' => 'featureType.perceptionPoor',
        'type' => 'perception',
        'poor' => null,
        'major' => 'perception',
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['mobility'] = array(
        'name' => 'featureType.mobility',
        'type' => 'mobility',
        'poor' => 'mobilityPoor',
        'major' => null,
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['mobilityPoor'] = array(
        'name' => 'featureType.mobilityPoor',
        'type' => 'mobility',
        'poor' => null,
        'major' => 'mobility',
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['personality'] = array(
        'name' => 'featureType.personality',
        'type' => 'personality',
        'poor' => null,
        'major' => null,
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['sexuality'] = array(
        'name' => 'featureType.sexuality',
        'type' => 'sexuality',
        'poor' => null,
        'major' => null,
        'limit' => null
    );

    $GLOBALS['meshal']['featureContainer']['hobby'] = array(
        'name' => 'featureType.hobby',
        'type' => 'hobby',
        'poor' => null,
        'major' => null,
        'limit' => null
    );



//稀有度描述
$GLOBALS['meshal']['rarity'] = array();
    
    //默认
    $GLOBALS['meshal']['rarity']['feature']['default'] = array(
        array(
            'min' => -1,
            'max' => 0.0015,
            'desc' => 'common.feature.rarity.extremelyRare',
            'style' => 'rarity-1'
        ),
        array(
            'min' => 0.0015,
            'max' => 0.005,
            'desc' => 'common.feature.rarity.veryRare',
            'style' => 'rarity-2'
        ),
        array(
            'min' => 0.005,
            'max' => 0.015,
            'desc' => 'common.feature.rarity.rare',
            'style' => 'rarity-3'
        ),
        array(
            'min' => 0.015,
            'max' => 0.05,
            'desc' => 'common.feature.rarity.uncommon',
            'style' => 'rarity-4'
        ),
        array(
            'min' => 0.05,
            'max' => 0.15,
            'desc' => 'common.feature.rarity.common',
            'style' => 'rarity-5'
        ),
        array(
            'min' => 0.15,
            'max' => null,
            'desc' => 'common.feature.rarity.majority',
            'style' => 'rarity-6'
        )
    );

    //物种
    $GLOBALS['meshal']['rarity']['feature']['species'] = array(
        array(
            'min' => -1,
            'max' => 0.00015,
            'desc' => 'common.feature.rarity.extremelyRare',
            'style' => 'rarity-1'
        ),
        array(
            'min' => 0.00015,
            'max' => 0.0005,
            'desc' => 'common.feature.rarity.veryRare',
            'style' => 'rarity-2'
        ),
        array(
            'min' => 0.0005,
            'max' => 0.0015,
            'desc' => 'common.feature.rarity.rare',
            'style' => 'rarity-3'
        ),
        array(
            'min' => 0.0015,
            'max' => 0.005,
            'desc' => 'common.feature.rarity.uncommon',
            'style' => 'rarity-4'
        ),
        array(
            'min' => 0.005,
            'max' => 0.015,
            'desc' => 'common.feature.rarity.common',
            'style' => 'rarity-5'
        ),
        array(
            'min' => 0.015,
            'max' => null,
            'desc' => 'common.feature.rarity.majority',
            'style' => 'rarity-6'
        )
    );

    //族群
    $GLOBALS['meshal']['rarity']['feature']['ethnicity'] = array(
        array(
            'min' => -1,
            'max' => 0.00015,
            'desc' => 'common.feature.rarity.extremelyRare',
            'style' => 'rarity-1'
        ),
        array(
            'min' => 0.00015,
            'max' => 0.0005,
            'desc' => 'common.feature.rarity.veryRare',
            'style' => 'rarity-2'
        ),
        array(
            'min' => 0.0005,
            'max' => 0.0015,
            'desc' => 'common.feature.rarity.rare',
            'style' => 'rarity-3'
        ),
        array(
            'min' => 0.0015,
            'max' => 0.005,
            'desc' => 'common.feature.rarity.uncommon',
            'style' => 'rarity-4'
        ),
        array(
            'min' => 0.005,
            'max' => 0.015,
            'desc' => 'common.feature.rarity.common',
            'style' => 'rarity-5'
        ),
        array(
            'min' => 0.015,
            'max' => null,
            'desc' => 'common.feature.rarity.majority',
            'style' => 'rarity-6'
        )
    );

    //阵营
    $GLOBALS['meshal']['rarity']['feature']['faction'] = array(
        array(
            'min' => -1,
            'max' => 0.0025,
            'desc' => 'common.feature.rarity.extremelyRare',
            'style' => 'rarity-1'
        ),
        array(
            'min' => 0.0025,
            'max' => 0.005,
            'desc' => 'common.feature.rarity.veryRare',
            'style' => 'rarity-2'
        ),
        array(
            'min' => 0.005,
            'max' => 0.0125,
            'desc' => 'common.feature.rarity.rare',
            'style' => 'rarity-3'
        ),
        array(
            'min' => 0.0125,
            'max' => 0.025,
            'desc' => 'common.feature.rarity.uncommon',
            'style' => 'rarity-4'
        ),
        array(
            'min' => 0.025,
            'max' => 0.05,
            'desc' => 'common.feature.rarity.common',
            'style' => 'rarity-5'
        ),
        array(
            'min' => 0.05,
            'max' => null,
            'desc' => 'common.feature.rarity.majority',
            'style' => 'rarity-6'
        )
    );

    //体型
    $GLOBALS['meshal']['rarity']['feature']['size'] = array(
        array(
            'min' => -1,
            'max' => null,
            'desc' => 'common.feature.rarity.common',
            'style' => 'rarity-5'
        )
    );

/**
 * 物品效果器的type定义
 * - 1 为modPositive
 * - 0 为modNegative
 * - null 为modSpecial
 */
$GLOBALS['meshal']['itemUsage'] = array(
    'addScore' => 1,
    'subScore' => 0
);

/**
 * 设施效果器的type定义
 */
$GLOBALS['meshal']['userEfx'] = array(
    'add' => 1,
    'sub' => 0
);

/**
 * 用户初始化时的初期配置
 */
$GLOBALS['meshal']['initialisation'] = array(
    'facilities' => array(
        'tent' => 1,
        'watchtower' => 1
    )
);
?>