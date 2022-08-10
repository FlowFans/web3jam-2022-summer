import Cloud from "../contracts/Cloud.cdc"

pub fun main(dropID: UInt64, host: Address): UFix64 {
    let dropCollection =
        getAccount(host)
        .getCapability(Cloud.DropCollectionPublicPath)
        .borrow<&Cloud.DropCollection{Cloud.IDropCollectionPublic}>()
        ?? panic("Could not borrow IDropCollectionPublic from address")

    let drop = dropCollection.borrowPublicDropRef(dropID: dropID)
        ?? panic("Could not borrow drop")

    return drop.getDropBalance()
}
 