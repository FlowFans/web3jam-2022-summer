export const unlistFromAuthorizeTx = `
import NFTCharityMarket from 0xf2011014fb9bee77
transaction(id: UInt64) {
  prepare(acct: AuthAccount) {
    let saleCollection = acct.borrow<&&NFTCharityMarket.AuthorizeCollection>(from: /storage/MyAuthorizeCollection)
                            ?? panic("This SaleCollection does not exist")
    saleCollection.unlistFromAuthorize(id: id)
  }
  execute {
    log("A user unlisted an NFT for Sale")
  }
}
`