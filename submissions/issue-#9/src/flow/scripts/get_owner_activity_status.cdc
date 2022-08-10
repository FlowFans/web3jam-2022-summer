

import PioneerNFT  from "../cadence/PioneerNFT.cdc"
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"


pub fun main(account: Address,id: UInt64): UInt8{
    let account = getAccount(account)
    let collectionRef = account.getCapability(PioneerMarketplace.StorefrontActivityPublicPath)
                            .borrow<&{PioneerMarketplace.StorefrontPublic}>()!
  
       let publicdetails= collectionRef.borrowListing(listingResourceID:id)!
        return publicdetails.getDetails().itemStatus
        
}
