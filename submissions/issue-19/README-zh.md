# <Melody>


## 赛道选择

请选择一项赛道类型进行报名：

- [x] NFT x DAO/Tools - 组织工具
- [ ] NFT x Game/Entertainment - 游戏娱乐
- [ ] NFT x Life/Metaverse - 生活方式

## 项目描述

为 DAO 组织或个人创建基于动态票据资产的周期性支付工具

### 项目背景

Web3 世界中越来越多的价值交互行为发生在区块链网络上，但基于区块链的支付方式仍然缺乏灵活与透明性，
价值交换不得不依赖中心化担保或合作方之间的信任完成，通过智能合约可以将支付双方的交互变得更加透明且去信任化。

Melody 的目标用户为需要通过区块链完成延时交易的用户，DAO 组织者、或使用延期代币交付的项目与等待代币释放的投资者。
通过智能合约完成周期性支付会更加透明且可预测，用 NFT 作为票据接收权益可以灵活转移与管理资产。

### 产品方案

_简介_
通过公开创建 Payment(Stream / Vesting) 的方式创建用户之间的周期支付，每个 Payment 都会给接收者发行 Ticket 作为类似于「票据」权益的 NFT 资产。
支付的创建者可以允许票据持有人转移和交易票据：例如 Vesting NFT 的风险转移，Salary 账户权益的迁移与风险对冲等。根据支付双方不同的诉求场景，支持创建者配置可撤回/不可撤回支付，和可转移/不可转移的支付票据。

使用动态 NFT 展示票据权益，票据 NFT 自身携带票据信息。

_技术架构_

使用 Cadence 实现所有产品逻辑，通过 Melody 主合约管理支付，引入标准 NFT 合约作为票据合约由 Melody 作为发行者管理。

- 产品 Logo 

![logo](https://trello.com/1/cards/62dd12a167854020143ccd01/attachments/62f0c3e7b0401e250f0a5199/previews/62f0c3e7b0401e250f0a51df/download/melody-logo.png)

- 运营策略
  - Melody 作为标准 NFT 资产可以轻松的被基础设施支持，完善动态 NFT 的展示效果
    - 钱包
    - 交易市场等合作
  - 在 DAO 组织中推广与应用
  - 与 Flow 生态中现有的 Defi 项目和协议进行整合

## Web3 Jam 期间的开发规划

Testnet 合约[地址](https://flow-view-source.com/testnet/account/0xb797a88390357df4)
测试版本[地址](https://testnet.melody.im/) https://testnet.melody.im/
deployed to Testnet address: 0xb797a88390357df4


_Cadence 合约_

- [x] NFT 标准合约实现与 Melody Ticket 业务定义逻辑
- [x] Stream / Vesting 创建，撤回配置等功能
- [x] 票据 NFT 发行、申领
- [x] Cadence 脚本编写与测试
- [x] 合约用户用例单元测试

_客户端_

- [x] UI design
- [x] FCL 接入
- [x] 创建 Stream
- [x] Stream 信息/票据展示
- [ ] 动态票据样式设计

_服务端_

- [ ] NFT 票据动态生成服务
- [x] 链上事件索引与查询服务整合

## 团队成员

| 姓名 Name | 角色 Role     | 个人经历 Bio | 联系方式 Contact                            |
| --------- | ------------- | ------------ | ------------------------------------------- |
| Caos      | Fullstack dev | ...          | github:Caosbad / email: caosbad@fn.services |
| Helen     | Designer      | ...          | email: 365135488@qq.com                     |
| Fei       | Operator      | ...          | email: fayhkbu@gmail.com                    |
