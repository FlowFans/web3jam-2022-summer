import PioneerNFT from "../cadence/PioneerNFT.cdc"
import NonFungibleToken from "../cadence/NonFungibleToken.cdc"
import FlowToken from "../cadence/FlowToken.cdc"
import FungibleToken from "../cadence/FungibleToken.cdc"
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"
  transaction(activeID: UInt64,listingResourceID: UInt64, storefrontAddress: Address,ticketAmount: UFix64) {
      let bidAddress:Address
      let paymentVault: @FungibleToken.Vault
      let PioneerNFTCollection: &PioneerNFTs.Collection{NonFungibleToken.Receiver}
      let storefront: &PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}
      let saleOffer: &PioneerMarketplace.Listing{PioneerMarketplace.ListingPublic}
      let activityDetail:PioneerMarketplace.Activity
      prepare(account: AuthAccount) {
          self.storefront = getAccount(storefrontAddress)
              .getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(
                  PioneerMarketplace.StorefrontActivityPublicPath
              )!
              .borrow()
              ?? panic("Cannot borrow Storefront from provided address")
          self.activityDetail= PioneerMarketplace.getActivity(id:activeID)

          self.saleOffer = self.storefront.borrowListing(listingResourceID: listingResourceID)
              ?? panic("No offer with that ID in Storefront")

          let eachPrice=self.activityDetail.minPartAmount

          let bidTotal=ticketAmount*eachPrice
    
          let mainflowTokenVault = account.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)
              ?? panic("Cannot borrow flow vault from account storage")
  
          self.paymentVault <- mainflowTokenVault.withdraw(amount: bidTotal)
          
          if account.borrow<&PioneerNFTs.Collection>(from: PioneerNFT.CollectionStoragePath) == nil {

            // create a new TopShot Collection
            let collection <- PioneerNFT.createEmptyCollection() as! @PioneerNFT.Collection

            // Put the new Collection in storage
            account.save(<-collection, to: PioneerNFT.CollectionStoragePath)

            // create a public capability for the collection
            account.link<&{NonFungibleToken.CollectionPublic, PioneerNFT.PioneerNFTCollectionPublic}>(PioneerNFT.CollectionPublicPath, target: PioneerNFT.CollectionStoragePath)
        }
          
          self.bidAddress=account.address 
          self.PioneerNFTCollection = account.borrow<&PioneerNFTs.Collection{NonFungibleToken.Receiver}>(
              from: PioneerNFT.CollectionStoragePath
          ) ?? panic("Cannot borrow PioneerNFT collection receiver from account")
      }
  
      execute {
        //
        let isbid=self.saleOffer.bid(
            activeID:activeID,
            bidAddr:self.bidAddress,
            payment: <-self.paymentVault,
            ticketAmount:ticketAmount
        )
      }
  }