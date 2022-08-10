import FungibleToken from 0xFungibleToken
import FlowToken from 0xFlowToken
import FUSD from 0xFUSD
import Kibble from 0xKibble

transaction() {
  prepare(signer: AuthAccount) {

    signer.save(<-FUSD.createEmptyVault(), to: /storage/fusdVault)

    signer.link<&{FungibleToken.Receiver}>(
      /public/fusdReceiver,
      target: /storage/fusdVault
    )

    signer.link<&{FungibleToken.Balance}>(
      /public/fusdBalance,
      target: /storage/fusdVault
    )

    signer.save(<-Kibble.createEmptyVault(), to: Kibble.VaultStoragePath)

    signer.link<&{FungibleToken.Receiver}>(
      Kibble.ReceiverPublicPath,
      target: Kibble.VaultStoragePath
    )

    signer.link<&{FungibleToken.Balance}>(
      Kibble.BalancePublicPath,
      target: Kibble.VaultStoragePath
    )

    // signer.save(<-FlowToken.createEmptyVault(), to: /storage/flowTokenVault)

    // signer.link<&FlowToken.Vault{FungibleToken.Receiver}>(
    //   /public/flowTokenReceiver,
    //   target: /storage/flowTokenVault
    // )

    // // Create a public capability to the stored Vault that only exposes
    // // the `balance` field through the `Balance` interface
    
    // signer.link<&FlowToken.Vault{FungibleToken.Balance}>(
    //   /public/flowTokenBalance,
    //   target: /storage/flowTokenVault
    // )
  }
}
