# <Relation Club /Relationlabs>


## 赛道选择

请选择一项赛道类型进行报名：

- [x] NFT x DAO/Tools - 组织工具

## 项目描述

### 项目背景（待解决的问题）


NFT的所有权，理应是一种无需证明的事实。

社群是NFT很重要的组成部分，在当前的NFT社群运营工具(如discord)没有原生NFT的集成，社群的身份证明依赖第三方bot的集成，从而产生了安全隐患。随着钓鱼攻击频发，NFT持有者的资产安全受到很大的威胁。

另一方面，目前的NFT运营体系中社群工具与资产分隔在Web2与Web3，项目方只能看到一个个twitter/discord账号，无法精准的找到Owner用户，这使得运营激励活动即不精准也不高效。

Relation Club是基于Relation的服务构建的社群运营工具。Relation是基于Web3构建social graph的社交基础设施，提供统一身份认证、用户个人数据管理工具、web3 IM协议、Web3 social Graph，Data toolkit等工具帮助开发者高效开发及部署DAPP。


*目标用户*

- NFT创作者或项目方
- NFT持有者


*需求证明*
- NFT社群领域安全问题频发:
1. 4月1日，BAYC（无聊猿）的Discord遭遇短暂黑客攻击。
2. 5月23日，MEE6官方Discord遭受攻击，导致账号被盗，官方discord群里发布mint的钓鱼网站信息。
3. 5月6日，NFT交易市场Opensea官方Discord遭受攻击。
4. 更多的NFT安全事件......

