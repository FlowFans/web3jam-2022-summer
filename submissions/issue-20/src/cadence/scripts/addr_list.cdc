import ExampleMarketplace from 0xb10db40892311e63

pub fun main(addr: Address): {UInt64: UFix64}{
    let saleIds = ExampleMarketplace.list()[addr] ?? panic("this address no list for sale")
    let id_price: {UInt64: UFix64} = {}
    let acct = getAccount(addr)
    let saleRef = acct.getCapability<&{ExampleMarketplace.SalePublic}>(ExampleMarketplace.SalePublicPath)
                    .borrow() ?? panic("no SaleCollection resource")
    for id in saleIds {
        id_price[id] = saleRef.idPrice(tokenID:id)!
    }
    return id_price
}