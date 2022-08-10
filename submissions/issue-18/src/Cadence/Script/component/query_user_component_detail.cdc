// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

import SoulMadeComponent from 0x9a57dfe5c8ce609c

pub fun main(address: Address, componentNftId: UInt64) : SoulMadeComponent.ComponentDetail {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    return receiverRef.borrowComponent(id : componentNftId)!.componentDetail
    
}
