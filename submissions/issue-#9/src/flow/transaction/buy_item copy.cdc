import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import FungibleToken from 0xFungibleToken
import PioneerMarketplace from 0xPioneerNFT

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
      }
  
      execute {
    let storeFrontTokenVault = getAccount(storefrontAddress).getCapability<&{FungibleToken.Receiver}>(from: /public/flowTokenVault).borrow()??("no valut")
    log("Transfer succeeded!")
          self.storeFrontTokenVault.deposit(token: <-self.paymentVault)
      }
  }