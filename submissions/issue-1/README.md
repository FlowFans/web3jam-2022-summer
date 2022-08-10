# Project Name

## Track

- [ ] NFT x DAO/Tools
- [ ] NFT x Game/Entertainment
- [x] NFT x Life/Metaverse

## Description

### Problem statement

Inspired by geohash geocoding and fractals, we try to propose a new paradigm for NFTs: infinite liquidity and finite
weighting. We will use a simple economic model to create it. Genesis generates 32 NFTs, each of which can then be split
into 32 new NFTs.

And we will create a scene suitable for split propagation to apply it. What does a membership-based AI metaverse sound
like? We can use Discord to build it. And we also have Bot helpers.

Infinity is achieved through constant dynamic splitting. However, since the weight of each child NFT is only 1/32 of the
parent NFT, the sum of the weights is no different from the original 32 NFTs at the time of creation. Therefore, the
unlimited free issuance will not cause any trouble to the future DAO.

#### Target audience

AI artist.

#### Evidence for the need

Highly available AI will be a scarce resource in the future, and may have regional biases, and we hope to alleviate this
problem through blockchain.

### Proposed solution

#### Product Introduction

Each NFT is just a simple string of codes, stored on the blockchain. Based on this, we will build a membership system
and build a Discord community. Here, the robot is connected to the blockchain network, so it can easily and quickly
determine the user's identity. Due to the nature of fractals, each NFT can be destroyed and split into 32 equivalent
blocks, and the process is irreversible.

Become a member to enjoy the privileges in the community: AI services provided by robots, such as content creation
through AI, quickly generate images, texts, etc. of your own imagination. In addition, the project is multi-chain
independent existence.

#### Technical architecture

- [x] Blockchain. (Flow, Ethereum)
- [x] Smart contract. (Cadence, Solidity)
- [x] UI. (React)
- [x] AI. (OpenAI)
- [x] Bot. (Discord Bot)
- [x] Community. (Discord)

#### Operational strategy

Since our genesis NFT has only 32 blocks, it only needs to be distributed to 32 people, and the NFT generated 
subsequently will be freely distributed by the holders.

## What was done during Web3 Jam

> Delivery Materials

- [Source Code](./src), deployed to Testnet address: 0xf5c21ffd3438212b.
- [web code](./src/web), deployed to [https://wakandaplus.wakanda-labs.com](https://wakandaplus.wakanda-labs.com). Origin git is [wakanda-plus-web](https://github.com/wakandalabs/wakanda-plus-web)
- [solidity code](./src/solidity), deployed to polygon, see [polygonscan](https://polygonscan.com/address/0x9c824c1dc64cdfcfe27c91faafc991c013bdaa74#code). Origin git is [wakanda-pass](https://github.com/wakandalabs/wakanda-pass)
- [discord bot code](./src/bot), see [Wakanda Metaverse](https://discord.com/invite/hzvXbjtzgj), the bot is
  Wakanda+#0223, use /help to open the menu. Origin git is [wakanda-plus-bot](https://github.com/wakandalabs/wakanda-plus-bot)

## Additional information

### How to use the bot

Join the [Discord](https://discord.com/invite/hzvXbjtzgj) server, and use /help to open the menu. Usually, the first thing is to link your wallet, using /connectwallet.
And then, you will visit wakanda+ web, link your wallet and enjoy the services.

### How to use the web portal

visit [Wakanda+](https://wakandaplus.wakanda-labs.com), connect your flow wallet like lilico or blocto. And then your
can click [Flow Portal](https://wakandaplus.wakanda-labs.com/#/portal/flow) to use the services.

### How about solidity code

We also have a solidity version, and we deployed on Polygon. You can visit [OpenSea](https://opensea.io/collection/wakandapass)
In the Polygon, we tried to add a random gradient property to generate on-chain images. The motivation is to create
some "ugly" NFT to lead people to burn and split it.
