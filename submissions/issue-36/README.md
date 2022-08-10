# GhostNFT.fun

## A <strong>_collateral-free_</strong>, <strong>_non-ownership-transfer_</strong>, <strong>_application-oriented_</strong> NFT rental protocol and marketplace

### Background

Many believe that we are on the eve of NFT applications breakout. NFTs have wide variety of utility in both online and offline NFT applications. An NFT owner could get profit by using the NFTs in the applications, but he/she may not have enough time. To users who want to participate in the applications but do not have NFTs, buying NFTs may costs a lot. This is where the idea of NFT rental comes up.<br>

### Existing NFT Rental Protocol

There are collateral-based NFT rental protocols and some innovative ones:<br>

#### Collateral-base rental process: </br>
1. Lessors transfer their NFTs to the rental platform, set up rent and rental terms.</br>
2. Renters pledge tokens to the rental platform, pay the required rent, then get the NFTs from the rental platform.</br>
3. When rental terms end, the renters return borrowd NFTs to the platform. The platform return the pledges to the renters and return the NFTs to the lessors.</br>

#### Rental processes with standard ERC-4907: </br>
NFTs with ERC-4907 standard have a user and an expiration field to indicate to whom the use right belong and the term of NFT rent. </br>
1. Lessors transfer their NFTs to the rental platform, set up rent and rental terms, approve the authentication of the platform to change the use right. </br>
2. Renters pay the required rent to the rental platform. The rental platform then set user field to the renters.</br>
3. When the rent term ends, the lessors redeem the NFTs, the platform transfer the NFTs back to the lessors.</br>

#### Something could be imporoved?
In the above processes, we can see some disadvantages:</br>
1. Users have to transfer assets(NFTs/pledges) to the platform. The assets could be lost if the platform have any flaws. </br>
2. The NFT owners could miss some airdrop oppertunities if NFTs have to leave the origin owner temperorily.</br>
3. Collateral-based renal protocols is low financial efficient.</br>
4. When one NFT is rented to one application, it can't be rented to other applications in the rental term.</br>

### Our Idea and Solution

In real world, one has to take possession of something psychically if he/she need to use it. But in digital world it would be hardly necessary. For example in a combat game: we use heroes's/weapon's properties values to judge their fighting abilities, we use heroes's/weapon's images to show them.</br></br>
So in NFT rental, we believe that when we: </br>
1. have the AUTHENTICATION OF USAGE of the origin NFTs,
2. are able to access to the NFTs' properties(metadata),</br>

then we can USE the NFTs!</br>

#### Our rental process

![image](https://github.com/yijie37/ghostnft-protocol-flow-contract/blob/main/misc/collateral-free-flow-en.png)<br>

1. Lessors register authentication of usage of their NFTs(list for rent) instead of transfer their NFTs to the platform, set up rent and rental terms. The platform record the NFT metadata. As the lessors do not need to transfer the NFTs, they have to pledge some guarantees for not transfering the NFTs to others in rental term.</br>
2. Renters pay the required rent to the rental platform.</br>
3. If ANYONE finds out that the lessors transfer their listed NFTs to others in the rental term, he/she can claim a fixed portion of their guarantee and distribute rest guarantees to the lessors and the applications to cover their loss.</br>
4. If the lessors keep their promises during the rental form, they can get back their guarantees.</br>
5. The applications dev team need to integrate the rental protocol to their code, which is pretty EASY.</br>

In the process above, we can see there isn't ANY assets transfered. That means users don't have to worry about assest losses. NFT owners can always have airdrop oppertunities by constantly possessing their NFTs. The renters not having to pledge collaterals increase financial efficiency.


#### M * N Rental Model

Since the NFTs are not transfered, they can be registered for rent in more than one application. Also, one application can use multiple NFT collections registered for rent in themselves. That is our M * N rental model. </br>
To any application team, they can make any NFT collection rentable by using GhostNFT platform. That means application can bring in blue chip NFT owners to take part in their application, this may improve the influence of the application. </br>
In GhostNFT platform, any application team can use a self-service page to deploy a rental contract that enable a specific NFT collection rentable in the application, that makes the whole listing process more conveniently.</br>
We believe this model will bring massive combinations of NFTs and applications!</br></br>


### Tokenomics

#### We will issue token $GNFT.</br>
Lessors should use $GNFT as guarantee when listing NFTs for rent.</br>
#### We will start a airdrop mode called airdrop on demand:</br>
A portion of $GNFT will be airdroped to the users of the first 100 application projects that integrate with GhostNFT rental platform.


### Video Demos:
https://www.youtube.com/watch?v=C74F-zpexQ0
