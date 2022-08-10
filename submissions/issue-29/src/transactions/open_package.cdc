import OverluConfig from 0xOverluConfig
import OverluModel from 0xOverluModel
import OverluDNA from 0xOverluDNA
import OverluPackage from 0xOverluPackage
import NonFungibleToken from 0xNonFungibleToken

transaction(id: UInt64) {
  var userCertificateCap: Capability<&{OverluConfig.IdentityCertificate}>
  var nft: @OverluPackage.NFT
  var dnaReciever: &{OverluDNA.CollectionPublic}
  var modelReciever: &{OverluModel.CollectionPublic}

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
   
    let collectionRef = signer.borrow<&NonFungibleToken.Collection>(from: OverluPackage.CollectionStoragePath )?? panic("can not find collection")
    self.nft <- collectionRef.withdraw(withdrawID: id) as! @OverluPackage.NFT

    self.dnaReciever = signer.borrow<&{OverluDNA.CollectionPublic}>(from: OverluDNA.CollectionStoragePath)?? panic("can not find dna collection")
    self.modelReciever = signer.borrow<&{OverluModel.CollectionPublic}>(from: OverluModel.CollectionStoragePath)?? panic("can not find model collection")
  }
  execute {
    let nfts <- OverluPackage.openPackage(userCertificateCap: self.userCertificateCap, nft: <- self.nft)
    // if nfts.length == 0 {
    //   panic("Can not open package")
    // }
    self.modelReciever.deposit(token: <- (nfts.remove(at: 0) as @NonFungibleToken.NFT?)!)
    self.dnaReciever.deposit(token: <- (nfts.remove(at: 0) as @NonFungibleToken.NFT?)!)
    destroy nfts
  }
}
