import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import WeaponItems1 from "../../contracts/WeaponItems1.cdc"
import MetadataViews from "../../contracts/MetadataViews.cdc"

// This transaction configures an account to hold Kitty Items1.

transaction {
    prepare(signer: AuthAccount) {
        // if the account doesn't already have a collection
        if signer.borrow<&WeaponItems1.Collection>(from: WeaponItems1.CollectionStoragePath) == nil {

            // create a new empty collection
            let collection <- WeaponItems1.createEmptyCollection()
            
            // save it to the account
            signer.save(<-collection, to: WeaponItems1.CollectionStoragePath)

            // create a public capability for the collection
            signer.link<&WeaponItems1.Collection{NonFungibleToken.CollectionPublic, WeaponItems1.WeaponItemsCollectionPublic, MetadataViews.ResolverCollection}>(WeaponItems1.CollectionPublicPath, target: WeaponItems1.CollectionStoragePath)
        }
    }
}
