import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

transaction {
    prepare(signer: AuthAccount) {
        if signer.borrow<&WakandaPass.Collection>(from: WakandaPass.CollectionStoragePath) == nil {
            let collection <- WakandaPass.createEmptyCollection()
            signer.save(<-collection, to: WakandaPass.CollectionStoragePath)
            signer.link<&WakandaPass.Collection{NonFungibleToken.CollectionPublic,
            WakandaPass.WakandaPassCollectionPublic}>(WakandaPass.CollectionPublicPath,
            target: WakandaPass.CollectionStoragePath)
        }
    }
}
