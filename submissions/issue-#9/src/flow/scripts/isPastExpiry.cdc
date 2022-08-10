import PioneerNFT from "../cadence/PioneerNFT.cdc"
import NonFungibleToken from "../cadence/NonFungibleToken.cdc"
import FlowToken from "../cadence/FlowToken.cdc"
import FungibleToken from "../cadence/FungibleToken.cdc"
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"



pub fun main(id :UInt64):Bool{
    let activityDetail=PioneerMarketplace.getActivity(id:id)
    let isPastExpiry: Bool = getCurrentBlock().timestamp >= activityDetail.endTime

    return isPastExpiry
}