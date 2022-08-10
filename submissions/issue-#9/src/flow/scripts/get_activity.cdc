import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"


pub fun main(id:UInt64):PioneerMarketplace.Activity{

    return PioneerMarketplace.getActivity(id:id)
}


