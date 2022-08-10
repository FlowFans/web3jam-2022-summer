# Project Name

## Track

- [x] NFT x DAO/Tools
- [x] NFT x Game/Entertainment
- [ ] NFT x Life/Metaverse

## Description

### Problem statement
- **For most of the Web2 products which planned to migrate to Web3, it's hard to integrate blockchain wallet addresses into the existing account system in a C/S architecture product without sacrificing the UX.**  
[ORCAMOBI](http://www.orcamobi.com/), one of our collaborators with around 50M user basis worldwide, is looking for a solution that seamlessly integrate user's blockchain address with their existing accounts system.

- **In most scenarios, we can only decide whether buy or not by an NFT's price, appearance and maybe perks. There are fewer ways to develop a deeper bond with the collections.**  
There're too many NFT projects(especially PFP genre) has few utilities except being used as a profile picture. The potentialities are out there to be developed.  
Projects such as Fabricant, Loots, and Some.place are providing objects in the metaverse. However, the owner of these objects should have the power to re-utilise them.

- **Most NFT collections lack practical use cases, and holders cannot be a part of the creation process or offer their talents to the projects.**  
Nowadays, few projects involve holders as builders during the creation. Even for the projects that support customisation(e.g. Flovatar and MetaSoul), an owner of NFTs is still limited to the preset options provided by the project teams.  
The thresholds for being a creator/builder are still very high for most participants, either for the creating skills or coding abilities. Moreover, growing audiences from zero to one is also a pain point for the creators.


<!--
Please describe the following

- Target audience
- Evidence for the need
-->

### Proposed solution

<!--
Please describe the following, including but not limited to:

- Product Introduction
- Product Logo (Optional)
- Technical architecture
- Operational strategy
-->

![image](https://thing.fund/renas/images/siteLogo.png)

Renas is a gamified co-creation platform for evolving Web3 IPs, incubated by THiNG.FUND. By using Renas, every NFT holder could utilize their own collections and create stories together with their friends. Besides, they could get token incentives by playing, creating and participating in the governance.
- To the **players**, it’s a classic game, but they govern it;
- To the **creators**, it’s an IP generator that anyone can be a part of the co-creation and earn both reputation and incentives;
- To the **traders and collectors**, it’s easier to discover the actual value of an asset by simply following the trends in the community.


## What was done during Web3 Jam
The features of Renas we finished during Web3 Jam are:


- **Easily signing up and mapping with wallet**  
In Renas, users can sign up via their familiar Web2 auth methods, e.g. Discord. Once logged in, Renas will automatically query Emerald Id to see if there's their Discord<>wallet address mapping data. If so, Renas will create a record for mapping the account with the wallet address. The user would not even notice that until they need to verify their assets in the wallet or withdrawal.  
[» See the demo](https://thing.fund/renas_stg/profile/wallet/)
> <img src="https://thing.fund/renas_stg/_design/bind.jpg" width="50%">

  
- **Topping up as easy as using mainstream platforms**  
Once a user needs to top up their tokens, they will be redirected to a service page, then connect their wallet and approve the transaction.  
Renas will monitor the transaction status and automatically update a user's balance in game.  
[» See the demo](https://thing.fund/renas_stg/token/deposit/)
> <img src="https://thing.fund/renas_stg/_design/topup.jpg" width="50%">

- **Withdrawing within one click**
In most circumstance, users don't need to handle any onchain activities. They only care about the assets they withdrawed from the game and the time they about to receive. In Renas, all they need to do is clicking the withdraw button and checking if the transaction is completed later.  
[» See the demo](https://thing.fund/renas_stg/token/withdraw/)
> <img src="https://thing.fund/renas_stg/_design/withdraw.jpg" width="50%">

- **Switchable soulbound fungible tokens**
In some scenarios, such as being at the early stage of MVP or purely measuring contribution during the user's lifespan, the fungible tokens should be set to soul-bounded. Once a project goes to the mass market, the same fungible tokens might need to be able to switch to transferrable.  
Renas' token contract provides the method to turn on/off the transferring so that a project could be customised to fit the needs.

- **Governed by holding both fungible tokens & NFTs**
A user can vote for the proposals in Renas, such as a revision of an individual gaming item, a global variable change, or a new content submission. To do that, the user should stake the governance tokens and specific NFTs in Renas to gain the voting weight.

<!-- Please list the features and docs you achieved during the event -->

> Delivery Meterials

- [Source Code](./src/), deployed to Testnet address: [A.98c9c2e548b84d31.ContributionPoint](https://flowscan.org/contract/A.98c9c2e548b84d31.ContributionPoint/overview)
<!-- Optional -->
- [Please see the pitch deck to learn more about Renas](https://thing.fund/renas_stg/_design/theRenas.pitchdeck.pdf)
- Demo Video is coing soon <!-- or using online documentation url / ipfs url -->

## Additional information
A playable product has already deployed online, [please see the link here](https://thing.fund/renas_stg/), you'll need to log in via your Discord account.
**Wish you have Fun!**
<!-- More information you want the judges to see -->
