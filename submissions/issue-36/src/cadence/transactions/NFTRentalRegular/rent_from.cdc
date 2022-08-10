import FungibleToken from "../../contracts/FungibleToken.cdc"
import FlowToken from "../../contracts/FlowToken.cdc"
import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

transaction(tokenId: UInt64, feeAmount: UFix64) {
  let signer: AuthAccount
  let vaultRef: &FungibleToken.Vault?

  prepare(signer: AuthAccount) {
    self.signer = signer
    self.vaultRef = signer.borrow<&FungibleToken.Vault>(from: /storage/flowTokenVault) 
      ?? panic("Account has no Flow vault")
  }

  execute {
    let vault <- self.vaultRef!.withdraw(amount: feeAmount) as! @FlowToken.Vault
    NFTRentalRegular.rentFrom(tokenId: tokenId, tenant: self.signer.address, feePayment: <- vault)
  }
}