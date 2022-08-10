// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMade from "../../contracts/SoulMade.cdc"

// testnet
import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMade from 0xb4187e54e0ed55a8

pub fun main(address: Address, mainNftIds: [UInt64]) : [SoulMadeMain.MainDetail] {
    let res : [SoulMadeMain.MainDetail] = []
    for mainNftId in mainNftIds {
        res.append(SoulMade.getMainDetail(address:address, mainNftId:mainNftId))
    }
    return res
}

