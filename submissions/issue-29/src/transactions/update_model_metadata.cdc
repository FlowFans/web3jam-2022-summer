import NonFungibleToken from 0xNonFungibleToken
import FungibleToken from 0xFungibleToken
import MetadataViews from 0xMetadataViews
import OverluModel from 0xOverluModel

transaction(id: UInt64, recipient: Address, keys:[String], values:[String]) {
    let minter: &OverluModel.NFTMinter

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluModel.NFTMinter>(from: OverluModel.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
    }

    execute {
        let metadata: {String: AnyStruct} = {}
        let recepientCap = getAccount(recipient).getCapability<&{FungibleToken.Receiver}>(/public/fusdReceiver)!
        let royalty = MetadataViews.Royalty(
            recipient: recepientCap,
            amount: 0.1,
            description: "Overlu Royalty"
        )
        let royalties:[MetadataViews.Royalty] = [royalty]
        // metadata["name"] = "Overlu package"
        // metadata["description"] = "Overlu model"
        metadata["mediaType"] = "image/png"
        metadata["royalties"] = royalties

        var idx = 0

        while idx < keys.length {
            metadata[keys[idx]] = values[idx]
           idx = idx + 1 
        }

        self.minter.updateMetadata(
            id: id,
            metadata: metadata
        )
    }
}
 