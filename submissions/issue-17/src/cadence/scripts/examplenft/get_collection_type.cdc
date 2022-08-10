import NonFungibleToken from "../../contracts/core/NonFungibleToken.cdc"
import ExampleNFT from "../../contracts/NFTCatalog/ExampleNFT.cdc"
import MetadataViews from "../../contracts/core/MetadataViews.cdc"

// This transaction is what an account would run
// to set itself up to receive NFTs

pub fun main(): Type {
    let collection <- ExampleNFT.createEmptyCollection() 
    let type = collection.getType()
    destroy collection
    return type
}