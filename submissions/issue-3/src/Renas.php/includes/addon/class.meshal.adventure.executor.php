<?php
namespace meshal\adventure;

################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供Meshal遭遇检查器的类
################################################

/*
常用方法

向队伍每个成员增加潜能
addPotentialityToTeam(
    int $baseAmount = 0, //基本增加量
    int $dicePt = 1, //随机增加量（计算点数的骰子数）
    int $diceNum = 0, //随机增加量（计算骰面的骰子数）
)

向冒险的所有关联角色添加潜能
addPotentialityToRelChar(
    int $baseAmount = 1, //基本增加量
    int $dicePt = 0, //随机增加量（计算点数的骰子数）
    int $diceNum = 0 //随机增加量（计算骰面的骰子数）
)

向冒险的关联成功角色添加潜能
addPotentialityToRelSuccess(
    int $baseAmount = 1, //基本增加量
    int $dicePt = 0, //随机增加量（计算点数的骰子数）
    int $diceNum = 0 //随机增加量（计算骰面的骰子数）
)

向冒险的关联失败角色添加潜能
addPotentialityToRelFailure(
    int $baseAmount = 1, //基本增加量
    int $dicePt = 0, //随机增加量（计算点数的骰子数）
    int $diceNum = 0 //随机增加量（计算骰面的骰子数）
)

向队伍中的随机成员添加物品
giveItemToRandomMember(
    ...$lootList //每个参数是一个子数组，为一种物品的掉落配置，格式为：
        $lootList[] = array(
            string itemName：物品name
            int probability：可能掉落此物品的概率权重
            int baseAmount：掉落此物品时的基本数量
            int dicePt：决定此物品数量的点数掷骰数
            int diceNum：决定此物品数量的骰面掷骰数
        )
)


*/
class xExecutor
{
    function __construct(
        \meshal\xAdventure &$parent
    ) {
        $this->parent = $parent; //父级冒险对象
        $this->team = $parent->team; //存储队伍信息
        $this->dice = $parent->dice; //掷骰器
    }

    /**
     * 向队伍每个成员增加潜能
     * 
     * @param int $baseAmount = 0
     * 基本增加量
     * 
     * @param int $dicePt = 1
     * 增加量（计算点数的骰子数）
     * 
     * @param int $diceNum = 0
     * 增加量（计算骰面的骰子数）
     * 
     * @return array
     * 返回增加量细节
     */
    public function addPotentialityToTeam (
        int $baseAmount = 0,
        int $dicePt = 1,
        int $diceNum = 0
    ) {
        $log = array();
        foreach ($this->team->members as $charId => $char) {
            //计算增加数量
            $amountPt = $this->dice->ptDetail($dicePt);
            $amountNum = $this->dice->numDetail($diceNum);
            $amount = $amountPt['result'] + $amountNum['result'] + $baseAmount;
            $char->strength->add(
                'pp',
                $amount
            );
            
            \fLog("Character({$charId})'s pp += {$amount}");
            $this->parent->addLedgerPotentiality($charId, $amount);
            $log[$charId] = array(
                'ptRoll' => $amountPt,
                'numRoll' => $amountNum,
                'baseAmount' => $baseAmount,
                'result' => $amount
            );
        }

        //记录log
        return array(
            'params' => array(
                'baseAmount' => $baseAmount,
                'dicePt' => $dicePt,
                'diceNum' => $diceNum
            ),
            'result' => $log
        );
    }

    /**
     * 向冒险的所有关联角色添加潜能
     * 
     * @param int $baseAmount = 0
     * 基本增加量
     * 
     * @param int $dicePt = 1
     * 增加量（计算点数的骰子数）
     * 
     * @param int $diceNum = 0
     * 增加量（计算骰面的骰子数）
     * 
     * @return array
     * 返回增加量细节
     */
    public function addPotentialityToRelChar (
        int $baseAmount = 1,
        int $dicePt = 0,
        int $diceNum = 0
    ) {
        $log = array();
        foreach ($this->parent->relChar as $k => $charId) {
            //计算增加数量
            $amountPt = $this->dice->ptDetail($dicePt);
            $amountNum = $this->dice->numDetail($diceNum);
            $amount = $amountPt['result'] + $amountNum['result'] + $baseAmount;
            $this->parent->team->members[$charId]->strength->add(
                'pp',
                $amount
            );
            \fLog("Character({$charId})'s pp += {$amount}");
            $this->parent->addLedgerPotentiality($charId, $amount);
            $log[$charId] = array(
                'ptRoll' => $amountPt,
                'numRoll' => $amountNum,
                'baseAmount' => $baseAmount,
                'result' => $amount
            );
        }

        //记录log
        return array(
            'params' => array(
                'baseAmount' => $baseAmount,
                'dicePt' => $dicePt,
                'diceNum' => $diceNum
            ),
            'result' => $log
        );
    }

