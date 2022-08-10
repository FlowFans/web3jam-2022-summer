
// This transaction is a template for a transaction
// to add a Vault resource to their account
// so that they can use the CarlyToken

import FungibleToken from "./../contracts/FungibleToken.cdc"
import CarlyToken from "./../contracts/CarlyToken.cdc"

transaction {

    prepare(signer: AuthAccount) {

        // Return early if the account already stores a CarlyToken Vault
        if signer.borrow<&CarlyToken.Vault>(from: CarlyToken.VaultStoragePath) != nil {
            return
        }

        // Create a new CarlyToken Vault and put it in storage
        signer.save(
            <-CarlyToken.createEmptyVault(),
            to: CarlyToken.VaultStoragePath
        )

        // Create a public capability to the Vault that only exposes
        // the deposit function through the Receiver interface
        signer.link<&CarlyToken.Vault{FungibleToken.Receiver}>(
            CarlyToken.ReceiverPublicPath,
            target: CarlyToken.VaultStoragePath
        )

        // Create a public capability to the Vault that only exposes
        // the balance field through the Balance interface
        signer.link<&CarlyToken.Vault{FungibleToken.Balance}>(
            CarlyToken.BalancePublicPath,
            target: CarlyToken.VaultStoragePath
        )
    }
}
