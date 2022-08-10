
import ExampleRentMarketplace from 0xb096b656ab049551

pub fun main(account: Address): {UInt64: UFix64}{
    let acct = getAccount(account)
    let saleRef = acct.getCapability<&{ExampleRentMarketplace.SalePublic}>(
        /public/ExampleRentMarketplace
    ).borrow() ?? panic("no capability")
    let ids = saleRef.getIDs()
    let ans: {UInt64: UFix64} = {}
    for id in ids {
        ans[id] = saleRef.idPrice(tokenID: id)!
    }
    return ans
}
