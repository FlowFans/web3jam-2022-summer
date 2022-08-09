import Melody from 0xMelody
import FungibleToken from 0xFungibleToken

// transaction(startTimestamp: UFix64, endTimestamp: UFix64, identifier: String, amount: UFix64, revocable:Bool, transferable:Bool, receiver: Address) {
transaction(identifier: String, amount: UFix64, revocable: Bool, transferable: Bool, startTimestamp: UFix64, endTimestamp: UFix64, receiver: Address) {
  
  var userCertificateCap: Capability<&{Melody.IdentityCertificate}>
  var config: {String: AnyStruct}
  var vault: @FungibleToken.Vault

  prepare(signer: AuthAccount) {
    if signer.borrow<&{Melody.IdentityCertificate}>(from: Melody.UserCertificateStoragePath) == nil {
      destroy <- signer.load<@AnyResource>(from: Melody.UserCertificateStoragePath)

      let userCertificate <- Melody.setupUser()
      signer.save(<-userCertificate, to: Melody.UserCertificateStoragePath)
      signer.link<&{Melody.IdentityCertificate}>(Melody.UserCertificatePrivatePath, target: Melody.UserCertificateStoragePath)
    }
    if (signer.getCapability<&{Melody.IdentityCertificate}>(Melody.UserCertificatePrivatePath).check()==false) {
      signer.link<&{Melody.IdentityCertificate}>(Melody.UserCertificatePrivatePath, target: Melody.UserCertificateStoragePath)
    }
    self.userCertificateCap = signer.getCapability<&{Melody.IdentityCertificate}>(Melody.UserCertificatePrivatePath)
   
    let vaultRef = signer.borrow<&FungibleToken.Vault>(from: StoragePath(identifier: identifier)!)!
    self.vault <- vaultRef.withdraw(amount: amount)
    
    let config: {String: AnyStruct} = {}
    config["transferable"] = transferable
    config["startTimestamp"] = startTimestamp
    config["endTimestamp"] = endTimestamp
    config["vaultIdentifier"] = identifier

    self.config = config
  }
  execute {
    Melody.createStream(userCertificateCap: self.userCertificateCap, vault: <- self.vault, receiver: receiver, revocable: revocable, config: self.config)
  }
}
