import Melody from 0xMelody
import FungibleToken from 0xFungibleToken

transaction(identifier: String, revocable: Bool, transferable: Bool, receiver: Address, startTimestamp: UFix64, cliffDuration: UFix64, cliffAmount: UFix64, steps: Int8, stepDuration: UFix64, stepAmount: UFix64) {
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
    
    let config: {String: AnyStruct} = {}
    config["transferable"] = transferable
    config["startTimestamp"] = startTimestamp
    config["cliffDuration"] = cliffDuration
    config["cliffAmount"] = cliffAmount
    config["steps"] = steps
    config["stepDuration"] = stepDuration
    config["stepAmount"] = stepAmount
    config["vaultIdentifier"] = identifier

    self.config = config
    
    let totalAmount = cliffAmount + UFix64(steps!) * stepAmount!

    let vaultRef = signer.borrow<&FungibleToken.Vault>(from: StoragePath(identifier: identifier)!)!
    self.vault <- vaultRef.withdraw(amount: totalAmount)

  }

  execute {
    Melody.createVesting(userCertificateCap: self.userCertificateCap, vault: <- self.vault, receiver: receiver, revocable: revocable, config: self.config)
  }
}
