import PunstersNFT from "../contracts/Punsters.cdc"

pub fun main(addr: Address): UInt32? {
    return PunstersNFT.getPunsterFunnyIndex(ownerAddr: addr);
}
