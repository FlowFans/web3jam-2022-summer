import SoulMade from 0xf8d6e0586b0a20c7
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

import FungibleToken from 0xee82856bf20e2aa6
import FUSD from 0xf8d6e0586b0a20c7
import NFTStorefront from 0xf8d6e0586b0a20c7


// transaction() {

//   let fusdProvider: Capability<&FUSD.Vault{FungibleToken.Provider}>
//   let nftReceiver: Capability<&{NonFungibleToken.Receiver}>

//   prepare(acct: AuthAccount) {

//     self.fusdProvider = acct.getCapability<&FUSD.Vault{FungibleToken.Provider}>(/private/fusdVault)
//     assert(self.fusdProvider.borrow() != nil, message: "Missing or mis-typed FUSD Provider")

//     // todo: is this the right way of doing so?
//     //var nftPublicPath = nftType == Type<@SoulMadeMain.NFT>() ? SoulMadeMain.CollectionPublicPath : SoulMadeComponent.CollectionPublicPath
//     var nftPublicPath = SoulMadeMain.CollectionPublicPath

//     self.nftReceiver = acct.getCapability<&{NonFungibleToken.Receiver}>(nftPublicPath)!
//     assert(self.nftReceiver.borrow() != nil, message: "Missing or mis-typed SoulMadeMain.Collection provider")

//   }

//   execute {
//     SoulMade.buyItme(listingResourceID: UInt64(26), paymentVaultCapability: self.fusdProvider, nftReceiverCapability: self.nftReceiver)
//   }
// }





transaction(listingResourceID: UInt64, nftType: String) {
  let paymentVault: @FungibleToken.Vault
  //let nftCollection: &{NonFungibleToken.Receiver}
  let nftCollection: &{SoulMadeMain.CollectionPublic}
  let storefront: &NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}
  let listing: &NFTStorefront.Listing{NFTStorefront.ListingPublic}

  prepare(acct: AuthAccount) {
    let storefrontAddress : Address = 0xf8d6e0586b0a20c7
    self.storefront = getAccount(storefrontAddress)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow Storefront from provided address")

    self.listing = self.storefront.borrowListing(listingResourceID: listingResourceID)
                ?? panic("No Offer with that ID in Storefront")

    
    let price = self.listing.getDetails().salePrice

    let fusdVault = acct.borrow<&FUSD.Vault>(from: /storage/fusdVault)
        ?? panic("Cannot borrow FUSD vault from acct storage")

    self.paymentVault <- fusdVault.withdraw(amount: price)

    //self.nftCollection = acct.getCapability<&{NonFungibleToken.Receiver}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Cannot borrow NFT collection receiver from account")
    self.nftCollection = acct.getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Cannot borrow NFT collection receiver from account")
    
  }

  execute {

    SoulMade.buyItme(listing: self.listing, paymentVault: <- self.paymentVault, nftReceiver: self.nftCollection)

  }

  //- Post to check item is in collection?
}