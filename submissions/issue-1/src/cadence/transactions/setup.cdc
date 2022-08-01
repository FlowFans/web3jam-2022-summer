import NonFungibleToken from 0x01
import WakandaPass from 0x02

// This transaction configures an account to hold and mint Cogito.
transaction {
    prepare(signer: AuthAccount) {
        // if the account doesn't already have a collection
        if signer.borrow<&WakandaPass.Collection>(from: WakandaPass.CollectionStoragePath) == nil {
            // create a new empty collection
            let collection <- WakandaPass.createEmptyCollection()
            // save it to the account
            signer.save(<-collection, to: WakandaPass.CollectionStoragePath)
            // create a public capability for the collection
            signer.link<&WakandaPass.Collection{NonFungibleToken.CollectionPublic,
            WakandaPass.WakandaPassCollectionPublic}>(WakandaPass.CollectionPublicPath,
            target: WakandaPass.CollectionStoragePath)
        }
    }
}

// transfer token

