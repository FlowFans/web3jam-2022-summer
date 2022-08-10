import NonFungibleToken from "../contracts/core/NonFungibleToken.cdc"
import Mist from "../contracts/Mist.cdc"

transaction(raffleID: UInt64, host: Address) {
    let raffle: &{Mist.IRafflePublic}
    let address: Address

    prepare(acct: AuthAccount) {
        self.address = acct.address

        let raffleCollection = getAccount(host)
            .getCapability(Mist.RaffleCollectionPublicPath)
            .borrow<&Mist.RaffleCollection{Mist.IRaffleCollectionPublic}>()
            ?? panic("Could not borrow the public RaffleCollection from the host")
        
        let raffle = raffleCollection.borrowPublicRaffleRef(raffleID: raffleID)
            ?? panic("Could not borrow the public Raffle from the collection")

        self.raffle = raffle 
    }

    execute {
        self.raffle.register(account: self.address, params: {})
    }
}