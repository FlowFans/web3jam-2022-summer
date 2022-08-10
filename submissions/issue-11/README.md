# Project Name

## Track

- [ ] NFT x DAO/Tools
- [ ] NFT x Game/Entertainment
- [x] NFT x Life/Metaverse

## Description

### Problem statement
LearntVerse is a learn-to-earn social-fi platform which aims at building a platform for web 3 users to share their knowledge, opinions and insights towards the information about blockchain, metaverse, NFT and crypto. 

LearntVerse is building an organic cultivating mechanism through peer-mentoring. The mentees can learn for free, play the learning games, get rewards tokens and convert to stable coin. The mentors can create knowledge sharing content, i.e. graphs, videos, games and short articles, earn rewards and convert to stable coin. The roles of mentor and mentee are not fixed but can be switched from on to another. 

Both mentees and mentors can mint and/or buy NFT assets (i.e. characters, notebooks, pens and so on). NFT owner can mint new NFTs by meeting certain requirements and criteria. All NFTs can be traded at the public marketplace, for example: Opensea. And hopefully in the near future, the internal marketplace will be built in the LearntVerse platform. 
LearntVerse believes that we are smarter and stronger together thus is governed in the form of DAO. The governance token holders will be involved in the community building, operating and decision making. 

LearntVerse will reinvent education from NFT and metaverse to broader crypto, blockchain and multi-disciplinary knowledge body. With the rapid development of NFT ecosystem, more and more people starts knowing about NFT and NFT projects through celebrities, athletes, stars and rocket-high priced digital arts. However the application of NFT is much bigger while the NFT ecosystem has rarely been thoroughly understood by the majority of the population. The misunderstanding of NFT and the confusion of NFT ecosystem are the hurdles for NFT to be accepted, used, or adopted by the ordinary people. This is not consistent with the spirit of Blockchain technology, which is the basis of the NFT development. Under this circumstance, we hope to develop a learn-to-earn platform, which can motivate people to learn about NFT and NFT ecosystem. It’s an Edu-Fi platform and can be Decentralised Autonomous Organised. The governance of DAO incentivises people to engage in the community building while the earning from learning motivates the participants to learn further. It provides a lot of opportunities for a wide range of people joining the NFT ecosystem and becoming creators, developers, and investors. Players benefit from intellectual learning, teaching and discussing (debating) while enjoying the fun and rewards. 


### Proposed solution

1. Role: We bring various participants together in this community to achieve the re-invention. Here, mentee can choose to learn various knowledge in NFT and metaverse; the mentor (creator) can develop modules about NFT and metaverse. Both teaching and learning activities will be rewarded thus players are motivated to contribute and learn further.

2. Token：We have two kinds of tokens `LNC` and `LNT`
   - LNC：Game tokens, the reward from learning and teaching, and which is able to used to mint NFT.
   - LNT：Governance tokens, which is able to be used for DAO and trading.

3. NFT：Two ways to get NFT. The First is to mint NFT by users, the second is to get NFT from the platfrom.

## Web3 Jam Project

*Cadence Contracts*

- [x] NFT contract
- [x] LNT token contract
- [x] Profile contract

*Deploy*

- `FungibleToken` deployed `0x20f3eb00bb387d15` on testnet
- `NonFungibleToken` deployed `0x20f3eb00bb387d15` on testnet
- `ExampleNFT` deployed `0x3de9c43a330b0332` on testnet
- `ExampleToken` deployed `0x3de9c43a330b0332` on testnet
- `Profile` deployed `0x3de9c43a330b0332` on testnet
- `MetadataViews` deployed `0x3de9c43a330b0332` on testnet

*Dapp*
 
Based on FCL-js and React, we have built a simple demo：
- 1. Wallet log-in and sign-up
- 2. Profile: query and modification
- 3. LNC: LNC is not recorded on chain. The number of LNC is influenced by mint NFT, learning and teaching.
- 4. LNT: LNT is based on `ExampleToken` and `FungibleToken`
- 5. NFT: NFT is based on `NonFungibleToken`, `FungibleToken`, `ExampleNFT` and `MetadataViews`

- [Source Code](./src/)
- [Pitch Deck](./docs/deck.pdf)/(./docs/deck.ppt)
- [Demo Video](./docs/demo.mov)

## Team

| Name | Role     | Bio | Contact     |
| ---- | ------------------- | --- | ----------------------- |
| Sherry | PM / Designer | 20 years’ experience in finance and higher-education sectors; 3 years’ experiences in crypto and blockchain; passionate in web 3.0, metaverse and NFT. |  |
| Orianne | PM / Designer | 7 years’ experience youth leadership in social empowerment and 2 years’ experience in project management; passionate in web 3.0 and defi. |  |
| Zhixiny | Full-Stack Engineer | 3 years’ experience in deep learning and AI accelerator. Have a big interest in and keep learning more about blockchain and web 3.0. |  |