import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeComponent from 0x76b2527585e45db4

pub fun main(address: Address) : {String : [UInt64]} {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    var res : {String : [UInt64]} = {}
    for id in receiverRef.getIDs(){
        var name = receiverRef.borrowComponentNFT(id: id)!.componentDetail.name
        if res[name] == nil {
            res[name] = [id]
        } else {
            res[name]!.append(id)
        }
    }

    return res    
}