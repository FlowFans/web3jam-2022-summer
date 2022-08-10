import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaPass from "../../contracts/CaaPass.cdc"

transaction(index: UInt64, title: String, description: String, mediaType: String, mediaHash: String) {

    prepare(signer: AuthAccount) {
        let admin = signer
            .borrow<&CaaPass.Admin>(from: CaaPass.AdminStoragePath)
            ?? panic("Signer is not the admin")

        let metadata: CaaPass.Metadata = CaaPass.Metadata(
            name: title,
            description: description,
            mediaType: mediaType,
            mediaHash: mediaHash
        )

        admin.registerMetadata(index: index, metadata: metadata)
    }
}
