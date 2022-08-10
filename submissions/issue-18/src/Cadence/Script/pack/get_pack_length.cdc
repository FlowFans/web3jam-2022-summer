// import SoulMadePack from "../../contracts/SoulMadePack.cdc"

// testnet
import SoulMadePack from 0x9a57dfe5c8ce609c
// import SoulMadePack from 0x76b2527585e45db4

pub fun main(address: Address) : Int {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
        
    return receiverRef.getIDs().length
    
}