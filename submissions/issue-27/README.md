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




- **Interesting Tools Matching NFT Community Cultures**

  To facilitate community activities, Relation Club has launched a series of tools to help NFT creators conduct community operation in more convenient and interesting ways and produce content that matches NFT community cultures.

For example, the PFP Group Photo function, allowing NFT holders to  enter the same room to take group photos in real time, which provides an interesting and informative tool for proving user attendance in activities.
    
![club homepage (1)](https://user-images.githubusercontent.com/91399393/183905910-a1c13282-762a-44eb-84f1-433d99c3dced.jpg)



*Technology Architecture*

![Frame 1000003297 (1)](https://user-images.githubusercontent.com/91399393/183940677-fd1e4589-7ee3-42ec-aa74-90395f1e5d2d.png)



*Product LOGO*

![0 5](https://user-images.githubusercontent.com/91399393/183939657-c03171f3-a950-4eec-b032-fd7d7a6b7af1.png)

*Operation Strategies*


- Cooperating with NFT marketplace and projectors to create Web3 native communities. Making use of Relation Club's data function to better understand user's demands and iterate product functions.
- Strengthening the influence of Relation Clubs in NFT communities through cooperation to increase user stickiness.
- Horizontally increasing Club types. Enriching the types of Club operation tools, and encouraging communities to build Club operation tools together.
- Increasing the depth and availability of Club Infra data and building data infrastructure for Flow NFTs

## Development Plans during the Web3 Jam

*The Cadence Contract*

- To define New Club's architecture and key fields.
- To enable user-generated NFT contracts and NFT minting functions. 
- To deploy test network [contracts](https://flow-view-source.com/testnet/account/0x457f3685a6f38813/contract/RelationNFT)
- To connect Infra to New Club Contract. And to enable NFTs created by users to be generated into Clubs in the first place.
- Key Implementations
<img width="912" alt="111" src="https://user-images.githubusercontent.com/91399393/183896194-aba9471c-2ac4-49f1-841e-49d7ec5e8671.png">


*Server-end*

- To implement Flow Club Infra. To update NFT ownership according to real-time transactions on chain to manage Club members.
- To implement Flow Resource Aggregator with existing data platforms and services and to collect and regularly update metadata information from different Dapps and NFTs in marketplaces.
- To implement the Club Owner Verification Program to open the rights of management and dashboard browsing to users by verifying whether their signatures are NFT contract deployers.

*Client-end*

- To integrate FCL and integrate Flow Identity System into Relation Unified Identity Verification. To enable the users’ signatures of the Flow addresses to be authenticated and obtain temporary session keys as identity verification credentials.
- To define the web/HTML5 UI for Club Space. To regularly update the Linked Pages, Club News, Proposals, Active Users and Club Membership Wall information according to the Flow Club Infra.
- To implement Flow Club UI and the sending and display of Flow NFTs in Chat extensions.
- To implement the UI and data analysis of the Club statistics function, which will open data services on chain and in Relation to the contract deployers based on the users’ aggregated identities. The statistics of data covering average NFT holdings, average prices, everyday independent wallet statistics, sales statistics in different secondary markets, daily active users, statistics of Club chat, Club member asset analysis and Club members details. These statistics will be based on user-owned accounts rather than just addresses.
- To implement the use cases of the Club community operation tools. For the Group Photo function, it includes UI, the function of creating a Group Photo room, the Online Status Check, and the function to generate and share images.  

*Materials Submission*

- [Source Code](https://github.com/relationlabs/web3jam-2022-summer/tree/main/submissions/issue-27/src/cadence), deployed to Testnet address: 0x457f3685a6f38813
- [Pitch Deck](https://docsend.com/view/htfb4dzi55ey794j) 
- [Demo Video](https://3fypb-gqaaa-aaaag-aaedq-cai.ic1.io/banner/relationClub.mp4) 
- [Testing Demo](https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia) https://relationlabs.ai/#/clubguide/A.afb8473247d9354c.FlowNia


## Team member

|  Name |  Role     |  Bio |  Contact     |
| ---- | ------------------- | --- | ----------------------- |
| Jessica | Economic Designer | Crypto and commodity derivatives trader,5+ years of blockchain research experience,CFA, ACCA,Visiting researcher at Imperial College |  j.chang@relationlabs.ai |
| Joe | Product Manager/Engineer | former product leader of top blockchain companies, 6+ years of project experience in finance, logistics, social networking, games products | pikajoe@relationlabs.ai  |
| Santry | Marketing |Former CMO at Patract Labs,9+ years of marketing, operation and management experiences |  santry@relationlabs.ai | 
| Yann | Full Stack Developer | 15+ years of experience in cloud computing, big data and large-scale distributed systems. |  yann.ren@relationlabs.ai  |
|  Ben | Full Stack Developer | former core development engineer of IBM, AWS.10+ years of back-end development experience |  b.zhang@relationlabs.ai  |           |



