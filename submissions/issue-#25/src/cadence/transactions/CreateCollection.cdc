import NonFungibleToken from "../NonFungibleToken.cdc"
import ExampleNFT from "../ExampleNFT.cdc"

transaction{
    prepare(acct: AuthAccount){
        acct.save(<- ExampleNFT.createEmptyCollection(),to:ExampleNFT.CollectionStoragePath)
        acct.link<&ExampleNFT.Collection{NonFungibleToken.CollectionPublic, ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath,target:ExampleNFT.CollectionStoragePath)
    }

    execute{

    }
}