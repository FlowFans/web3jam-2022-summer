# <Relation Club /Relationlabs>


## 赛道选择

请选择一项赛道类型进行报名：

- [ √ ] NFT x DAO/Tools - 组织工具

## 项目描述

### 项目背景（待解决的问题）

社群是NFT很重要的组成部分，当前NFT项目的社群组件和身份校验主要通过Discord及第三方机器人完成。而随着钓鱼攻击频发，NFT holder 的资产安全受到很大的危险。
另一方面，NFT的证明与NFT的社群在web3与web2的之间割裂。用户在Web3中用不同的地址hold不同的NFT，而在Web2种，
则只能看到一个个twitter/discord账号，运营不便的同时，也无法通过地址定位到一个个真实的用户。
Relation希望构建Web3原生的NFT社群运营工具NFT Club，它将帮助NFT创作者和NFT holders可以直接在web3中开展社群活动，这不仅能在黑暗森林中有效地保证用户资产安全性，而且还将便利创作者的运营，使得其可以开拓更多 Web3 Native的应用场景。

*目标用户*

NFT创作者&NFT持有者

*需求证明*
NFT社群领域安全问题频发:
1. 4月1日，BAYC（无聊猿）的Discord遭遇短暂黑客攻击
2. 5月23日，MEE6官方Discord遭受攻击，导致账号被盗，官方discord群里发布mint的钓鱼网站信息。
3. 2022年5月6日，NFT交易市场Opensea官方Discord遭受攻击

权属与社群的割裂已成事实，除少数愿意公开身份的KOL外，NFT创作者并不了解他的用户，仅能通过浏览器知晓对方的地址。


### 产品方案

*产品介绍*

Relation Club 是web3 native的社区运营工具。同时也是Relation为NFT持有者们创建的聚集地。它可以将拥有同一系列的NFT持有者们聚集在一起，持有该系列的NFT是加入Club的唯一凭证。

**Relation Club具有以下特性：**

1. 进出基于NFT的链上所有权，无需额外认证
2. 相对于传统NFT社群工具，提供更加安全的聊天服务和运营支持
3. Flow友好
4. 帮助创作者将地址映射到真实的用户
5. 丰富且有趣的运营工具

**Relation Club将包下述模块：**

- Club Space.Club space 是Club 的主页，将包含Club基本信息，linked page，Club news，Proposal，活跃用户和成员墙，是Club成员的聚集地.

