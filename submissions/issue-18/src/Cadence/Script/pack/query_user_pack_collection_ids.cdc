// import SoulMadePack from "../../contracts/SoulMadePack.cdc"

// testnet
import SoulMadePack from 0x421c19b7dc122357
// import SoulMadePack from 0x76b2527585e45db4

pub fun main(address: Address) : [UInt64] {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
        
    return receiverRef.getIDs()
}