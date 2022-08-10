export const GET_NFTS_MARKET = `
import PioneerNFTs from 0xPioneerNFT

pub fun main():{UInt64: PioneerNFTs.PriceMeta} {
    let arr=PioneerNFTs.getIDs()
    return arr
}
`