import FungibleToken from "../../contracts/FungibleToken.cdc"
import NFTRentalRegular from "../../contracts/NFTRentalRegular.cdc"

transaction(tokenId: UInt64) {
  let signer: AuthAccount
  let vaultRef: Capability<&{FungibleToken.Receiver}>

  prepare(signer: AuthAccount) {
    self.signer = signer
    self.vaultRef = signer.getCapability<&{FungibleToken.Receiver}>(/public/gnftTokenReceiver) 
  }

  execute {
    NFTRentalRegular.claim(tokenId: tokenId, claimerVault: self.vaultRef)
  }
}