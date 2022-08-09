import FungibleToken from 0xFungibleToken
import Melody from 0xMelody
import MelodyTicket from 0xMelodyTicket

transaction(paymentId: UInt64) {
  var userCertificateCap: Capability<&{Melody.IdentityCertificate}>
  var ticketRef: &MelodyTicket.NFT
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
    
    let collectionPriv =  signer.borrow<&{MelodyTicket.CollectionPrivate}>(from: MelodyTicket.CollectionStoragePath)!

    self.ticketRef = collectionPriv.borrowMelodyTicket(id: paymentId)!

    let paymentInfo = Melody.getPaymentInfo(paymentId)
    let identifier = (paymentInfo["vaultIdentifier"] as? String?)!
    self.receiver = signer.borrow<&FungibleToken.Vault>(from: StoragePath(identifier: identifier!)!)!
  }
  execute {
    self.receiver.deposit(from: <- Melody.withdraw(userCertificateCap: self.userCertificateCap, ticket: self.ticketRef))
  }
}
