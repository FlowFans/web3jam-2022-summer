import OverluConfig from 0xOverluConfig
import OverluPackage from 0xOverluPackage
import NonFungibleToken from 0xNonFungibleToken
import FungibleToken from 0xFungibleToken

transaction(count: UInt8, amount: UFix64) {
  var userCertificateCap: Capability<&{OverluConfig.IdentityCertificate}>
  var vault: @FungibleToken.Vault

  prepare(signer: AuthAccount) {
    if signer.borrow<&{OverluConfig.IdentityCertificate}>(from: OverluConfig.UserCertificateStoragePath) == nil {
      destroy <- signer.load<@AnyResource>(from: OverluConfig.UserCertificateStoragePath)

      let userCertificate <- OverluConfig.setupUser()
      signer.save(<-userCertificate, to: OverluConfig.UserCertificateStoragePath)
      signer.link<&{OverluConfig.IdentityCertificate}>(OverluConfig.UserCertificatePrivatePath, target: OverluConfig.UserCertificateStoragePath)
    }
    if (signer.getCapability<&{OverluConfig.IdentityCertificate}>(OverluConfig.UserCertificatePrivatePath).check()==false) {
      signer.link<&{OverluConfig.IdentityCertificate}>(OverluConfig.UserCertificatePrivatePath, target: OverluConfig.UserCertificateStoragePath)
    }
    self.userCertificateCap = signer.getCapability<&{OverluConfig.IdentityCertificate}>(OverluConfig.UserCertificatePrivatePath)
   
    let vaultRef = signer.borrow<&FungibleToken.Vault>(from: StoragePath(identifier: "flowTokenVault")!)!
    self.vault <- vaultRef.withdraw(amount: amount)
  }
  execute {
    OverluPackage.purchasePackage(userCertificateCap: self.userCertificateCap, amount: count, feeToken: <- self.vault)
  }
}
