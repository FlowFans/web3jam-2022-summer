export const getAuthorizeNFTsScript = `

import MyNFT from 0xf2011014fb9bee77
import NonFungibleToken from 0x631e88ae7f1d7c20
import NFTCharityMarket from 0xf2011014fb9bee77

pub fun main(account: Address): {UInt64: NFTCharityMarket.SaleItem} {
    let authCollection = getAccount(account).getCapability(/public/MyAuthorizeCollection)
                            .borrow<&NFTCharityMarket.AuthorizeCollection{NFTCharityMarket.AuthorizeCollectionPublic}>()
                            ?? panic("could not borrow the user's salecollection")

    let collection = getAccount(account).getCapability(/public/MyNFTCollection)
                            .borrow<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}>()
                            ?? panic("Cannot get the user's collection")
    
    let saleIDs = authCollection.getAuthorizeIDs()

    let returnVals: {UInt64: NFTCharityMarket.SaleItem} = {}

    for saleID in saleIDs {
        let price = authCollection.getAuthorizePrice(id: saleID)
        let nftRef = collection.borrowEntireNFT(id: saleID)

        returnVals.insert(key:nftRef.id, NFTCharityMarket.SaleItem(_price: price,_nftref: nftRef))
    }

    return returnVals
  
}

`