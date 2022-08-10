import NonFungibleToken from 0xNonFungibleToken
import MetadataViews from 0xMetadataViews
import Melody from 0xMelody
import MelodyTicket from 0xMelodyTicket


transaction() {
  prepare(signer: AuthAccount) {
    // Get protocol-issued user certificate
    if signer.borrow<&{Melody.IdentityCertificate}>(from: Melody.UserCertificateStoragePath) == nil {

      let userCertificate <- Melody.setupUser()
      signer.save(<- userCertificate, to: Melody.UserCertificateStoragePath)
      signer.link<&{Melody.IdentityCertificate}>(Melody.UserCertificatePrivatePath, target: Melody.UserCertificateStoragePath)
    }

    if signer.borrow<&MelodyTicket.Collection>(from: MelodyTicket.CollectionStoragePath) == nil {

      signer.save(<-  MelodyTicket.createEmptyCollection(), to: MelodyTicket.CollectionStoragePath)

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
  }

}