![image](https://user-images.githubusercontent.com/91399393/183704985-c4f966d7-ce56-4b9d-b442-cb25bf86014e.png)

- Club Chat.Club chat是NFT Club的成员聊天群组，服务将根据合约的holders信息自动管理Club成员，Chat的IM协议将支持文字、图片、emoji、自定义表情、NFT分享、网页分享等消息格式。

- Club信息聚合中间件.负责收集和管理NFT holders的链上公开信息的infra，将为Club提供基础的服务支持，并可以自动完成成员管理，用户通过FCL登录Relation Club后，将根据用户地址验证用户是否持有某些NFT，它将被自动加入对应的Club，而根据Flow链上的权属更新，如果他失去了NFT，则将被移出Club，由新的持有者进入。

- Club合约标准.Relation nft 是relation labs 在flow chain 实现relation nft club 的标准实行 开发者可以参考实现自己flow nft 合约加入relation club 从而进入relation世界，实现艺术家与nft 持有者更充分的交流。

- Club运营工具.Club 提供一系列运营工具帮助NFT创作者更便捷、有趣的运营Club。

-Club统计.基于聚合的Web3地址，Club帮助创作者能将用户定位为人而非地址，更加全面的了解NFT用户群体，制定运营策略。这使得Club统计不仅可以提供On-chain的数据分析，还能提供基于真实用户个体的进阶数据统计。

![812feed524de4f80b29b3ea219f3bea](https://user-images.githubusercontent.com/91399393/183707938-3760a52b-deea-48b2-abf5-929fbaab36a1.jpg)



-Club合影
为了便于社区开展活动、创造符合NFT社区文化的社区内容。Relaiton Club提供了合影工具，允许holders实时进入同一个房间完成合影，提供有趣味、有传播力的出席证明工具。
![image](https://user-images.githubusercontent.com/91399393/183707256-07cac18d-206e-4908-a3da-c5a980b4f285.png)


*技术架构*


*产品LOGO*

![image](https://user-images.githubusercontent.com/91399393/183732375-7b93e1d0-968b-49e3-b84d-8ea1e9eea76f.png)

*运营策略*

- 帮助交易市场、NFT创作者构建Web3原生的社群，利用Club chat开展社区活动，更好的基于真实用户发放激励并获得数据反馈
- 通过Relation Club与NFT项目的联合激励，扩大社群影响力，增强社群增长，并以此增加用户、NFT创作者对Relation Club的黏性
- 积极响应社区建议，扩展Club的类型、运营工具的类型，开放端口激励社区基于Club构建社群工具
- 不断增强Club Infra的功能和可用性，积极与社区合约开发者合作，构建出Flow生态NFT的数据基础设施

## Web3 Jam 期间的开发规划

*Cadence合约*

- Club NFT标准的构思、定义与合约实现
- 测试王的NFT合约部署，[地址](https://flow-view-source.com/testnet/account/0x457f3685a6f38813/contract/RelationNFT)
- 用例NFT的mint及系统联通

关键实现

![image](https://user-images.githubusercontent.com/91399393/183738021-010bb837-16bb-4d94-b535-f785077c3fc4.png)

*服务端*

- 构建Flow Club Infra，实时根据链上的交易完成NFT权属信息的更新，进行Club成员管理.
- Flow资源聚合器，整合现有的数据平台及服务，汇总不同DAPP和市场中NFT中的metadata信息，并定期更新.
- Club Owner校验程序，通过校验用户签名是否是对应NFT的合约部署者，向用户开放管理权限及运营看板的浏览权限。


*客户端*

- 通过完成FCL的接入，链接Flow身份体系与Relation 统一身份认证的打通，使用户Flow地址的签名可以被验证并获得临时session key，作为身份认证.
- Club Space的web/Html5的UI界面，根据Flow Club Infra定期完成linked page，Club news，Proposal，活跃用户和成员墙的信息展示
- 聊天扩展程序对Flow NFT的发送及展示支持，Flow Club的 UI实现
- Club统计功能的UI及数据分析，Club统计功能将根据用户的聚合身份向合约部署者开放On chain和 On Relation的数据权限。包括平均的持有者、交易均价、每日的独立钱包统计、不同二级市场的销售情况统计、用户每日的活跃、Club聊天的统计、Club成员的资产分析及Club成员的明细。这些统计都将基于用户所有的账户而非仅仅单一的地址。
- Club合影工具的实现，包括Club合影工具的UI、合影房间创建的功能、基于websocket的在线状态检查、以及图片生成与分享的功能。

*交付材料*

- [Source Code](https://github.com/relationlabs/web3jam-2022-summer/tree/main/submissions/issue-27/src/cadence), deployed to Testnet address: 0x457f3685a6f38813
- [Pitch Deck](./docs/deck.pdf) <!-- or using online documentation url / ipfs url -->
- [Demo Video](./docs/demo.mp4) <!-- or using online documentation url / ipfs url -->
- [测试demo地址](https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia) https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia


## 团队成员

| 姓名 Name | 角色 Role     | 个人经历 Bio | 联系方式 Contact     |
| ---- | ------------------- | --- | ----------------------- |
| Yann | Full Stack Developer | 15+ years of experience in cloud computing, big data and large-scale distributed systems. |  yann.ren@relationlabs.ai  |
|  Ben | Full Stack Developer | former core development engineer of IBM, AWS.10+ years of back-end development experience |  j.chang@relationlabs.ai  |
| Jessica | Economic Designer | Crypto and commodity derivatives trader,5+ years of blockchain research experience,CFA, ACCA,Visiting researcher at Imperial College |  b.zhang@relationlabs.ai |
| Joe | Product Manager/Engineer | former product leader of top blockchain companies, 6+ years of project experience in finance, logistics, social networking, games products | pikajoe@relationlabs.ai  |
| Santry | Marketing |Former CMO at Patract Labs,9+ years of marketing, operation and management experiences |  santry@relationlabs.ai |            |



