import Melody from 0xMelody
import MelodyTicket from 0xMelodyTicket
import MetadataViews from 0xMetadataViews
import NonFungibleToken from 0xNonFungibleToken

transaction() {
  var userCertificateCap: Capability<&{Melody.IdentityCertificate}>
  var receiverCollection: &{NonFungibleToken.CollectionPublic}
  var ids: [UInt64]
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

    if signer.borrow<&MelodyTicket.Collection>(from: MelodyTicket.CollectionStoragePath) == nil {

      signer.save(<- MelodyTicket.createEmptyCollection(), to: MelodyTicket.CollectionStoragePath)

      // create a public capability for the collection
      signer.link<&MelodyTicket.Collection{NonFungibleToken.CollectionPublic, MelodyTicket.CollectionPublic, MetadataViews.ResolverCollection}>(
        MelodyTicket.CollectionPublicPath,
        target: MelodyTicket.CollectionStoragePath
      )
      signer.link<&MelodyTicket.Collection{MelodyTicket.CollectionPrivate}>(
        MelodyTicket.CollectionPrivatePath,
        target: MelodyTicket.CollectionStoragePath
      )
    } 

    let receiver = self.userCertificateCap.borrow()!.owner!.address
    let receiverCollectionCap = getAccount(receiver).getCapability<&{NonFungibleToken.CollectionPublic}>(MelodyTicket.CollectionPublicPath)
    self.receiverCollection = receiverCollectionCap.borrow()?? panic("Canot borrow receiver's collection")

    self.ids = Melody.getUserTicketRecords(receiver)

  }
  execute {
    for id in self.ids {
      self.receiverCollection.deposit(token: <- Melody.claimTicket(userCertificateCap: self.userCertificateCap, paymentId: id))
    }
  }
}
