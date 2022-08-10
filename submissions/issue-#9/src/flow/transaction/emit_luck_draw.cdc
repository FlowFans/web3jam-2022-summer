import PioneerNFT from "../cadence/PioneerNFT.cdc"
import NonFungibleToken from "../cadence/NonFungibleToken.cdc"
import FlowToken from "../cadence/FlowToken.cdc"
import FungibleToken from "../cadence/FungibleToken.cdc"
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"
////activeID: UInt64,acc:Address, payment: @FungibleToken.Vault,ticketAmount: UFix64
//activeID: UInt64,current: UFie






  transaction(activeID: UInt64,listingResourceID: UInt64, storefrontAddress: Address) {

        let storefront: &PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}
        let saleOffer: &PioneerMarketplace.Listing{PioneerMarketplace.ListingPublic}
       
        prepare(account:AuthAccount){
                self.storefront = getAccount(storefrontAddress)
                        .getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(
                            PioneerMarketplace.StorefrontPublicPath
                        )!
                        .borrow()
                        ?? panic("Cannot borrow Storefront from provided address")

            self.saleOffer = self.storefront.borrowListing(listingResourceID: listingResourceID)
              ?? panic("No offer with that ID in Storefront")
        
        }
        

        execute {
            self.saleOffer.selectWinner(activeID: activeID)

        }
     
     
     
     
   
  }