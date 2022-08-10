import Mist from "../contracts/Mist.cdc"
import NonFungibleToken from "../contracts/core/NonFungibleToken.cdc"
import ExampleNFT from "../contracts/examplenft/ExampleNFT.cdc"

transaction(raffleID: UInt64) {
    let raffle: &Mist.Raffle
    let nftCollectionRef: &ExampleNFT.Collection{NonFungibleToken.CollectionPublic}

    prepare(acct: AuthAccount) {
        let raffleCollection = acct.borrow<&Mist.RaffleCollection>(from: Mist.RaffleCollectionStoragePath)
            ?? panic("Could not borrow raffleCollection")

        self.raffle = raffleCollection.borrowRaffleRef(raffleID: raffleID)!

        self.nftCollectionRef = acct.borrow<&ExampleNFT.Collection{NonFungibleToken.CollectionPublic}>(from: self.raffle.nftInfo.collectionStoragePath)
            ?? panic("Could not borrow collection from signer")
    }

    execute {
        self.raffle.end(receiver: self.nftCollectionRef)
    }
}