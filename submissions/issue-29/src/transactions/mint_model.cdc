import NonFungibleToken from 0xNonFungibleToken
import FungibleToken from 0xFungibleToken
import MetadataViews from 0xMetadataViews
import OverluModel from 0xOverluModel

transaction(recipient: Address, name: String, descripttion: String, thumbnail: String, slotNum: UInt64 ) {
    let minter: &OverluModel.NFTMinter
    let receiver: &{NonFungibleToken.CollectionPublic}
    let creator: Capability<&{FungibleToken.Receiver}>

    prepare(signer: AuthAccount) {
        self.minter = signer.borrow<&OverluModel.NFTMinter>(from: OverluModel.MinterStoragePath)
        ?? panic("Signer is not the nft admin")

        self.receiver = getAccount(recipient)
        .getCapability(OverluModel.CollectionPublicPath)!
        .borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Unable to borrow receiver reference")

        self.creator = signer.getCapability<&{FungibleToken.Receiver}>(/public/fusdReceiver)!
    }

    execute {
        let royalty = MetadataViews.Royalty(self.creator, cut: 0.1, description:"Overlu collection royalties")
        let royalties:[MetadataViews.Royalty] = [royalty]
        let metadata: {String: AnyStruct} = {}

        self.minter.mintNFT(
            recipient: self.receiver,
            name: name,
            description: descripttion,
            thumbnail: thumbnail,
            slotNum: slotNum,
            royalties: royalties,
            metadata: metadata
        )
    }
}