![Frame 1000003316](https://user-images.githubusercontent.com/91399393/183941144-afec3976-792d-4087-bd66-87dd6109bde7.png)


- NFT社群与资产持有的割裂：

    权属与社群的割裂已成常态，除少数愿意公开身份的KOL外，NFT项目方无法了解其用户，仅仅能通过拼凑的信息模糊的分析用户，无法精准的查找到Owner用户。
    ![Frame 1000003294](https://user-images.githubusercontent.com/91399393/183894671-b82f68f7-8a7d-4ac9-8c25-b1266daeaba7.png)



### 产品方案

*产品介绍*

Relation Club是Chain native的NFT社群运营工具，它将帮助NFT创作者和NFT 持有者在web3中直接开展社群活动，这不仅能在有效地保证用户资产安全性，而且还将便利创作者的社群运营，可以开拓更多Web3 Native的NFT应用场景。

Chain Native的特性为Relation Club带来独特的安全性，社群身份验证无需依赖第三方Bot，有效防范了钓鱼风险。

同时，Relation Club提供精细化运营工具，帮助项目方高效触达NFT持有者，并能对用户的行为进行持续的追踪。

**Relation Club具有以下特性：**

- Flow友好，支持Flow所有主流NFT collection
- NFT权益事实化，进入、离开Club基于NFT的链上所有权，无需额外认证
- 帮助创作者将地址映射到真实的用户,高效触达NFT持有者
- 提供基于Web3的身份认证、IM、社交关系图谱等基础服务
- 丰富且有趣社群运营小工具

**Relation Club将包下述模块：**

- **Club Space**

    Club Space是Club的主页，包含Club基本信息，linked Page，Club News，Proposal，活跃用户和成员墙。

![club homepage (5)](https://user-images.githubusercontent.com/91399393/183921942-8ae2378c-06f1-4a01-b1dd-27034badb261.jpg)


- **Club Chat**

    Club Chat是NFT Club的成员聊天群组，服务将根据合约的持有者信息自动管理Club成员，Chat的IM协议将支持文字、图片、emoji、自定义表情、NFT分享、网页分享等消息格式。
    
    ![Frame 1000003293](https://user-images.githubusercontent.com/91399393/183894798-f7d4a222-18d5-4475-bef7-95ebbfdee4cb.png)


- **Club信息聚合中间件**

    负责收集和管理NFT持有者的链上公开信息的infra，将为Club提供基础的服务支持，并可以自动完成成员管理，用户通过FCL登录Relation Club后，将根据用户地址验证用户是否持有某些NFT，它将被自动加入对应的Club，而根据Flow链上的权属更新，如果他失去了NFT，则将被移出Club，由新的持有者进入。

- **New Club合约**

    New Club合约是用于创建Club NFT的智能合约。New Club合约遵循自Flow的NFT创建标准，帮助项目方直接通过Relation Club的UI直接完成从合约部署、NFT mint到Club上线的全过程。


- **Club统计**

    基于聚合的Web3地址，Club帮助创作者能将用户定位为人而非地址，更加全面的了解NFT用户群体，制定运营策略。这使得Club统计不仅可以提供On-chain的数据分析，还能提供基于真实用户个体的进阶数据统计。

![club homepage (6)](https://user-images.githubusercontent.com/91399393/183922360-d77f3ea5-fa94-40a7-a787-1560183c7a63.jpg)




- **符合项目社区文化的趣味工具**

    为了便于社区开展活动，Club推出了一系列运营工具帮助NFT创作者更便捷、更有趣的运营Club，创造符合NFT社区文化的社区内容。

    例如：PFP合影工具，这将允许NFT持有实时进入同一个房间完成合影，提供有趣味、有传播力的出席证明工具。
    
![club homepage (1)](https://user-images.githubusercontent.com/91399393/183905910-a1c13282-762a-44eb-84f1-433d99c3dced.jpg)



*技术架构*

![中文](https://user-images.githubusercontent.com/91399393/183940878-b8de5f3d-9817-4167-b0ee-41b3bbcf220e.png)


*产品LOGO*

![0 5](https://user-images.githubusercontent.com/91399393/183939943-e061cf8c-6079-4ddb-a992-5fa4601b9724.png)


*运营策略*


- 与NFT市场、项目方合作创建Web3原生社群，利用Relation Club的数据功能，更好的了解用户需求，优化产品功能迭代
- 通过合作构建Club，增强Club的在NFT社区影响力。并以此增加用户黏性
- 横向拓展不同类型的Club，丰富Club运营工具的类型，激励社区共建Club运营工具
- 丰富Club Infra的数据深度和可用性，构建Flow生态NFT的数据基础设施

## Web3 Jam 期间的开发规划

*Cadence合约*

- 定义New Club的架构与关键字段
- 实现用户自主创建NFT合约、mint NFT的功能
- 部署测试网[合约](https://flow-view-source.com/testnet/account/0x457f3685a6f38813/contract/RelationNFT)
- 联通Infra与New Club合约，使用户自主创建的NFT可以第一时间生成Club
- 关键实现
<img width="912" alt="111" src="https://user-images.githubusercontent.com/91399393/183896194-aba9471c-2ac4-49f1-841e-49d7ec5e8671.png">


*服务端*

- 实现Flow Club Infra，根据链上交易实时完成NFT权属信息的更新，进行Club成员管理。
- 实现Flow资源聚合器，整合现有的数据平台及服务，汇总不同DAPP和市场中NFT中的metadata信息并定期更新。
- 实现Club Owner校验程序，通过校验用户签名是否是对应NFT的合约部署者，向用户开放管理权限及运营看板的浏览权限。

*客户端*

- 接入FCL，实现Flow身份体系与Relation统一身份认证的打通，使用户Flow地址的签名可以被验证并获得临时session key，作为身份认证。
- 定义Club Space的web/Html5的UI界面，根据Flow Club Infra定期完成linked page，Club news，Proposal，活跃用户和成员墙的信息展示。
- 实现聊天扩展程序对Flow NFT的发送及展示支持，Flow Club的UI。
- 实现Club统计功能的UI及数据分析，Club统计功能将根据用户的聚合身份向合约部署者开放On chain和Relation服务的数据权限。包括持有者的平均数量、交易均价、每日的独立钱包统计、不同二级市场的销售情况统计、用户每日的活跃、Club聊天的统计、Club成员的资产分析及Club成员的明细。这些统计都将基于用户所有的账户而非仅仅单一的地址。
- 实现Club社区工具的用例——合影工具，包括Club合影工具的UI、合影房间创建的功能、在线状态检查、以及图片生成与分享的功能。

*交付材料*

- [Source Code](https://github.com/relationlabs/web3jam-2022-summer/tree/main/submissions/issue-27/src/cadence), deployed to Testnet address: 0x457f3685a6f38813
- [Pitch Deck](https://docsend.com/view/htfb4dzi55ey794j) 
- [Demo Video](https://3fypb-gqaaa-aaaag-aaedq-cai.ic1.io/banner/relationClub.mp4) 
- [测试demo地址](https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia) https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia


## 团队成员

| 姓名 Name | 角色 Role     | 个人经历 Bio | 联系方式 Contact     |
| ---- | ------------------- | --- | ----------------------- |
| Jessica | Economic Designer | Crypto and commodity derivatives trader,5+ years of blockchain research experience,CFA, ACCA,Visiting researcher at Imperial College |  j.chang@relationlabs.ai |
| Joe | Product Manager/Engineer | former product leader of top blockchain companies, 6+ years of project experience in finance, logistics, social networking, games products | pikajoe@relationlabs.ai  |
| Santry | Marketing |Former CMO at Patract Labs,9+ years of marketing, operation and management experiences |  santry@relationlabs.ai | 
| Yann | Full Stack Developer | 15+ years of experience in cloud computing, big data and large-scale distributed systems. |  yann.ren@relationlabs.ai  |
|  Ben | Full Stack Developer | former core development engineer of IBM, AWS.10+ years of back-end development experience |  b.zhang@relationlabs.ai  |           |



