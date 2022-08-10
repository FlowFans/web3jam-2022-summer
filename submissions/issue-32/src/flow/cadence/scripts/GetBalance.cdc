import FlowToken from 0xf8d6e0586b0a20c7
import FungibleToken from 0xf8d6e0586b0a20c7
pub fun main(addr:Address): UFix64{
    let acct = getAccount(addr).getCapability<&FlowToken.Vault{FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic).borrow() ?? panic("VAult not found")
    return acct.balance
}