    /**
     * 向冒险的关联成功角色添加潜能
     * 
     * @param int $baseAmount = 0
     * 基本增加量
     * 
     * @param int $dicePt = 1
     * 增加量（计算点数的骰子数）
     * 
     * @param int $diceNum = 0
     * 增加量（计算骰面的骰子数）
     * 
     * @return array
     * 返回增加量细节
     */
    public function addPotentialityToRelSuccess (
        int $baseAmount = 1,
        int $dicePt = 0,
        int $diceNum = 0
    ) {
        $log = array();
        foreach ($this->parent->relSuccess as $k => $charId) {
            //计算增加数量
            $amountPt = $this->dice->ptDetail($dicePt);
            $amountNum = $this->dice->numDetail($diceNum);
            $amount = $amountPt['result'] + $amountNum['result'] + $baseAmount;
            $this->team->members[$charId]->strength->add(
                'pp',
                $amount
            );
            \fLog("Character({$charId})'s pp += {$amount}");
            $this->parent->addLedgerPotentiality($charId, $amount);
            $log[$charId] = array(
                'ptRoll' => $amountPt,
                'numRoll' => $amountNum,
                'baseAmount' => $baseAmount,
                'result' => $amount
            );
        }

        //记录log
        return array(
            'params' => array(
                'baseAmount' => $baseAmount,
                'dicePt' => $dicePt,
                'diceNum' => $diceNum
            ),
            'result' => $log
        );
    }

    /**
     * 向冒险的关联失败角色添加潜能
     * 
     * @param int $baseAmount = 0
     * 基本增加量
     * 
     * @param int $dicePt = 1
     * 增加量（计算点数的骰子数）
     * 
     * @param int $diceNum = 0
     * 增加量（计算骰面的骰子数）
     * 
     * @return array
     * 返回增加量细节
     */
    public function addPotentialityToRelFailure (
        int $baseAmount = 1,
        int $dicePt = 0,
        int $diceNum = 0
    ) {
        $log = array();
        foreach ($this->parent->relFailure as $k => $charId) {
            //计算增加数量
            $amountPt = $this->dice->ptDetail($dicePt);
            $amountNum = $this->dice->numDetail($diceNum);
            $amount = $amountPt['result'] + $amountNum['result'] + $baseAmount;
            $this->team->members[$charId]->strength->add(
                'pp',
                $amount
            );
            \fLog("Character({$charId})'s pp += {$amount}");
            $this->parent->addLedgerPotentiality($charId, $amount);
            $log[$charId] = array(
                'ptRoll' => $amountPt,
                'numRoll' => $amountNum,
                'baseAmount' => $baseAmount,
                'result' => $amount
            );
        }

        //记录log
        return array(
            'params' => array(
                'baseAmount' => $baseAmount,
                'dicePt' => $dicePt,
                'diceNum' => $diceNum
            ),
            'result' => $log
        );
    }

    /**
     * 队伍中的随机成员获得物品
     * 
     * @param array ...$lootList
     * 每个参数是一个数组，对应一种物品的掉落参数，参数依次为：
     * - string itemName：物品name
     * - int probability：可能掉落此物品的概率权重
     * - int baseAmount：掉落此物品时的基本数量
     * - int dicePt：决定此物品数量的点数掷骰数
     * - int diceNum：决定此物品数量的骰面掷骰数
     */
    public function giveItemToRandomMember (
        ...$lootList
    ) {
        //随机决定获得哪类物品
        $random = array();
        foreach($lootList as $k => $lootData) { //组装供随机用的数组
            $random[$k] = $lootData['probability'];
        }
        $lootResult = fArrayRandWt($random); //获得随机结果
        \fLog("Random loot generated: {$lootResult[0]}");

        //决定获得物品的数量
        if(!empty($lootResult)) {
            //从遭遇数据中取物品相关的掉落资料
            $lootParams = $lootList[$lootResult[0]];
            \fLog("Loot data: ".\fDump($lootParams));

            //计算获得数量
            $lootAmntPt = $this->dice->ptDetail($lootParams['dicePt']);
            $lootAmntNum = $this->dice->numDetail($lootParams['diceNum']);
            $lootAmnt = $lootAmntPt['result'] + $lootAmntNum['result'] + $lootParams['baseAmount'];
            \fLog("lootAmnt = Pt({$lootAmntPt['result']}) + Num({$lootAmntNum['result']}) + Base({$lootParams['baseAmount']})");

            //给随机队伍成员分配物品
            $member = $this->team->getRandMember(1);
            if($lootAmnt > 0) {
                $stat = $this->team->members[$member[0]]->inventory->acquire($lootParams['itemName'], $lootAmnt, false);

                if($stat == 3) {
                    $lootStat = 'overload';
                } else {
                    $lootStat = null;
                    $this->parent->addLedgerItem($member[0], $lootParams['itemName'], $lootAmnt);
                }
            }
            
            //记录log
            return array(
                'params' => $lootParams,
                'result' => array(
                    'character' => array($member[0] => $lootStat),
                    'item' => array(
                        'itemName' => $lootParams['itemName'],
                        'lootAmnt' => $lootAmnt,
                        'lootAmntPt' => $lootAmntPt,
                        'lootAmntNum' => $lootAmntNum
                    )
                )
            );
        }
    }

    /**
     * 向关联失败角色造成一次攻击
     */
    public function attackRelFailure (
        string $attackType,
        string $targetAttr,
        int $baseAmount = 1,
        int $dicePt = 0,
        int $diceNum = 0
    ) {
        $log = array();
        $attackTypes = explode('&', $attackType);
        $targetAttrs = explode('&', $targetAttr);
        foreach ($this->parent->relFailure as $k => $charId) {
            $log[$charId] = \meshal\xAttack::attack(
                $this->team->members[$charId],
                $attackTypes,
                $targetAttrs,
                $dicePt,
                $diceNum,
                $baseAmount
            );
        }
        //记录log
        return array(
            'params' => array(
                'attackType' => $attackTypes,
                'targetAttr' => $targetAttrs,
                'baseAmount' => $baseAmount,
                'dicePt' => $dicePt,
                'diceNum' => $diceNum
            ),
            'result' => $log
        );
    }
}
?>