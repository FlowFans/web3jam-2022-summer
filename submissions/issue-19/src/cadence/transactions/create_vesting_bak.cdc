import Melody from 0xMelody
import FungibleToken from 0xFungibleToken

transaction(identifier: String, revocable: Bool, transferable: Bool, receiver: Address, config: {String: String}) {
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
    // config["transferable"] = transferable
    // config["startTimestamp"] = configArr[0]
    // config["cliffDuration"] = configArr[1]
    // config["cliffAmount"] = configArr[2]
    // config["steps"] = configArr[3]
    // config["stepAmount"] = configArr[4]
    // config["stepDuration"] = configArr[5]

    self.config = config
    
    let cliffAmount = (config["cliffAmount"] as? UFix64) ?? 0.0
    let steps = (config["steps"] as? UFix64?)!
    let stepAmount = (config["stepAmount"] as? UFix64?)!

    let totalAmount = cliffAmount + UFix64(steps!) * stepAmount

    let vaultRef = signer.borrow<&FungibleToken.Vault>(from: StoragePath(identifier: identifier)!)!
    self.vault <- vaultRef.withdraw(amount: totalAmount)

  }

  execute {
    Melody.createVesting(userCertificateCap: self.userCertificateCap, vault: <- self.vault, receiver: receiver, revocable: revocable, config: config)
  }
}