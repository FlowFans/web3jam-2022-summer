import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import FungibleToken from 0xFungibleToken
import PioneerMarketplace from 0xPioneerNFT

transaction(admin:Address,
    name: String,
    description: String,
    url: String,
    saleItemID: UInt64, 
    partAmount: UFix64,
    createTime: UFix64,
    targetAmount: UFix64,
    divisionCount: UInt64,
    timeLength: UFix64,
    ){
let storefront: &PioneerMarketplace.Storefront
let flowReceiver: Capability<&AnyResource{FungibleToken.Provider, FungibleToken.Receiver}>
let PioneerNFTProvider: Capability<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
let creatorAddress: Address
let collectionRef:&{PioneerNFTs.PioneerNFTCollectionPublic

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

    self.collectionRef = account.getCapability<&{PioneerNFTs.PioneerNFTCollectionPublic}>(PioneerNFTs.CollectionPublicPath).borrow()??panic("no collectionRef")



     self.flowReceiver = account.getCapability<&FlowToken.Vault{FungibleToken.Provider,FungibleToken.Receiver}>(/public/flowTokenReceiver)
     // assert(self.flowReceiver.borrow() != nil, message: "Missing or mis-typed FlowToken receiver")

     let PioneerNFTCollectionProviderPrivatePath=/private/PioneerNFTCollection

     self.creatorAddress=account.address

     if !account.getCapability<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(PioneerNFTCollectionProviderPrivatePath)!.check() {
        account.link<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(PioneerNFTCollectionProviderPrivatePath, target: PioneerNFTs.CollectionStoragePath)
         }

   self.PioneerNFTProvider = account.getCapability<&PioneerNFTs.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(PioneerNFTCollectionProviderPrivatePath)!
    assert(self.PioneerNFTProvider.borrow() != nil, message: "Missing or mis-typed PioneerNFTs.Collection provider")
   }

execute{
    let admin = getAccount(admin)
    let adminStroeFront = admin.borrow<&PioneerMarketplace.Storefront>(from: PioneerMarketplace.StorefrontActivityStoragePath)!


    let adminCollectionRef = admin.getCapability<&{PioneerNFTs.PioneerNFTCollectionPublic}>(PioneerNFTs.CollectionPublicPath).borrow()??panic("no capability")
    
let activeID= adminStroeFront.createActivity(
    name:name,
    description:description,
    url:url,
    activeStatus:1,
    nftType: Type<@PioneerNFTs.NFT>(),
    nftID: saleItemID,
    creator:self.creatorAddress,
    createTime:createTime,
    targetAmount:targetAmount,
    currentAmount:0.0,
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
    minPrice: partAmount,
    itemStatus:1,
    activeID: activeID
    )

    let nftToken <- self.collectionRef.withdraw(withdrawID:saleItemID)
    adminCollectionRef.deposit(token:<-nftToken )
 }
}