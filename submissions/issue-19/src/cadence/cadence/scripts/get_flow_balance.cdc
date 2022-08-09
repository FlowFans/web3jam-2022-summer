import FungibleToken from 0xFungibleToken

pub fun main(address: Address) : UFix64 {
    let account = getAccount(address)
    var balance = 0.00
    if let vault = account.getCapability(/public/flowTokenBalance).borrow<&{FungibleToken.Balance}>() {
      balance = vault.balance
    }
    return balance
}
