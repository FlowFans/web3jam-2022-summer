import FungibleToken from 0xee82856bf20e2aa6
import FUSD from 0xf8d6e0586b0a20c7

pub fun main(address: Address): UFix64 {
    let account = getAccount(address)

    let vaultRef = account.getCapability(/public/fusdBalance)!
        .borrow<&FUSD.Vault{FungibleToken.Balance}>()
        ?? panic("Could not borrow Balance reference to the Vault")

    return vaultRef.balance
}