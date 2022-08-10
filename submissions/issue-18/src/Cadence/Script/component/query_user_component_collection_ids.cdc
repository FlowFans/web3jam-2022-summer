// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
import SoulMadeComponent from 0x421c19b7dc122357

pub fun main(address: Address) : [UInt64] {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
        
    return receiverRef.getIDs()

}
