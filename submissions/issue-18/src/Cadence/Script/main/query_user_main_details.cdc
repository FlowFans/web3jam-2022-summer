// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMade from "../../contracts/SoulMade.cdc"

// testnet
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMade from 0x9a57dfe5c8ce609c

pub fun main(address: Address, mainNftId: UInt64) : SoulMadeMain.MainDetail {
    return SoulMade.getMainDetail(address: address, mainNftId: mainNftId)
}
 