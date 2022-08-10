import NonFungibleToken from 0xNonFungibleToken
import FungibleToken from 0xFungibleToken
import MetadataViews from 0xMetadataViews
import OverluPackage from 0xOverluPackage

transaction(recipient: Address) {
    let minter: &OverluPackage.NFTMinter

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
    }

    execute {
        let metadata:{String: AnyStruct} = {}
        let recepientCap = getAccount(recipient).getCapability<&{FungibleToken.Receiver}>(/public/fusdReceiver)!
        let royalty = MetadataViews.Royalty(
            recipient: recepientCap,
            amount: 0.1,
            description: "Overlu Royalty"
        )
        let royalties:[MetadataViews.Royalty] = [royalty]
        metadata["name"] = "Overlu package"
        metadata["typeId"] = UInt64(1)
        metadata["description"] = "Overlu package"
        metadata["mediaType"] = "image/png"
        metadata["mediaHash"] = ""
        metadata["thumbnail"] = "https://trello.com/1/cards/624713879fd8c23f0395c63d/attachments/6247139af2071076c7d74c93/download/logo192.png"
        metadata["max"] = UInt64(10)
        metadata["royalties"] = royalties
        metadata["baseURI"] = "https://trello.com/1/cards/624713879fd8c23f0395c63d/attachments/6247139af2071076c7d74c93/download/logo192.png"

        self.minter.updateMetadata(
            typeId: UInt64(1),
            metadata: metadata
        )
    }
}
 