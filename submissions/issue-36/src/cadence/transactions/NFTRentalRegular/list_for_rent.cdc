import FungibleToken from "../../contracts/FungibleToken.cdc"
import GnftToken from "../../contracts/GnftToken.cdc"
import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

transaction(tokenId: UInt64, endTime: UFix64, rentFee: UFix64, guarantee: UFix64) {
  let signer: AuthAccount
  let vaultRef: &FungibleToken.Vault?

  prepare(signer: AuthAccount) {
    self.signer = signer
    self.vaultRef = signer.borrow<&FungibleToken.Vault>(from: /storage/gnftTokenVault) 
      ?? panic("Account has no GNFT vault")
  }

  execute {
    let vault <- self.vaultRef!.withdraw(amount: guarantee) as! @GnftToken.Vault
    NFTRentalRegular.listForRent(acct: self.signer, tokenId: tokenId, endTime: endTime, rentFee: rentFee, guaranteePayment: <- vault)
  }
}