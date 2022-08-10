
// This transaction is a template for a transaction
// to add a Vault resource to their account
// so that they can use the exampleToken

import FungibleToken from "../../contracts/FungibleToken.cdc"
import ContributionPoint from "../../contracts/ContributionPoint.cdc"

transaction {

    prepare(signer: AuthAccount) {

        // Return early if the account already stores a ExampleToken Vault
        if signer.borrow<&ContributionPoint.Vault>(from: ContributionPoint.VaultStoragePath) != nil {
            return
        }

        // Create a new ExampleToken Vault and put it in storage
        signer.save(
            <-ContributionPoint.createEmptyVault(),
            to: ContributionPoint.VaultStoragePath
        )

        // Create a public capability to the Vault that only exposes
        // the deposit function through the Receiver interface
        signer.link<&ContributionPoint.Vault{FungibleToken.Receiver}>(
            ContributionPoint.ReceiverPublicPath,
            target: ContributionPoint.VaultStoragePath
        )

        // Create a public capability to the Vault that only exposes
        // the balance field through the Balance interface
        signer.link<&ContributionPoint.Vault{FungibleToken.Balance}>(
            ContributionPoint.BalancePublicPath,
            target: ContributionPoint.VaultStoragePath
        )
    }
}
