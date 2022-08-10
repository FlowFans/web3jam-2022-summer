import Mist from "../contracts/Mist.cdc"
import NonFungibleToken from "../contracts/core/NonFungibleToken.cdc"
import ExampleNFT from "../contracts/examplenft/ExampleNFT.cdc"

transaction(raffleID: UInt64) {
    let raffleCollection: &Mist.RaffleCollection
    let nftCollectionRef: &ExampleNFT.Collection{NonFungibleToken.CollectionPublic}

    prepare(acct: AuthAccount) {
        self.raffleCollection = acct.borrow<&Mist.RaffleCollection>(from: Mist.RaffleCollectionStoragePath)
            ?? panic("Could not borrow raffleCollection")

        let raffle = self.raffleCollection.borrowRaffleRef(raffleID: raffleID)!

        self.nftCollectionRef = acct.borrow<&ExampleNFT.Collection{NonFungibleToken.CollectionPublic}>(from: raffle.nftInfo.collectionStoragePath)
            ?? panic("Could not borrow collection from signer")
    }

    execute {
        self.raffleCollection.deleteRaffle(raffleID: raffleID, receiver: self.nftCollectionRef)
    }
}