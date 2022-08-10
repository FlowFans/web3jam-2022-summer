import ExampleNFT from 0xb10db40892311e63
import NonFungibleToken from 0x631e88ae7f1d7c20
import MetadataViews from 0x631e88ae7f1d7c20

transaction {
    prepare(acct: AuthAccount) {
        let collection <- ExampleNFT.createEmptyCollection()
        acct.save(<-collection, to: ExampleNFT.CollectionStoragePath)
        acct.link<&ExampleNFT.Collection{NonFungibleToken.CollectionPublic, ExampleNFT.ExampleNFTCollectionPublic, MetadataViews.ResolverCollection}>(
            ExampleNFT.CollectionPublicPath,
            target: ExampleNFT.CollectionStoragePath
        )
    }
}