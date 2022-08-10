import CaaPass from "../../contracts/CaaPass.cdc"

pub fun main(typeID: UInt64): CaaPass.Metadata? {
    return CaaPass.getMetadata(typeID: typeID)
}
