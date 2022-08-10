import Mist from "../contracts/Mist.cdc"

pub fun main(raffleID: UInt64, host: Address, claimer: Address): Mist.RegistrationRecord? {
    let raffleCollection =
        getAccount(host)
        .getCapability(Mist.RaffleCollectionPublicPath)
        .borrow<&Mist.RaffleCollection{Mist.IRaffleCollectionPublic}>()
        ?? panic("Could not borrow IRaffleCollectionPublic from address")

    let raffle = raffleCollection.borrowPublicRaffleRef(raffleID: raffleID)
        ?? panic("Could not borrow raffle")
    
    return raffle.getRegistrationRecord(account: claimer)
}