

import PioneerNFT  from "../cadence/PioneerNFT.cdc"
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"
//getListingIDs


pub struct ActivityDetail{
    pub let owner:Address
    pub let Donor:{Address:UFix64}
    pub let activity:PioneerMarketplace.Activity

    init(owner:Address,
         Donor:{Address:UFix64},
         activity:PioneerMarketplace.Activity
    ){
        self.owner=owner
        self.Donor=Donor
        self.activity=activity
    }

}

pub fun main(account: Address): [UInt64] {
    let account = getAccount(account)
    let collectionRef = account.getCapability(PioneerMarketplace.StorefrontPublicPath)
                            .borrow<&{PioneerMarketplace.StorefrontPublic}>()!
    let ids=collectionRef.getListingIDs()

    var i=0
    var res:[PioneerMarketplace.ListingDetails]=[]
    var resid:[UInt64]=[]

    while i<ids.length {
       let publicdetails= collectionRef.borrowListing(listingResourceID:ids[i])!
       let arr= publicdetails.getDetails()
       resid.append(arr.activeID)
       res.append(arr)
    }
    return resid
}


pub fun getActivityDetail(activityID:UInt64) :ActivityDetail{

  let detail=PioneerMarketplace.getActivity(id:activityID)

    let owner=detail.creator

    let do =PioneerMarketplace.activityUserTicket[activityID]!
    return ActivityDetail(
          owner:owner,
          Donor:do,
          activity:detail
    )
}