import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeComponent from 0x76b2527585e45db4

pub fun main(address: Address) : [{UInt64 : SoulMadeComponent.ComponentDetail}] {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    var res : [{UInt64 : SoulMadeComponent.ComponentDetail}] = []
    for id in receiverRef.getIDs(){
        res.append({id : receiverRef.borrowComponent(id: id)!.componentDetail})
    }

    return res    
}