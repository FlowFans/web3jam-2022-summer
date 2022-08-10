import NonFungibleToken from "../contracts/NonFungibleToken.cdc"
import RelationNFT from "../contracts/RelationNFT.cdc"

pub fun main(address: Address): Int {
    let account = getAccount(address)

    let collectionRef = account
        .getCapability(RelationNFT.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Could not borrow capability from public collection")
    
    return collectionRef.getIDs().length
}
