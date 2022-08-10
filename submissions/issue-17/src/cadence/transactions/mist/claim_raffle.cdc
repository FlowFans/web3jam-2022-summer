import NonFungibleToken from "../contracts/core/NonFungibleToken.cdc"
import ExampleNFT from "../contracts/examplenft/ExampleNFT.cdc"
import Mist from "../contracts/Mist.cdc"
import MetadataViews from "../../contracts/core/MetadataViews.cdc"

transaction(raffleID: UInt64, host: Address) {
    let raffle: &{Mist.IRafflePublic}
    let receiver: &{NonFungibleToken.CollectionPublic}

    prepare(acct: AuthAccount) {
        let raffleCollection = getAccount(host)
            .getCapability(Mist.RaffleCollectionPublicPath)
            .borrow<&Mist.RaffleCollection{Mist.IRaffleCollectionPublic}>()
            ?? panic("Could not borrow the public RaffleCollection from the host")
        
        let raffle = raffleCollection.borrowPublicRaffleRef(raffleID: raffleID)
            ?? panic("Could not borrow the public Raffle from the collection")

        if (acct.borrow<&ExampleNFT.Collection>(from: ExampleNFT.CollectionStoragePath) == nil) {
            acct.save(<-ExampleNFT.createEmptyCollection(), to: ExampleNFT.CollectionStoragePath)

            acct.link<&{ExampleNFT.ExampleNFTCollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Receiver, MetadataViews.ResolverCollection}>(
                ExampleNFT.CollectionPublicPath,
                target: ExampleNFT.CollectionStoragePath
            )
        }
        
        self.raffle = raffle 
        self.receiver = acct
            .getCapability(ExampleNFT.CollectionPublicPath)
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not borrow Receiver")
    }

    execute {
        self.raffle.claim(receiver: self.receiver, params: {})
    }
}