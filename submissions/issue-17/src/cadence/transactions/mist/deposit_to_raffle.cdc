import ExampleNFT from "../contracts/examplenft/ExampleNFT.cdc"
import MetadataViews from "../contracts/core/MetadataViews.cdc"
import Mist from "../contracts/Mist.cdc"

transaction(raffleID: UInt64, tokenIDs: [UInt64]) {
    let raffle: &Mist.Raffle
    let nftCollectionRef: &ExampleNFT.Collection
    let displays: {UInt64: Mist.NFTDisplay}

    prepare(acct: AuthAccount) {
        let raffleCollection = acct.borrow<&Mist.RaffleCollection>(from: Mist.RaffleCollectionStoragePath)
            ?? panic("Could not borrow raffleCollection")

        self.raffle = raffleCollection.borrowRaffleRef(raffleID: raffleID)!

        self.nftCollectionRef = acct.borrow<&ExampleNFT.Collection>(from: self.raffle.nftInfo.collectionStoragePath)
            ?? panic("Could not borrow collection from signer")
    
        self.displays = {}
        for tokenID in tokenIDs {
            let resolver = self.nftCollectionRef.borrowViewResolver(id: tokenID)
            let mDisplay = MetadataViews.getDisplay(resolver)!
            let display = Mist.NFTDisplay(
                tokenID: tokenID,
                name: mDisplay.name,
                description: mDisplay.description,
                thumbnail: mDisplay.thumbnail.uri()
            )
            self.displays[tokenID] = display
        } 
    }

    execute {
        for tokenID in tokenIDs {
            let token <- self.nftCollectionRef.withdraw(withdrawID: tokenID)
            let display = self.displays[tokenID]!
            self.raffle.deposit(token: <- token, display: display)
        }
    }
}