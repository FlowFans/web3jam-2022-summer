import SoulMade from 0x9a57dfe5c8ce609c

pub fun main(address: Address) : {String: [UInt64]} {        
    return SoulMade.getPackListingIdsPerSeries(address: address)
}