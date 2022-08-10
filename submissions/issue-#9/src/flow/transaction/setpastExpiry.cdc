



import PioneerNFT from "../cadence/PioneerNFT.cdc"
import NonFungibleToken from "../cadence/NonFungibleToken.cdc"
import FlowToken from "../cadence/FlowToken.cdc"
import FungibleToken from "../cadence/FungibleToken.cdc"
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"




  transaction(activeID: UInt64,listingResourceID: UInt64, storefrontAddress: Address) {
        execute {
            let storefront = getAccount(storefrontAddress)
                        .getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(
                            PioneerMarketplace.StorefrontPublicPath
                        )!
                        .borrow()
                        ?? panic("Cannot borrow Storefront from provided address")

            let saleOffer = storefront.borrowListing(listingResourceID: listingResourceID)
              ?? panic("No offer with that ID in Storefront")
              saleOffer.withdrawBids(activeID: activeID)

        }
     
     
     
     
   
  }