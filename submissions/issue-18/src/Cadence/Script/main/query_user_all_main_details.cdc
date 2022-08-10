// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
import SoulMadeMain from 0x421c19b7dc122357
import SoulMadeComponent from 0x421c19b7dc122357

// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20


pub fun main(address: Address) : [{UInt64:  SoulMadeMain.MainDetail}] {
    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")

    var res : [{UInt64: SoulMadeMain.MainDetail}] = []
    for mainId in receiverRef.getIDs(){
        res.append({mainId : receiverRef.borrowMain(id: mainId).mainDetail})
    }
    return res
}



