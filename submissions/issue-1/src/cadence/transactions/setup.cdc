import NonFungibleToken from 0xdaf76cab293e4369
import WakandaPass from 0xdaf76cab293e4369

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
