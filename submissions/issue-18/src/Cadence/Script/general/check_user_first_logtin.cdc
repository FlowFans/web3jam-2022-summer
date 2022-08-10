// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

// testnet
import SoulMadeMain from 0xb4187e54e0ed55a8

pub fun main(address: Address) : Bool {
    return getAccount(address).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()
}