 //上架
export const SELL_NFT = `
import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import FungibleToken from 0xFungibleToken
import PioneerMarketplace from 0xPioneerNFT

transaction(id:UInt64,url:String,price:UFix64,name: String,description: String,officialUrl: String,creator: Address){
    prepare(account: AuthAccount) {
    }
    execute{
        PioneerNFTs.addSaleList(
            id:id,
            url:url,
            price:price,
            name:name,
            description:description,
            officialUrl:officialUrl,
            creator:creator,
        )
    }
}
`