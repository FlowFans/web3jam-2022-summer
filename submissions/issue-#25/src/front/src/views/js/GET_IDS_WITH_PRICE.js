export const GET_IDS_WITH_PRICE = `
    import ExampleRentMarketplace from 0xb096b656ab049551

pub fun main(account: Address): {UInt64: {UFix64: UInt64} }{
    let acct = getAccount(account)
    let saleRef = acct.getCapability<&{ExampleRentMarketplace.SalePublic}>(
        /public/ExampleRentMarketplace
    ).borrow() ?? panic("no capability")
    let ids = saleRef.getIDs()
    let ans: {UInt64: {UFix64: UInt64} } = {}
    for id in ids {
        let price = saleRef.idPrice(tokenID: id)!
        let expired = saleRef.getExpired(tokenID: id)!
        let pri2exp: {UFix64: UInt64} = {}
        pri2exp[price] = expired
        ans[id] = pri2exp
    }
    return ans
}
`
