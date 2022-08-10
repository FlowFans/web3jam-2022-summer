# <Relation Club /Relationlabs>


## Track Selection


- [x] NFT x DAO/Tools - 组织工具

## Project Description

### Project Background 


NFT ownership is a kind of truth with unconditional trust.

Community building is essential for an NFT project. On the one hand, currently, normal NFT community operation tools, such as Discord, rely on third-party bot to verify members. In this case, frequent phishing attacks and other safety hazards expose user asset security to great danger.

On the other hand, in the current NFT project operation system, Web2 community operation tools are disconnected with Web3 NFT assets. Therefore, it’s impossible to accurately locate real NFT holders in Twitter and Discord, which leaves great barriers on the way of conducting community operation incentive activities accurately and efficiently.

Relation Club is a community operation tool based on Relation social services. Relation is a Web3 social graph infrastructure builder, providing services such as Unified Identity Verification, User Personal Data Management, Web3 IM Protocols, Web3 Social Graph, Data Toolkit, etc, to help developers build and deploy DApps more efficiently.


*Target Users*

- NFT Creators or Projectors
- NFT Holders


*Proof of Demand*
- Security incidents in NFT communities happen frequently:
1. On April 1, BAYC's (Bored Ape) Discord server was momentarily hacked
2. On May 23, MEE6's official Discord server was attacked. The official Discord account was stolen. And phishing sites for minting were posted in the official Discord server
3. On May 6, Opensea’s official Discord server was attacked
4. Lots of security incidents in NFT communities has happened and are still happening...

![Frame 1000003316](https://user-images.githubusercontent.com/91399393/183941100-8bc4fb37-7444-4550-9fc5-9fe70a9d346f.png)



- The disconnection between NFT communities and NFT assets:

    The NFT ownership and community membership has long been disconnected. Except for a few KOLs who are willing to disclose their real identities, NFT projectors find it hard to accurately locate and know more about NFT collection owners, with fragmented and vague user information only.
    ![Frame 1000003294](https://user-images.githubusercontent.com/91399393/183894671-b82f68f7-8a7d-4ac9-8c25-b1266daeaba7.png)



### Product Plans

*Product Introduction*

Relation Club is a chain-native NFT community operation tool. It will help NFT creators and NFT holders directly carry out community activities in the Web3 world, which will not only effectively ensure user asset security, but will also facilitate the community operation for NFT creators. With Relation Club, the exploration of more Web3 native NFT application scenarios will be possible.

The chain-native feature makes Relation Club particularly secure. In Relation Club, identity verification does not rely on a third-party Bot any more, which effectively prevents phishing risks.

In addition, Relation provides sophisticated operational tools that enables the NFT projectors to efficiently reach NFT holders and continuously track user behaviors.

**Relation Club has the following features:**

- Flow-friendly. Supportive of all mainstream NFT collections on the Flow blockchain
- Helpful for NFT creators to map on-chain addresses to real users and  reach NFT holders efficiently
- Providing Web3-based services such as Identity verification, IM, Social Graph, etc
- Containing various and interesting community operation tools 
- NFT rights de facto. Entrance and exit of Clubs will be based on the NFT ownership on chain, without additional verification process

**Relation Club includes the following modules:**

- **Club Space**

    Club Space is the Club's home page, containing Club basic info, linked Pages, Club News, Proposals, Active Users and Club Membership Wall, gathering all the Club members.

![club homepage (5)](https://user-images.githubusercontent.com/91399393/183921942-8ae2378c-06f1-4a01-b1dd-27034badb261.jpg)


- **Club Chat**

    Club Chat is an NFT Club chat group for Club members. Club members could be automatically managed based on contract holder information. The Chat's IM protocol will support text, images, emoji, custom stickers, NFT sharing, website link sharing and other message formats.
    
    ![Frame 1000003293](https://user-images.githubusercontent.com/91399393/183894798-f7d4a222-18d5-4475-bef7-95ebbfdee4cb.png)


- **Club Information Aggregation Middleware**

    The Infra, which is responsible for collecting and managing on-chain public information of NFT holders, will provide basic service support for the Club and will automatically complete membership management. After a user logging in to the Relation Club through the FCL, he/she will be verified on whether he/she holds some certain NFTs. If the user holds an NFT, then he/she will be automatically added to the corresponding Club. And according to the NFT ownership updated on the Flow blockchain, if the user does not own this NFT any more, he/she will be removed from the Club and replaced by a new holder.

- **New Club Contract**

    The New Club Contract is a smart contract for creating Club NFTs. The New Club Contract follows Flow’s NFT creation standard, helping NFT projectors finish the whole process of Contract Deployment, NFT Minting, and Club Launch directly with Relation Club UI.


- **Club Statistics**

    Based on aggregated Web3 addresses, Relation Club helps NFT creators locate users as people rather than addresses, thus creators could gain a more comprehensive understanding of the NFT user base, and develop operational strategies. This allows Club Statistics to provide not only on-chain data analysis, but also advanced statistics based on real users.

![club homepage (6)](https://user-images.githubusercontent.com/91399393/183922360-d77f3ea5-fa94-40a7-a787-1560183c7a63.jpg)




- **符合项目社区文化的趣味工具**

    为了便于社区开展活动，Club推出了一系列运营工具帮助NFT创作者更便捷、更有趣的运营Club，创造符合NFT社区文化的社区内容。

    例如：PFP合影工具，这将允许NFT持有实时进入同一个房间完成合影，提供有趣味、有传播力的出席证明工具。
    
![club homepage (1)](https://user-images.githubusercontent.com/91399393/183905910-a1c13282-762a-44eb-84f1-433d99c3dced.jpg)



*技术架构*

![Frame 1000003297 (1)](https://user-images.githubusercontent.com/91399393/183940677-fd1e4589-7ee3-42ec-aa74-90395f1e5d2d.png)



*产品LOGO*

![0 5](https://user-images.githubusercontent.com/91399393/183939657-c03171f3-a950-4eec-b032-fd7d7a6b7af1.png)

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
- 实现Club统计功能的UI及数据分析，Club统计功能将根据用户的聚合身份向合约部署者开放On chain和Relation服务的数据权限。包括平均的持有者、交易均价、每日的独立钱包统计、不同二级市场的销售情况统计、用户每日的活跃、Club聊天的统计、Club成员的资产分析及Club成员的明细。这些统计都将基于用户所有的账户而非仅仅单一的地址。
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



