import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"

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

pub fun main(activityID:UInt64):ActivityDetail{
    let detail=PioneerMarketplace.getActivity(id:activityID)

    let owner=detail.creator

    let do =PioneerMarketplace.activityUserTicket[activityID]!
    return ActivityDetail(
          owner:owner,
          Donor:do,
          activity:detail
    )
}

