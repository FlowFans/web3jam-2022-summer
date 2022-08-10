//上架
export const SELL_ITEM = `
import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import FungibleToken from 0xFungibleToken
import PioneerMarketplace from 0xPioneerNFT

transaction(saleItemID: UInt64, saleItemPrice: UFix64){
    let salePublicRef:&{PioneerNFTs.SalePublic}
    prepare(account: AuthAccount) {
        PioneerNFTs.addSaleList()
        
        if account.borrow<&{PioneerNFTs.SalePublic}>(from: PioneerNFTs.SaleCollectionStoragePath) == nil {

            // Create a new empty saleCollection
            let saleCollection <- PioneerNFTs.createSaleCollection() as! @PioneerNFTs.SaleCollection
            account.save(<- saleCollection,to:PioneerNFTs.SaleCollectionStoragePath)
            account.link<&{PioneerNFTs.SalePublic}>(PioneerNFTs.SaleCollectionPublicPath,target:PioneerNFTs.SaleCollectionStoragePath)
        }

        self.salePublicRef = account.getCapability<&{PioneerNFTs.SalePublic}>(PioneerNFTs.SaleCollectionPublicPath).borrow()??panic("Could not borrow a reference to the NFT PioneerSaleCollection")
    }

    
    execute{
        self.salePublicRef.addSaleList(tokenID:saleItemID,price:saleItemPrice)
    }
}
`
