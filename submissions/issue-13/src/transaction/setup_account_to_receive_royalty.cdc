
/// This transaction is a template for a transaction
/// to create a new link in their account to be used for receiving royalties
/// This transaction can be used for any fungible token, which is specified by the `vaultPath` argument
///
/// If the account wants to receive royalties in FLOW, they'll use `/storage/flowTokenVault`
/// If they want to receive it in USDC, they would use FiatToken.VaultStoragePath
/// and so on.
/// The path used for the public link is a new path that in the future, is expected to receive
/// and generic token, which could be forwarded to the appropriate vault

import FungibleToken from 0xFungibleToken
import MetadataViews from 0xNonFungibleToken

transaction {

    prepare(signer: AuthAccount) {

        if signer.borrow<&FungibleToken.Vault>(from: /storage/fusdVault) == nil {
            panic("A vault for the specified fungible token path does not exist")
        }

         signer.link<&{FungibleToken.Receiver, FungibleToken.Balance}>(
            MetadataViews.getRoyaltyReceiverPublicPath(),
            target: /storage/fusdVault
        )
    }
}