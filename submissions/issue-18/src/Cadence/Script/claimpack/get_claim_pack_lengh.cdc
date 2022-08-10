// import SoulMadePack from "../../contracts/SoulMadePack.cdc"

// testnet
// import SoulMadePack from 0xb4187e54e0ed55a8
import SoulMadePack from 0x9a57dfe5c8ce609c

pub fun main(address: Address) : Int {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionFreeClaimPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
        
    return receiverRef.getIDs().length
}