import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import WeaponItems1 from "../../contracts/WeaponItems1.cdc"

// This script returns the size of an account's WeaponItems1 collection.

pub fun main(address: Address): Int {
    let account = getAccount(address)

    let collectionRef = account.getCapability(WeaponItems1.CollectionPublicPath)!
        .borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Could not borrow capability from public collection")
    
    return collectionRef.getIDs().length
}
