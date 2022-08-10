import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

pub fun main(address: Address): Int {
    let account = getAccount(address)
    let collectionRef = account.getCapability(WakandaPass.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Could not borrow capability from public collection")
    return collectionRef.getIDs().length
}
