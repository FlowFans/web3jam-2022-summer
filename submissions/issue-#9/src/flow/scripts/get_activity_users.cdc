import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"


//获取某个活动下的所有参与者

pub fun main(activityID:UInt64):[Address]{


    let arr= PioneerMarketplace.activityUserAmount[activityID]!

    return arr.keys

  
   
}