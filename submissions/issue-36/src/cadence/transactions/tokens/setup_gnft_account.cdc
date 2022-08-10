
// This transaction is a template for a transaction
// to add a Vault resource to their account
// so that they can use the exampleToken

import FungibleToken from "../../contracts/FungibleToken.cdc"
import GnftToken from "../../contracts/GnftToken.cdc"

transaction {

    prepare(signer: AuthAccount) {

        // Return early if the account already stores a GnftToken Vault
        if signer.borrow<&GnftToken.Vault>(from: GnftToken.VaultStoragePath) != nil {
            return
        }

        // Create a new GnftToken Vault and put it in storage
        signer.save(
            <-GnftToken.createEmptyVault(),
            to: GnftToken.VaultStoragePath
        )

        // Create a public capability to the Vault that only exposes
        // the deposit function through the Receiver interface
        signer.link<&GnftToken.Vault{FungibleToken.Receiver}>(
            GnftToken.ReceiverPublicPath,
            target: GnftToken.VaultStoragePath
        )

        // Create a public capability to the Vault that only exposes
        // the balance field through the Balance interface
        signer.link<&GnftToken.Vault{FungibleToken.Balance}>(
            GnftToken.BalancePublicPath,
            target: GnftToken.VaultStoragePath
        )
    }
}
