export const GET_ADDRESS_NFT_DETAIL = `
import PioneerNFTs from 0xPioneerNFT

pub fun main(account: Address):[PioneerNFTs.PioneerNFTMetadataView]{
    let account = getAccount(account)
    let collectionRef = account.getCapability(PioneerNFTs.CollectionPublicPath)
                            .borrow<&{PioneerNFTs.PioneerNFTCollectionPublic}>()!
    let arr=collectionRef.getIDs()
    var i=0
    var res:[PioneerNFTs.PioneerNFTMetadataView]=[]

    while i<arr.length {
        let nft =collectionRef.borrowPioneerNFT(id: arr[i]) ?? panic("The NFT does not exist")
        let view = nft.resolveView(Type<PioneerNFTs.PioneerNFTMetadataView>())!
        let PioneerNFTDisplay =view as! PioneerNFTs.PioneerNFTMetadataView
        res.append(PioneerNFTDisplay)
        i=i+1
    }
    return res
}
`