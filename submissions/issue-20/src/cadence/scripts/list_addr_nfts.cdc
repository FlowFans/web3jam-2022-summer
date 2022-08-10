import ExampleNFT from 0xb10db40892311e63

pub fun main(addr: Address): [UInt64] {
    let acct = getAccount(addr)
    let collectionRef = acct.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath)
                        .borrow() ?? panic("no collection resource")
    return collectionRef.getIDs()
}