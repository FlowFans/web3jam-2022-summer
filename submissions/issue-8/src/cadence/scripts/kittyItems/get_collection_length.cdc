import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import OnlyBadges from "../../contracts/OnlyBadges.cdc"

// This script returns the size of an account's OnlyBadges collection.

pub fun main(address: Address): Int {
    let account = getAccount(address)

    let collectionRef = account.getCapability(OnlyBadges.CollectionPublicPath)!
        .borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Could not borrow capability from public collection")
    
    return collectionRef.getIDs().length
}
