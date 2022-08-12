# Web3 Jam 2022 on Flow - NFT 主题创新黑客松

![banner](/assets/banner-2160x960.jpg)

## 什么是 Web3 Jam

Web3 Jam on Flow 是一场持续一个多月的创新黑客松活动，旨在更好地连接 Web2 和 Web3 的互联网人才，让双方有机会深入浅出地进行行业交流和落地技术方案的探讨。  

团队或者个人均可以报名参加，本期活动可选的赛道为：

- [NFT x DAO/Tools][github-issues-1]
- [NFT x Game/Entertainment][github-issues-2]
- [NFT x Life/Metaverse][github-issues-3]

本次活动由 TinTinLand 和 Flow China 共同承办，详情可参见：[本期 Web3 Jam 活动指南][guide]。

## 活动时间表

> 具体时间安排以 Google Calendar 为准。

[点击此处获取日历订阅链接][calendar]

| 时间点  | 活动内容            |
| ------ | ------------------ |
| 6月17日 | 活动报名开启  |
| 7月7日 20:00 | 开幕式  |
| 7月11日-7月17日 | 3场线上交流会  |
| 7月17日 21:30 | 主题、评分维度揭晓  |
| 7月18日 | 开发阶段  |
| 7月21日-8月7日 | 6场线上 Workshop 直播  |
| 7月31日 23:59 | 项目注册截止  |
| 8月10日 23:59 | 项目递交截止  |
| 8月12日 | 初审结果公布  |
| 8月13日 | 线上 DemoDay  |
| 8月14日 | 最终结果宣布  |

## 活动报名和参与讨论会

> 请尽早填写报名表，并加入活动微信群，以及时获悉活动通知。

活动报名时间为 6月17日-7月31日，[点击此处前往填写活动报名表][sign-up-form]  
若希望参加讨论会，请在 7月17日 前递交报名表。

## 项目/团队注册

> 项目/团队注册情况，以本仓库内的 [issues][github-issues] 记录为准。

1. 到 [issues][github-issues] 页，点击 `New issue`，在模板选择页点选 `Sign Up` 的 `Get started`。
2. 根据 `Sign Up` 的 Issue 模版，填写您的项目基础信息。
3. 创建后，该 Issue 编号即为您递交项目资料时使用的队伍编号。

## 项目递交流程

请 fork 本仓库，并以发送 PR 的形式进行项目资料的递交。  
为确保各个团队递交到的目录独立，递交 PR 时各团队请确保自己递交的目录文件夹名为 `/submissions/issue-#/`，即：  
各个团队在 `submissions` 目录下创建一个名为 `issue-#` 的递交目录（其中 `#` 为您团队注册时的 Issue 编号）。

### 项目递交目录格式

可参考递交模板 `./submissions/.template` 目录的形式（可以复制 `.template` 文件夹，并改名为各团队相应的递交目录 `issue-#` ）：

```txt
submissions
  L issue-0/
    L src/
      L cadence/       // cadence 相关代码
      L ui/            // 前端相关代码
      L 。。。          // 其他源码
    L docs/            // 文档资料，视频资料, PPT/PDF等
    L README.md
```

### 项目递交所需的资料

比赛递交的资料，主要需要包含三部分内容：

- Cadence 合约代码（可将代码部署到 Flow Testnet 上）
- 项目简介（写在 README.md 中，**英文递交**，以方便部分海外评委在初审期间了解项目）
- 演示资料（演示文档 + Demo视频，初审时为**可选**递交资料，DemoDay时需要线上路演。DemoDay主要由中文评委组成可以用中文，若视频在初审期间递交建议加上英文字幕）
  - 演示文档，用于在 DemoDay 上进行路演的材料，是 DemoDay 的评分依据之一。可链接一个在线文档或命名为 `./docs/deck.pdf` 放置在项目递交资料中，在 `README.md` 内填好对应的链接。
  - 不长于 **5分钟** 的产品 DEMO 演示视频，是 DemoDay 的评分依据之一。 可链接一个在线文档（Google Drive、IPFS等均可）或命名为 `./docs/demo.mp4` 放置在项目递交资料中，在 `README.md` 内填好对应的链接。

比赛产出需要遵循的要求：

- 必须能清晰明确地阐述基于 NFT+ 的产品模型 / 经济模型 / 赋能方式等。
- 必须使用 Flow Cadence 完成合约的开发。
- （可选）完成产品最小化可行验证原型（MVP）的 Demo 实现。
- （可选）贴合本期的参赛主题，该主题将在第三场讨论会结束后正式公布。

## 项目注册与项目递交，重要时间点

所有参赛项目需先在 [issues][github-issues] 中注册，然后再进行 PR 递交资料。

- 团队报名或组队完成立项后，请及时完成项目注册，**7月31日 23:59**后我们将不再接收新的项目注册。
- 完成注册的项目，请在 **8月10日 23:59** 项目递交截止时间前进行 PR 递交参赛资料，初审将以此为准。

