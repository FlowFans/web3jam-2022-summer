import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaPass from "../../contracts/CaaPass.cdc"

transaction {

    prepare(signer: AuthAccount) {
        if signer.borrow<&CaaPass.Collection>(from: CaaPass.CollectionStoragePath) == nil {

            let collection <- CaaPass.createEmptyCollection() as! @CaaPass.Collection

            signer.save(<-collection, to: CaaPass.CollectionStoragePath)

            signer.link<&{NonFungibleToken.CollectionPublic, CaaPass.CollectionPublic}>(
                CaaPass.CollectionPublicPath,
                target: CaaPass.CollectionStoragePath)
        }
    }
}
