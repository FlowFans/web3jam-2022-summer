export const GET_NFT_DETAIL = `
import PioneerNFTs from 0xPioneerNFT

pub fun main(account: Address): [UInt64] {

    let account = getAccount(account)

    let collectionRef = account.getCapability(PioneerNFT.CollectionPublicPath)
                            .borrow<&{PioneerNFT.PioneerNFTCollectionPublic}>()!

    return collectionRef.getIDs()
}
`