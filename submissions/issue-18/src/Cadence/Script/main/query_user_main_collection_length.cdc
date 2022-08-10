// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

// testnet
// import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadeMain from 0x9a57dfe5c8ce609c

pub fun main(address: Address) : Int {
    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    return receiverRef.getIDs().length
}