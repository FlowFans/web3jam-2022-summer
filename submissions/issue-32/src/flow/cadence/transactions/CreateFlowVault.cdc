import FlowToken from 0xf8d6e0586b0a20c7
import FungibleToken from 0xf8d6e0586b0a20c7

transaction() {
  prepare(acct: AuthAccount) {
    acct.save(<-FlowToken.createEmptyVault(), to: FlowToken.FlowTokenVaultStorage)
    acct.link<&FlowToken.Vault{FungibleToken.Provider, FungibleToken.Receiver, FungibleToken.Balance}>(FlowToken.FlowTokenVaultPublic, target: FlowToken.FlowTokenVaultStorage)
  }
}
