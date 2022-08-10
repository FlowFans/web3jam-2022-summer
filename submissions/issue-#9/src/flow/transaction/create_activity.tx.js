export const CREATE_ACTIVITY = `
import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import FungibleToken from 0xFungibleToken
import PioneerMarketplace from 0xPioneerNFT

transaction(
    name: String,
    description: String,
    url: String,
    saleItemID: UInt64, 
    partAmount: UFix64,
    createTime: UFix64,
    divisionCount: UInt64,
    timeLength: UFix64,
    creatorAddress:Address,
    adminAddr:Address,
    targetAmount: UFix64,
    ){
let storefront: &PioneerMarketplace.Storefront
let flowReceiver: Capability<&AnyResource{FungibleToken.Provider, FungibleToken.Receiver}>
let PioneerNFTProvider: Capability<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
// let creatorAddress: Address
let collectionRef: &{PioneerNFTs.PioneerNFTCollectionPublic}
let collectionR:&PioneerNFTs.Collection
prepare(account: AuthAccount){

 if account.borrow<&PioneerMarketplace.Storefront>(from: PioneerMarketplace.StorefrontActivityStoragePath) == nil {

    // Create a new empty .Storefront
    let storefront <- PioneerMarketplace.createStorefront() as! @PioneerMarketplace.Storefront
    
    // save it to the account
    account.save(<-storefront, to: PioneerMarketplace.StorefrontActivityStoragePath)

    // create a public capability for the .Storefront
    account.link<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(PioneerMarketplace.StorefrontActivityPublicPath, target: PioneerMarketplace.StorefrontActivityStoragePath)
}

    self.storefront= account.borrow<&PioneerMarketplace.Storefront>(from: PioneerMarketplace.StorefrontActivityStoragePath)!
    // self.collectionRef = account.getCapability(PioneerNFTs.CollectionPublicPath).borrow<&{NonFungibleToken.CollectionPublic}>()?? panic("Could not get receiver reference to the NFT Collection")
    self.collectionRef = account.getCapability(PioneerNFTs.CollectionPublicPath).borrow<&{PioneerNFTs.PioneerNFTCollectionPublic}>()?? panic("Could not get receiver reference to the NFT Collection")
    //  self.collectionRef = account.getCapability<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(PioneerNFTs.CollectionPublicPath).borrow()??panic("no collectionRef")
    self.collectionR = account.borrow<&PioneerNFTs.Collection>(from: PioneerNFTs.CollectionStoragePath)!



     self.flowReceiver = account.getCapability<&FlowToken.Vault{FungibleToken.Provider,FungibleToken.Receiver}>(/public/flowTokenReceiver)
     // assert(self.flowReceiver.borrow() != nil, message: "Missing or mis-typed FlowToken receiver")

     let PioneerNFTCollectionProviderPrivatePath=/private/PioneerNFTCollection

    //  self.creatorAddress=account.address

     if !account.getCapability<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(PioneerNFTCollectionProviderPrivatePath)!.check() {
        account.link<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(PioneerNFTCollectionProviderPrivatePath, target: PioneerNFTs.CollectionStoragePath)
         }

   self.PioneerNFTProvider = account.getCapability<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(PioneerNFTCollectionProviderPrivatePath)!
    assert(self.PioneerNFTProvider.borrow() != nil, message: "Missing or mis-typed PioneerNFTs.Collection provider")
   }

execute{
    let admin = getAccount(adminAddr)
    

    // let adminStroeFront = admin.borrow<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontManager}>(from: PioneerMarketplace.StorefrontActivityStoragePath)??panic("no capability")

    let adminStroeFront = admin.getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(PioneerMarketplace.StorefrontActivityPublicPath).borrow()??panic("no capability")

    // let adminStroeFront = admin.getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontManager}>(PioneerMarketplace.StorefrontActivityStoragePath).borrow()??panic("no capability")

    let adminCollectionRef = admin.getCapability<&{PioneerNFTs.PioneerNFTCollectionPublic}>(PioneerNFTs.CollectionPublicPath).borrow()??panic("no capability")
    
    
let activeID= adminStroeFront.createActivity(
    name:name,
    description:description,
    url:url,
    activeStatus:0,
    nftType: Type<@PioneerNFTs.NFT>(),
    nftID: saleItemID,
    creator: creatorAddress,
    createTime: createTime,
    targetAmount: UFix64(targetAmount),
    currentAmount: 0.0,
    minPartAmount:targetAmount/UFix64(divisionCount),
    divisionCount:divisionCount,
    startTime: getCurrentBlock().timestamp,
    endTime: getCurrentBlock().timestamp+timeLength,
    receiver:self.flowReceiver,
    externalUrl:"",
   )

  adminStroeFront.createListing(
    nftProviderCapability:self.PioneerNFTProvider,
    nftType: Type<@PioneerNFTs.NFT>(),
    nftID: saleItemID,
    salePaymentVaultType: Type<@FlowToken.Vault>(),
    receiver:self.flowReceiver,
    salePrice:targetAmount,
    minPrice: UFix64(partAmount),
    itemStatus:1,
    activeID: activeID
    )
    let nftToken <- self.collectionR.withdraw(withdrawID:saleItemID)

    adminCollectionRef.deposit(token:<-nftToken )
 }
}
`