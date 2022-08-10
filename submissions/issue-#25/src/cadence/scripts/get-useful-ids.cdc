
import ExampleNFT from 0xb096b656ab049551
import ExampleNFTUser from 0xb096b656ab049551
pub fun main(address: Address): [UInt64]{
    let acct = getAccount(address)
    let ref = acct.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(
        ExampleNFT.CollectionPublicPath
    ).borrow() ?? panic("no collection")
    let ids = ref.getIDs()
    let ans: [UInt64] = []
    for id in ids {
        let nftref = ref.borrowNFT(id: id)
        if(!ExampleNFTUser.getExpired(uuid: nftref.uuid)) {
            ans.append(id)
        }
    }
    return ans
}
