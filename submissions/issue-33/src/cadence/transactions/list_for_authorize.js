export const listForAuthorizeTx = `

import NFTCharityMarket from 0xf2011014fb9bee77

transaction(id:UInt64, price: UFix64) {

  prepare(acct: AuthAccount) {
    let saleCollection = acct.borrow<&NFTCharityMarket.AuthorizeCollection>(from: /storage/MyAuthorizeCollection) ?? panic("This AuthorizeCollection does not exist")
    saleCollection.listForAuthorize(id: id, price: price)

  }

  execute {
    log("A user stored a SaleCollection inside their account")
  }
}

`