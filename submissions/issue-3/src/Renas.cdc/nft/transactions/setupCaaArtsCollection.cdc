import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaArts from "../../contracts/CaaArts.cdc"

transaction {

    prepare(signer: AuthAccount) {
        if signer.borrow<&CaaArts.Collection>(from: CaaArts.CollectionStoragePath) == nil {

            let collection <- CaaArts.createEmptyCollection() as! @CaaArts.Collection

            signer.save(<-collection, to: CaaArts.CollectionStoragePath)

            signer.link<&{NonFungibleToken.CollectionPublic, CaaArts.CollectionPublic}>(
                CaaArts.CollectionPublicPath,
                target: CaaArts.CollectionStoragePath)
        }
    }
}