## 本期活动主题

![theme](/assets/theme.png)

## 活动奖项与奖金

本次 Web3 Jam 以思想的碰撞为基调，故不设置层级，各领域拔尖者均有机会获奖。  
一共设置有五大奖项，分别为：

- 最佳项目奖 8000 USD
- 最佳代码设计奖 3000 USD
- 最佳 Web3 叙事奖 3000 USD
- 最佳主题表达奖 3000 USD
- 开发者选择奖 3000 USD

## 评审规则

### 项目初审 （技术层面）

在初选截止日前提交项目资料，并列出在活动期间已完成的开发工作/功能点，我们将对这些目录/档案作重点进行技术评审。**每项分数为10分。**

- 技术难度：解决的问题有技术门槛，技术层面具备的创新性。评审 Cadence 合约为主，前后端为辅。
- 项目完备：项目不只是概念，已实现PoC；技术架构完整、代码优雅、流程规范。评审以项目完备性程度为主。

### 线上路演 （产品层面）

在 DemoDay时进行线上路演，对产品进行以下评审维度的评分。**每项分数为10分。**

- 创新与应用价值：能解决问题，并在所对应的应用或行业场景中发挥作用，有创新性，可以打破常规思维。
- 叙事与市场策略：有明确可行的产品路线图，并在所阐述的叙事中能明确启动、增长、留存的策略。
- 视觉与产品体验：产品对潜在用户来说，能很直观地理解并使用；前端产品需具备良好的 UE / UX 。
- 贴合主题：在解读本期活动的主题后，在产品或者产品演示过程中，能将对其的理解进行有效的阐述。
- 路演表现：产品展示/录播，很好反映做出的功能点及流程，演说清晰，项目内容交待清楚明确。

### 奖项记分规则

| 评分维度 \ 奖项 | 最佳项目奖 | 最佳代码设计奖 | 最佳Web3叙事 | 最佳主题表达奖 | 开发者选择奖 |
| --- | --- | --- | --- | --- | --- |
| 技术难度 | 20% | 40% | 10% | 10% | 20% |
| 项目完备 | 10% | 20% | 5% | 5% | 10% |
| 应用价值 | 20% | 10% | 30% | 20% | 20%（互评） |
| 叙事空间 | 10% | 5% | 25% | 15% | 10%（互评） |
| 产品体验 | 20% | 10% | 15% | 15% | 20%（互评） |
| 贴合主题 | 10% | 10% | 5% | 20% | 10%（互评） |
| 路演表现 | 10% | 5% | 10% | 15% | 10%（互评） |

## 补充说明

- 参赛团队必须通过线上路演汇报的形式，全方位阐述作品实现过程及最终作品。
- 参赛团队提交的所有参赛资料，知识产权完全归参赛团队所有，参赛资料仅用于本次大赛评奖与宣发。
- **本次活动的最终解释权归 Flow 官方所有。**

## 参考资料

- [Flow官方文档][refer-1]
- [Flow Cadence 基础课程 by Emerald City][refer-2] 或 [本课程各章索引(中文版)][refer-3]
- [Flow 开发资源汇总][refer-4]
- [Flow生态项目列表][refer-5]

### 联系方式

如果您对本次活动由任何疑问，可以通过以下方式联系我们：

- [Github 讨论区][github-disc]
- 根据[活动指南][guide]，加入到我们的活动社群

<!-- Links -->

[github-issues-1]: https://github.com/FlowFans/web3jam-2022-summer/labels/NFT%20x%20DAO%2FTools
[github-issues-2]: https://github.com/FlowFans/web3jam-2022-summer/labels/NFT%20x%20Game%2FEntertainment
[github-issues-3]: https://github.com/FlowFans/web3jam-2022-summer/labels/NFT%20x%20Life%2FMetaverse
[github-issues]: https://github.com/FlowFans/web3jam-2022-summer/issues
[github-disc]: https://github.com/FlowFans/web3jam-2022-summer/discussions
[calendar]: https://calendar.google.com/calendar/u/1?cid=Y19wcGZwYmYwa2ltMHJrbnZoOWJhdmJscHA4b0Bncm91cC5jYWxlbmRhci5nb29nbGUuY29t "活动日历"
[guide]: https://tintinland1.notion.site/Web3-Jam-2022-Summer-0a0f85afb0db49cd9980cbdcc61f3101 "活动指南"
[sign-up-form]: https://wj.qq.com/s2/9919322/2a76/ "报名表"

[refer-1]: https://docs.onflow.org/
[refer-2]: https://github.com/emerald-dao/beginner-cadence-course
[refer-3]: https://flowapac.notion.site/Flow-Cadence-fb162c6be61d4b0e95b36e5ea79086da
[refer-4]: https://mp.weixin.qq.com/s/slDzYk8iVRmskgXsbQ9Qyw
[refer-5]: https://www.flowverse.co/
