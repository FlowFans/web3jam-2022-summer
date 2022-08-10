export const SELL_N_ITEM = `
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

 

   
   }

execute{
   
   
    
  
}
`