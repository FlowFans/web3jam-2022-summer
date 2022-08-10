export const LIST_FOR_SALE = `
    import ExampleRentMarketplace from 0xb096b656ab049551

    transaction(tokenID: UInt64, price: UFix64, expired: UInt64) {
        let saleCollection: &ExampleRentMarketplace.SaleCollection
        prepare(acct: AuthAccount){
            self.saleCollection = acct.borrow<&ExampleRentMarketplace.SaleCollection>(
                from: /storage/ExampleRentMarketplace1
            ) ?? panic("no saleCollection")
        }
        execute{
            self.saleCollection.listForSale(tokenID: tokenID, price: price, expired: expired)
        }
    }
`
