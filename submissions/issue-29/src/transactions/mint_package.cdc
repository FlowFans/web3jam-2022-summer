import NonFungibleToken from 0xNonFungibleToken
import MetadataViews from 0xMetadataViews
import OverluPackage from 0xOverluPackage

transaction(recipient: Address) {
    let minter: &OverluPackage.NFTMinter
    let receiver: &{NonFungibleToken.CollectionPublic}

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)
        ?? panic("Signer is not the nft admin")

        self.receiver = getAccount(recipient)
        .getCapability(OverluPackage.CollectionPublicPath)!
        .borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Unable to borrow receiver reference")
    }

    execute {
        let royalties:[MetadataViews.Royalty] = []
        self.minter.mintNFT(typeId: UInt64(1),
            recipient: self.receiver,
            name: "",
            description: "",
            thumbnail: "",
            royalties: royalties
        )
    }
}
