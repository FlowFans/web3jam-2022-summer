# TIK8

***

## 目录索引

### 文档与演示材料："/docs"
#### PPT : "/docs/TIK8.pdf"

### FLOW配置文件："/flow"

### 项目文件："/src"
#### 合约文件："/src/cadence"
这里存放的是项目所有的合约文件，一共分为三类：1) Contracts 2) Transactions 3) Scripts

- **Contracts**："/src/cadence/contract"
  - NebulaActivity.cdc： 核心合约，包含需要创建的各类资源、接口与结构体定义
 
- **Transactions**："/src/cadence/transactions"
  - add_tickets_info.cdc: 增加一个活动的票务信息
  - create_activity.cdc: 用来创建一个活动到链上的transaction
  - purchase_ticket.cdc: 用来购买指定票的transaction
  - set_up_account.cdc: 用来初始化账户票夹和活动助手的transaction
  - verify_ticket.cdc: 用来核销票的transaction
- **Scripts**: "/src/cadence/scripts"\
用来查询各种数据的script,由于合约本身已经写好的对应的注释，故在给出的例子中只是援引了部分函数，具体可引用函数详情见 NebulaActivity.cdc

#### 前端文件："/src/ui"
- **html**："/src/ui/html"\
里面是UI的html形式

- **Transactions**："/src/cadence/transactions"\
里面是用next.js编写的一些UI


***

## 项目描述

星云用活动参与打造“兴趣人设”社交，为用户提供共建并参与活动的平台，让用户成为活动共创者。鼓励用户通过参与活动，获取NFT并打造自己的数字人设来进行社交。通过将NFT绑定实体权益，让用户可以在线下活动中也可以获取服务，实现线上和线下的联动。
项目设计了一种Social 2 Earn的体系，使得玩家在社交中可以获得激励，这些激励反过来又可以促进用户的社交！同时由于和许多知名活动方合作，积分也可以解锁更多隐藏福利和社群功能！
项目主要玩法可分为三步：
（1）Interest to Social: 通过兴趣活动交友
（2）Social to Earn: 社交参与获取激励
（3）Collect to KOL/KOC: 参与足迹累积成达人

### 项目背景（待解决的问题）

- 目标用户 Target audience
  Z世代和城市新白领（90-00后群体）

- 需求证明 Evidence for the need
    - **后疫情时代活动消费仍是刚需，调整结构释放消费潜力**\
      线上活动如演出、剧本杀等变现需要线上凭证（门票）实现。同时线下娱乐活动稳步恢复，2022Q1，全球演出市场票房收入10.9亿美元，恢复至2019年同期47%。
    - **Web3社交一片蓝海，尚待解锁**\
      目前web3仍在探索属于web3人的社交产品与社交方式。web3原住民普遍对于“真实性”社交以及全新的社交方式抱有期待。Nebula通过NFT打造DID，并在此基础上发展社交关系，利用Social to Earn的体系实现“激励式社交”，希望为Web3社交带去全新的思路和解决方案！
    - **Z世代的独特需求**\
      Z世代普遍崇尚悦己，追求质价比，喜好为体验消费。经常为打造自己的人设而消费，在社交与兴趣活动方面大量消费

### 产品方案

- 产品介绍
  Tik8 是一款票务NFT社交Dapp，通过NFT技术将门票数字化，产生了可消费、收藏以及核销的功能。用户购买和收藏票根的同时也打造了自己的kol人设，在数字世界产生新的价值。
- 技术架构
    - Cadence合约
        - Contracts
        - Transactions
        - Scripts
    - 前端UI
- 产品Logo\
  ![Logo](./TIK8Logo.png "Magic Gardens")
- 运营策略
    * **内核优化**\
  用户凭消费（数字藏品）进入限时性社群, 配合高互动性满足多元社交需求，提高社交效率和体验感, 释放传统票务缺失的社交价值。进入长期社群后, 通过成长体系和激励制度提高复购率和活跃度
    * **用户即渠道**\
        利用用户个人关系网扩张, 贯穿从线上（购票、社交、票根收藏展示）到线下（活动体验）的营销闭环
