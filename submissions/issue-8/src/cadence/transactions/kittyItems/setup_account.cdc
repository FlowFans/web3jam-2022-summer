import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import OnlyBadges from "../../contracts/OnlyBadges.cdc"
import MetadataViews from "../../contracts/MetadataViews.cdc"

// This transaction configures an account to hold Kitty Items.

transaction {
    prepare(signer: AuthAccount) {
        // if the account doesn't already have a collection
        if signer.borrow<&OnlyBadges.Collection>(from: OnlyBadges.CollectionStoragePath) == nil {

            // create a new empty collection
            let collection <- OnlyBadges.createEmptyCollection()
            
            // save it to the account
            signer.save(<-collection, to: OnlyBadges.CollectionStoragePath)

            // create a public capability for the collection
            signer.link<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic, MetadataViews.ResolverCollection}>(OnlyBadges.CollectionPublicPath, target: OnlyBadges.CollectionStoragePath)
        }
    }
}
