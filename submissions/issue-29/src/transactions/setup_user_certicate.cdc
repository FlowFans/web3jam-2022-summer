import OverluConfig from OverluConfig

transaction() {
  prepare(signer: AuthAccount) {
    // Get protocol-issued user certificate
    if signer.borrow<&{OverluConfig.IdentityCertificate}>(from: OverluConfig.UserCertificateStoragePath) == nil {
      destroy <- signer.load<@AnyResource>(from: OverluConfig.UserCertificateStoragePath)

      let userCertificate <- OverluConfig.setupUser()
      signer.save(<- userCertificate, to: OverluConfig.UserCertificateStoragePath)
      signer.link<&{OverluConfig.IdentityCertificate}>(OverluConfig.UserCertificatePrivatePath, target: OverluConfig.UserCertificateStoragePath)
    }
  }
  execute {
    
  }
}
