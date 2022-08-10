export const getSaleNFTsScript = `

import MyNFT from 0xf2011014fb9bee77
import NonFungibleToken from 0x631e88ae7f1d7c20
import NFTMarketplace from 0xf2011014fb9bee77



pub fun main(account: Address): {UInt64: NFTMarketplace.SaleItem} {
    let saleCollection = getAccount(account).getCapability(/public/MySaleCollection)
                            .borrow<&NFTMarketplace.SaleCollection{NFTMarketplace.SaleCollectionPublic}>()
                            ?? panic("could not borrow the user's salecollection")

    let collection = getAccount(account).getCapability(/public/MyNFTCollection)
                            .borrow<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}>()
                            ?? panic("Cannot get the user's collection")
    
    let saleIDs = saleCollection.getIDs()

    let returnVals: {UInt64: NFTMarketplace.SaleItem} = {}

    for saleID in saleIDs {
        let price = saleCollection.getPrice(id: saleID)
        let nftRef = collection.borrowEntireNFT(id: saleID)

        returnVals.insert(key:nftRef.id, NFTMarketplace.SaleItem(_price: price,_nftref: nftRef))
    }

    return returnVals
  
}

`