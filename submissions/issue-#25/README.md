<!--
 * @Author: cryptoSharing
 * @Date: 2022-08-10 11:54:05
 * @LastEditTime: 2022-08-10 12:04:31
 * @LastEditors: cryptoSharing
 * @Description: 
-->
# Project Name

## Track

- [x] NFT x DAO/Tools
- [ ] NFT x Game/Entertainment
- [ ] NFT x Life/Metaverse

## Description

### Problem statement

​	If DeFi builds the economic system of the metaverse, then NFT constitutes the asset elements of the metaverse. At present, NFT applications are prosperous and derived, but as the unit price of NFT is wildly hyped in the secondary market, the threshold for users to purchase NFT has been greatly increased. In this context, the market has spawned the demand for NFT leasing.

Pain points in the current rental market

- lender
  - **take the risk of default**
  - **Cannot be used for NFT staking**
  - **Very cumbersome operation process**
- renter
  - **Over-collateralization required when leasing**
  - **Cumbersome operation**

### Proposed solution

- Product Introduction

​		In order to match the transactions of both parties as much as possible and improve the liquidity and utilization of NFT, we propose a solution of unsecured leasing.

  		1. Implement a lease agreement that can use the original NFT to generate usage rights	
		2. Implement a rental platform on this basis
		3. Minimize rental cost (user: rental fee; project party: adaptation cost)
		4. Maximize service experience (dramatically simplified and user-friendly)

- Product Logo (Optional)

​		![logo](https://camo.githubusercontent.com/91f97493dd1969fd19ee0879c1c5f5c4d00e1c04ef0d88fe6445686df1b43629/68747470733a2f2f692e706f7374696d672e63632f624a42666b4666542f696d6167652e706e67)

- Technical architecture

  ​	In order to solve the pain point of the high mortgage threshold in the rental market, we have created a new type of leasing method in the digital currency field based on the existing ecological structure of the blockchain: - Separating ownership and use rights, and adopting an unsecured leasing scheme.

  ​	Added user roles and removed many unnecessary operations. Users can transfer their own usage rights.

  ​	The owner can continue to use it before renting, not after renting.

  ![Technical architecture](https://camo.githubusercontent.com/00686b45cafb20dc902a9836a9010f0294756cd505e06a64d8b6b9e4bb1430b4/68747470733a2f2f75706c6f61642e63632f69312f323032322f30372f32302f304f32434c6e2e706e67)

## What was done during Web3 Jam

<!-- Please list the features and docs you achieved during the event -->

> Delivery Meterials

- [ExampleNFT](./src/cadence/contracts/ExampleNFT.cdc), deployed to Testnet address: 0xb096b656ab049551

- [ExampleNFTUser](./src/cadence/contracts/ExampleNFTUser.cdc), deployed to Testnet address: 0xb096b656ab049551

- [ExampleRentMarketplace](./src/cadence/contracts/ExampleRentMarketplace.cdc), deployed to Testnet address: 0xb096b656ab049551

  <!-- Optional -->

- [Pitch Deck](./docs/deck.pdf) <!-- or using online documentation url / ipfs url -->

- [Demo Video](./docs/demo.mp4) <!-- or using online documentation url / ipfs url -->

## Additional information

<!-- More information you want the judges to see -->