import Mist from "../contracts/Mist.cdc"

pub fun main(account: Address): {UInt64: &{Mist.IRafflePublic}} {
    let raffleCollection =
        getAccount(account)
        .getCapability(Mist.RaffleCollectionPublicPath)
        .borrow<&Mist.RaffleCollection{Mist.IRaffleCollectionPublic}>()

    if let collection = raffleCollection {
        return collection.getAllRaffles()
    }

    return {}
}