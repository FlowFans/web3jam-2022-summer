import PioneerNFTs from "../cadence/PioneerNFTs.cdc"
import NonFungibleToken from "../cadence/NonFungibleToken.cdc"
import FlowToken from "../cadence/FlowToken.cdc"
import FungibleToken from "../cadence/FungibleToken.cdc"
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"

  transaction(listingResourceID: UInt64, storefrontAddress: Address) {

      let paymentVault: @FungibleToken.Vault
      let PioneerNFTCollection: &PioneerNFTs.Collection{NonFungibleToken.Receiver}
      let storefront: &PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}
      let saleOffer: &PioneerMarketplace.Listing{PioneerMarketplace.ListingPublic}
  
      prepare(account: AuthAccount) {
          self.storefront = getAccount(storefrontAddress)
              .getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(
                  PioneerMarketplace.StorefrontPublicPath
              )!
              .borrow()
              ?? panic("Cannot borrow Storefront from provided address")
  
          self.saleOffer = self.storefront.borrowListing(listingResourceID: listingResourceID)
              ?? panic("No offer with that ID in Storefront")
  
          let price = self.saleOffer.getDetails().salePrice!
  
          let mainflowTokenVault = account.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)
              ?? panic("Cannot borrow flow vault from account storage")
  
          self.paymentVault <- mainflowTokenVault.withdraw(amount: price)


          
          if account.borrow<&PioneerNFTs.Collection>(from: PioneerNFT.CollectionStoragePath) == nil {

            // create a new TopShot Collection
            let collection <- PioneerNFT.createEmptyCollection() as! @PioneerNFT.Collection

            // Put the new Collection in storage
            account.save(<-collection, to: PioneerNFT.CollectionStoragePath)

            // create a public capability for the collection
            account.link<&{NonFungibleToken.CollectionPublic, PioneerNFT.PioneerNFTCollectionPublic}>(PioneerNFT.CollectionPublicPath, target: PioneerNFT.CollectionStoragePath)
        }
          
           
          self.PioneerNFTCollection = account.borrow<&PioneerNFTs.Collection{NonFungibleToken.Receiver}>(
              from: PioneerNFT.CollectionStoragePath
          ) ?? panic("Cannot borrow PioneerNFT collection receiver from account")
      }
  
      execute {
          let item <- self.saleOffer.purchase(
              payment: <-self.paymentVault
          )
          self.PioneerNFTCollection.deposit(token: <-item)
          self.storefront.cleanup(listingResourceID: listingResourceID)
      }
  }