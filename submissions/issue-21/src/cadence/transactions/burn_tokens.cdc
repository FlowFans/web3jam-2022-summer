// This transaction is a template for a transaction that
// could be used by the admin account to burn tokens
// from their stored Vault
//
// The burning amount would be a parameter to the transaction

import FungibleToken from "../contracts/FungibleToken.cdc"
import CarlyToken from "../contracts/CarlyToken.cdc"

transaction(amount: UFix64) {

    /// Vault resource that holds the tokens that are being burned
    let vault: @FungibleToken.Vault

    /// Reference to the CarlyToken Admin object
    let admin: &CarlyToken.Administrator

    /// The total supply of tokens before the burn
    let supplyBefore: UFix64

    prepare(signer: AuthAccount) {

        self.supplyBefore = CarlyToken.totalSupply

        // Withdraw 10 tokens from the admin vault in storage
        self.vault <- signer.borrow<&CarlyToken.Vault>(from: CarlyToken.VaultStoragePath)!
            .withdraw(amount: amount)

        // Create a reference to the admin admin resource in storage
        self.admin = signer.borrow<&CarlyToken.Administrator>(from: CarlyToken.AdminStoragePath)
            ?? panic("Could not borrow a reference to the admin resource")
    }

    execute {
        let burner <- self.admin.createNewBurner()

        burner.burnTokens(from: <-self.vault)

        destroy burner
    }

    post {
        CarlyToken.totalSupply == self.supplyBefore - amount: "The total supply must be decreased by the amount"
    }
}
