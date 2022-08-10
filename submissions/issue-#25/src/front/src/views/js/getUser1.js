export const getUser1 = `
import ExampleNFTUser from 0xb096b656ab049551

pub fun main(addr: Address): {String: [UInt64]} {
    let acct = getAccount(addr)
    let ref = acct.getCapability<&{ExampleNFTUser.NFTUserCollectionPublic}>(
        ExampleNFTUser.CollectionPublicPath
    ).borrow() ?? panic("no nftUser collection")
    let ans: {String: [UInt64]} = {}
    let ids: [UInt64] = ref.getIDs()
    for id in ids {
        let nftref = ref.borrowUserNFT(uuid: id)!
        if(nftref.expired > getCurrentBlock().height) {
            if(ans[nftref.type] == nil) {
                ans[nftref.type] = [nftref.token_id]
            }
            else {
                ans[nftref.type]!.append(nftref.token_id)
            }
        }
    }
    return ans
}
`
