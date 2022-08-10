export const listForSaleTx = `


import NFTMarketplace from 0xf2011014fb9bee77

transaction(id:UInt64, price: UFix64) {

  prepare(acct: AuthAccount) {
    let saleCollection = acct.borrow<&NFTMarketplace.SaleCollection>(from: /storage/MySaleCollection) ?? panic("This SaleCollection does not exist")
    saleCollection.listForSale(id: id, price: price)

  }

  execute {
    log("A user listed and NFT for sale")
  }
}


`