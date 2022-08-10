import ExampleMarketplace from 

transaction(tokenID: UInt64, price: UFix64) {
    let saleCollection: &ExampleMarketplace.SaleCollection
    prepare(acct: AuthAccount){
        self.saleCollection = acct.borrow<&ExampleMarketplace.SaleCollection>(
            from: /storage/ExampleMarketplace
        )
    }
    execute{
        self.saleCollection.listForSale(tokenID: tokenID, price: price)
    }
}