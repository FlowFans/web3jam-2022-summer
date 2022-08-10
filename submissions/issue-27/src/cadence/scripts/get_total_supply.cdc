import RelationNFT from "../contracts/RelationNFT.cdc"

pub fun main(): UInt64 {
    return RelationNFT.totalSupply
}
