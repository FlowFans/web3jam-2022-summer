
import ExampleNFT from 0xb096b656ab049551
pub fun main(addr: Address): [UInt64]{
    let acct = getAccount(addr)
    let ref = acct.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(
        ExampleNFT.CollectionPublicPath
    ).borrow() ?? panic("no collection")
    return ref.getIDs()
}
