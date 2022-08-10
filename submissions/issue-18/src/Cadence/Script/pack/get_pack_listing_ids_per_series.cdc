// import SoulMade from "../../contracts/SoulMade.cdc"

// testnet
import SoulMade from 0xb4187e54e0ed55a8

pub fun main(address: Address) : {String: [UInt64]} {        
    return SoulMade.getPackListingIdsPerSeries(address: address)
}