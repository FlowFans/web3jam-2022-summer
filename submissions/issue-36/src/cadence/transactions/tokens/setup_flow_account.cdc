
// This transaction is a template for a transaction
// to add a Vault resource to their account
// so that they can use the exampleToken

import FungibleToken from "../../contracts/FungibleToken.cdc"
import FlowToken from "../../contracts/FlowToken.cdc"

transaction {

    prepare(signer: AuthAccount) {

        // Return early if the account already stores a ExampleToken Vault
        if signer.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault1) != nil {
            return
        }

        // Create a new ExampleToken Vault and put it in storage
        signer.save(
            <-FlowToken.createEmptyVault(),
            to: /storage/flowTokenVault1
        )

        // Create a public capability to the Vault that only exposes
        // the deposit function through the Receiver interface
        signer.link<&FlowToken.Vault{FungibleToken.Receiver}>(
            /public/flowTokenReceiver,
            target: /storage/flowTokenVault1
        )

        // Create a public capability to the Vault that only exposes
        // the balance field through the Balance interface
        signer.link<&FlowToken.Vault{FungibleToken.Balance}>(
            /public/flowTokenBalance,
            target: /storage/flowTokenVault1
        )
    }
}
