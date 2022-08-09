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

### 产品方案

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






## Web3 Jam 期间的开发规划

Testnet 合约[地址](https://flow-view-source.com/testnet/account/0x457f3685a6f38813)

测试版本[地址](https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia) https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia


*Cadence合约*

- [ √ ] NFT标准合约实现

*客户端*

- [ √ ] FCL接入
- [ √ ] Club Space
- [ √ ] NFT Club 信息聚合中间件
- [ √ ] Web3 即时通讯
- [ √ ] NFT Club运营工具


## 团队成员

| 姓名 Name | 角色 Role     | 个人经历 Bio | 联系方式 Contact     |
| ---- | ------------------- | --- | ----------------------- |
| Yann | Full Stack Developer | 15+ years of experience in cloud computing, big data and large-scale distributed systems. |  yann.ren@relationlabs.ai  |
|  Ben | Full Stack Developer | former core development engineer of IBM, AWS.10+ years of back-end development experience |  j.chang@relationlabs.ai  |
| Jessica | Economic Designer | Crypto and commodity derivatives trader,5+ years of blockchain research experience,CFA, ACCA,Visiting researcher at Imperial College |  b.zhang@relationlabs.ai |
| Joe | Product Manager/Engineer | former product leader of top blockchain companies, 6+ years of project experience in finance, logistics, social networking, games products | pikajoe@relationlabs.ai  |
| Santry | Marketing |Former CMO at Patract Labs,9+ years of marketing, operation and management experiences |  santry@relationlabs.ai |            |




## Deck & Demo

-Deck:

-Demo:
