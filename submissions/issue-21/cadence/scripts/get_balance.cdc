// This script reads the balance field of an account's CarlyToken Balance

import FungibleToken from "../contracts/FungibleToken.cdc"
import CarlyToken from "../contracts/CarlyToken.cdc"

pub fun main(account: Address): UFix64 {
    let acct = getAccount(account)
    let vaultRef = acct.getCapability(/public/CarlyToken)!
        .borrow<&CarlyToken.Vault{FungibleToken.Balance}>()
        ?? panic("Could not borrow Balance reference to the Vault")

    return vaultRef.balance
}
