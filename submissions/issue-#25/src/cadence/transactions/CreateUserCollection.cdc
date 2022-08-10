import NonFungibleToken from "../NonFungibleToken.cdc"
import ExampleNFTUser from "../ExampleNFTUser.cdc"

transaction{
    let acct: AuthAccount
    prepare(acct: AuthAccount){
        self.acct = acct
    }
    execute{
        self.acct.save(<- ExampleNFTUser.createEmptyCollection(),to: ExampleNFTUser.CollectionStoragePath)
        self.acct.link<&ExampleNFTUser.Collection{NonFungibleToken.CollectionPublic, ExampleNFTUser.NFTUserCollectionPublic}>(
        ExampleNFTUser.CollectionPublicPath,
        target: ExampleNFTUser.CollectionStoragePath
        )
    }
}