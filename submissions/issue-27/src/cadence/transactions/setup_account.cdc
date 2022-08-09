import NonFungibleToken from "../contracts/NonFungibleToken.cdc"
import RelationNFT from "../contracts/RelationNFT.cdc"
import MetadataViews from "../contracts/MetadataViews.cdc"

/// This transaction is what an account would run
/// to set itself up to receive NFTs

transaction {

    prepare(signer: AuthAccount) {
        // Return early if the account already has a collection
        if signer.borrow<&RelationNFT.Collection>(from: RelationNFT.CollectionStoragePath) != nil {
            return
        }

        // Create a new empty collection
        let collection <- RelationNFT.createEmptyCollection()

        // save it to the account
        signer.save(<-collection, to: RelationNFT.CollectionStoragePath)

        // create a public capability for the collection
        signer.link<&{NonFungibleToken.CollectionPublic, RelationNFT.RelationNFTCollectionPublic, MetadataViews.ResolverCollection}>(
            RelationNFT.CollectionPublicPath,
            target: RelationNFT.CollectionStoragePath
        )
    }
}
