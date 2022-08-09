import NonFungibleToken from 0xNonFungibleToken
import OverluConfig from 0xOverluConfig
import OverluPackage from 0xOverluPackage
import OverluDNA from 0xOverluDNA
import OverluModel from 0xOverluModel
import MetadataViews from 0xMetadataViews

transaction() {

    prepare(signer: AuthAccount) {
       
        if signer.borrow<&{OverluConfig.IdentityCertificate}>(from: OverluConfig.UserCertificateStoragePath) == nil {
            let userCertificate <- OverluConfig.setupUser()
            signer.save(<- userCertificate, to: OverluConfig.UserCertificateStoragePath)
        }

        if signer.borrow<&{OverluPackage.CollectionPublic}>(from: OverluPackage.CollectionStoragePath) == nil {
            signer.save(<- OverluPackage.createEmptyCollection(), to: OverluPackage.CollectionStoragePath)
            signer.link<&OverluPackage.Collection{OverluPackage.CollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Receiver, MetadataViews.ResolverCollection}>(OverluPackage.CollectionPublicPath, target: OverluPackage.CollectionStoragePath)
        } 

        if signer.borrow<&{OverluDNA.CollectionPublic}>(from: OverluDNA.CollectionStoragePath) == nil {
            signer.save(<- OverluDNA.createEmptyCollection(), to: OverluDNA.CollectionStoragePath)
            signer.link<&OverluDNA.Collection{OverluDNA.CollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Receiver, MetadataViews.ResolverCollection }>(OverluDNA.CollectionPublicPath, target: OverluDNA.CollectionStoragePath)
        } 

        if signer.borrow<&{OverluModel.CollectionPublic}>(from: OverluModel.CollectionStoragePath) == nil {
            signer.save(<- OverluModel.createEmptyCollection(), to: OverluModel.CollectionStoragePath)
            signer.link<&OverluModel.Collection{OverluModel.CollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Receiver, MetadataViews.ResolverCollection }>(OverluModel.CollectionPublicPath, target: OverluModel.CollectionStoragePath)
        } 

    }

    execute {
    
    }
}
 