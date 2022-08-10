import Cloud from "../contracts/Cloud.cdc"

pub fun main(account: Address): {UInt64: &{Cloud.IDropPublic}} {
    let dropCollection =
        getAccount(account)
        .getCapability(Cloud.DropCollectionPublicPath)
        .borrow<&Cloud.DropCollection{Cloud.IDropCollectionPublic}>()

    if let collection = dropCollection {
        return collection.getAllDrops()
    }

    return {}
}