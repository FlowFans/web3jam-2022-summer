import Melody from 0xMelody

transaction(id: UInt64) {
  var userCertificateCap: Capability<&{Melody.IdentityCertificate}>

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
   
  }
  execute {
    Melody.changeTransferable(userCertificateCap: self.userCertificateCap, paymentId: id)
  }
}
