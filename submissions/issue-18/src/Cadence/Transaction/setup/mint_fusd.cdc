import FungibleToken from 0xee82856bf20e2aa6
import FUSD from "../../contracts/standard/FUSD.cdc"

transaction (to: Address, amount: UFix64) {
  let sentVault: @FungibleToken.Vault

  prepare(signer: AuthAccount) {
    let adminRef = signer.borrow<&FUSD.Administrator>(from: FUSD.AdminStoragePath) ?? panic("Could not borrow reference")
    signer.save(<- adminRef.createNewMinter(), to: /storage/fusdMinter)

    let minterRef = signer.borrow<&FUSD.Minter>(from: /storage/fusdMinter) ?? panic("Cannot borrow reference")
    self.sentVault <- minterRef.mintTokens(amount: amount)
  }

  execute {
    let recipient = getAccount(to)
    let receiverRef = recipient.getCapability(/public/fusdReceiver).borrow<&{FungibleToken.Receiver}>()
            ?? panic("Could not borrow receiver reference to the recipient's Vault")

    // Deposit the withdrawn tokens in the recipient's receiver
    receiverRef.deposit(from: <-self.sentVault)
  }
}