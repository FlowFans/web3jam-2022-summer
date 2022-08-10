# <LearntVerse>

## 赛道选择

请选择一项赛道类型进行报名：

- [ ] NFT x DAO/Tools - 组织工具
- [ ] NFT x Game/Entertainment - 游戏娱乐
- [x] NFT x Life/Metaverse - 生活方式

## 项目描述

### 项目背景（待解决的问题）

LearntVerse是一个Learn-to-Earn的社交网络平台，旨在为WEB 3用户建立一个平台，分享他们对区块链，元宇宙，NFT和加密的信息的知识，观点和见解。

LearntVerse正在建立一个互助辅导形式的有机培育机制。`mentee` 可以免费学习，玩学习游戏，获得奖励代币，并转换为稳定币。`mentor` 可以创造知识共享内容，如图表、视频、游戏和短文章，赚取奖励并转化为稳定的币。`mentor` 和 `mentee` 不是固定的，而是可以相互转换的。

`mentor` 和 `mentee` 都可以创造和/或购买NFT资产(如角色，笔记本，笔等)。非NFT所有者可以通过满足特定的需求和标准来创建新的非NFT。所有nft都可以在公共市场进行交易，例如:Opensea。希望在不久的将来，内部市场将建立在LearntVerse平台上。 LearntVerse相信我们在一起会更聪明、更强大，因此以DAO的形式进行管理。治理代币持有者将参与社区建设、运营和决策制定。

LearntVerse彻底将教育从NFT和元宇宙演进到更广泛的加密，区块链和多学科的知识体。随着NFT生态系统的快速发展，越来越多的人开始通过名人、运动员、明星和天价数字艺术来了解NFT和NFT项目。然而，NFT的应用范围要大得多，而NFT生态系统却很少被大多数人彻底了解。对非功能性语法的误解和非功能性语法生态系统的混乱是非功能性语法被普通人接受、使用或采用的障碍。这与区块链技术的精神是不一致的，而区块链技术是NFT开发的基础。在这种情况下，我们希望开发一个学习赚钱的平台，可以激励人们学习NFT和NFT生态系统。这是一个 `Edu-Fi` 平台，可以去中心化自治组织。DAO的治理激励人们参与社区建设，而从学习中获得的收入激励参与者进一步学习。它为各种各样的人加入NFT生态系统并成为创造者、开发者和投资者提供了许多机会。玩家从智力学习、教学和讨论(辩论)中受益，同时享受乐趣和奖励。

### 产品方案

1. 角色选择：`mentee` 和 `mentor` 带来的功能和体验是不一样的。 我们把不同的参与者聚集在这个社区，实现再创造。在这里, `mentee` 可以选择学习NFT和元世界的各种知识; `mentor` 可以传授关于NFT和元宇宙知识和技术。

2. 代币：设置两种代币 LNC 和 LNT：
   - LNC：游戏币，参与教学和学习活动都将得到 LNC 奖励，并且能用于铸造NFT和平台活动中。
   - LNT：治理代币，用于 DAO 管理和交易。

3. NFT：可以通过两种方式获取 NFT。一是用户自己铸造，二是获取平台提供的限量 NFT。NFT可交易并且可作用于提升学习和教学活动中获取的奖励。

## Web3 Jam 期间的开发规划

*Cadence合约*

- [x] NFT标准合约实现
- [x] 平台代币合约
- [x] Profile 合约

*Deploy*

- `FungibleToken` deployed `0x20f3eb00bb387d15` on testnet
- `NonFungibleToken` deployed `0x20f3eb00bb387d15` on testnet
- `ExampleNFT` deployed `0x3de9c43a330b0332` on testnet
- `ExampleToken` deployed `0x3de9c43a330b0332` on testnet
- `Profile` deployed `0x3de9c43a330b0332` on testnet
- `MetadataViews` deployed `0x3de9c43a330b0332` on testnet

*客户端*
 
基于 FCL-js 和 React 搭建的简易平台，实现以下功能：
- 1. Wallet 登陆和注册
- 2. Profile：用于获取用户信息和修改用户信息
- 3. LNC 代币：LNC 代币不上链，数值受铸造 NFT，学习和教学等活动影响
- 4. LNT 代币：LNT 基于 `ExampleToken` 和 `FungibleToken`，支持查询，交易
- 5. NFT 代币：NFT 基于 `NonFungibleToken`, `FungibleToken`, `ExampleNFT` 和 `MetadataViews`，支持查询，铸造

- [Source Code](./src/)
- [Pitch Deck](./docs/deck.pdf)/(./docs/deck.ppt)
- [Demo Video](./docs/demo.mov)

## 团队成员

| 姓名 Name | 角色 Role     | 个人经历 Bio | 联系方式 Contact     |
| ---- | ------------------- | --- | ----------------------- |
| Sherry | PM / Designer | 20 years’ experience in finance and higher-education sectors; 3 years’ experiences in crypto and blockchain; passionate in web 3.0, metaverse and NFT. |  |
| Orianne | PM / Designer | 7 years’ experience youth leadership in social empowerment and 2 years’ experience in project management; passionate in web 3.0 and defi. |  |
| Zhixiny | Full-Stack Engineer | 3 years’ experience in deep learning and AI accelerator. Have a big interest in and keep learning more about blockchain and web 3.0. |  |