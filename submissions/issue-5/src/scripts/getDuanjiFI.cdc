import PunstersNFT from "../contracts/Punsters.cdc"

pub fun main(addr: Address, id: UInt64): UInt32? {
    return PunstersNFT.getDuanjiFunnyIndex(ownerAddr: addr, duanjiID: id);
}