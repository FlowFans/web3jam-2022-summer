// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

import SoulMadeComponent from 0xb4187e54e0ed55a8
pub fun main(address: Address, componentNftIds: [UInt64]) : [SoulMadeComponent.ComponentDetail] {

    let receiverRef = getAccount(address)
                    .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    var res : [SoulMadeComponent.ComponentDetail] = []
    for componentNftId in componentNftIds{
        res.append(receiverRef.borrowComponent(id : componentNftId)!.componentDetail)
    }
    return res
}