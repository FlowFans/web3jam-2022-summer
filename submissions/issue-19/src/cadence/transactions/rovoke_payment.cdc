import Melody from 0xMelody
import FungibleToken from 0xFungibleToken

transaction(id: UInt64) {
  var userCertificateCap: Capability<&{Melody.IdentityCertificate}>
  var receiver: &FungibleToken.Vault
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

    let paymentInfo = Melody.getPaymentInfo(id)
    let identifier = (paymentInfo["vaultIdentifier"] as? String?)!
    self.receiver =  signer.borrow<&FungibleToken.Vault>(from: StoragePath(identifier: identifier!)!)!

  }
  execute {
    self.receiver.deposit(from: <- Melody.revokePayment(userCertificateCap: self.userCertificateCap, paymentId: id))
  }
}
