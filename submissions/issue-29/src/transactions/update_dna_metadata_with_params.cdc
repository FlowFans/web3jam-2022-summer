import NonFungibleToken from 0xNonFungibleToken
import FungibleToken from 0xFungibleToken
import MetadataViews from 0xMetadataViews
import OverluDNA from 0xOverluDNA

transaction(id: UInt64, thumbnail: String, uri: String, max: UInt64, typeStr: String, recipient: Address) {
    let minter: &OverluDNA.NFTMinter

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluDNA.NFTMinter>(from: OverluDNA.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
    }

    execute {
        let metadata:{String: AnyStruct} = {}
        let recepientCap = getAccount(recipient).getCapability<&{FungibleToken.Receiver}>(/public/fusdReceiver)!

        let royalties:[MetadataViews.Royalty] = []
        metadata["name"] = "Overlu DNA"
        metadata["typeId"] = id
        metadata["description"] = "Overlu DNA"
        metadata["mediaType"] = "image/png"
        metadata["mediaHash"] = ""
        metadata["thumbnail"] = thumbnail
        metadata["max"] = max
        metadata["royalties"] = royalties
        metadata["baseURI"] = uri
        metadata["royalties"] = royalties
        metadata["upgradeable"] = typeStr == "U"
        metadata["expandable"] = typeStr == "E"
        metadata["test"] = 20.0

        self.minter.updateMetadata(
            typeId: id,
            metadata: metadata
        )
    }
}
 