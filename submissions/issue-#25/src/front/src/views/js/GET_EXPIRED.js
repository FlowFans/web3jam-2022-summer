export const GET_EXPIRED = `
import ExampleNFTUser from 0xb096b656ab049551

pub fun main(acct: Address, ID: UInt64): UInt64{
    let account = getAccount(acct)
    let collectionRef = account.getCapability<&{ExampleNFTUser.NFTUserCollectionPublic}>(ExampleNFTUser.CollectionPublicPath)
                        .borrow() ?? panic("no resource")
    let ids = collectionRef.getIDs()
    for id in ids {
        let nftref = collectionRef.borrowUserNFT(uuid: id)!
        if(nftref.token_id == ID) {
            return (nftref.expired - getCurrentBlock().height)
        }
    }
    return 0
}
`
