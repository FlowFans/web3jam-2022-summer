import Mist from "../contracts/Mist.cdc"

transaction(raffleID: UInt64) {
    let raffle: &Mist.Raffle

    prepare(acct: AuthAccount) {
        let raffleCollection = acct.borrow<&Mist.RaffleCollection>(from: Mist.RaffleCollectionStoragePath)
            ?? panic("Could not borrow raffleCollection")
        self.raffle = raffleCollection.borrowRaffleRef(raffleID: raffleID)!
    }

    execute {
        self.raffle.batchDraw(params: {})
    }
}