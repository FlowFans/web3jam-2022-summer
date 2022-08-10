import PunstersNFT from "../contracts/Punsters.cdc"

pub fun main(): {UInt64: Address} {
    return PunstersNFT.getRegisteredPunsters();
}