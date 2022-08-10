export const GET_USER_ACTIVITY = `
import PioneerMarketplace from 0xPioneerNFT

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

pub fun main(user: Address):[ActivityDetail]{
   let id_arr= PioneerMarketplace.userActivity[user]!

   var i=0
   var all_details: [ActivityDetail]=[]
   var addrKeys=PioneerMarketplace.activityMapping.keys

   while i<id_arr.length {
    all_details.append(getUserActivity(activityID:addrKeys[i]))
    i=i+1
   }
   return all_details
}

pub fun getUserActivity(activityID: UInt64):ActivityDetail{

    let detail=PioneerMarketplace.getActivity(id:activityID)

    let owner=detail.creator

    let do =PioneerMarketplace.activityUserTicket[activityID]!
    return ActivityDetail(
          owner:owner,
          Donor:do,
          activity:detail
    )
}
`