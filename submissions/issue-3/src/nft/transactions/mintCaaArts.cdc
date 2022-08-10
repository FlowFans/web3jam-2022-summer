import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaArts from "../../contracts/CaaArts.cdc"

transaction(address: Address) {

    prepare(signer: AuthAccount) {
        let minter = signer
            .borrow<&CaaArts.NFTMinter>(from: CaaArts.MinterStoragePath)
            ?? panic("Signer is not the admin")

        let nftCollectionRef = getAccount(address).getCapability(CaaArts.CollectionPublicPath)
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not borrow CAA Arts collection public reference")

        let metadata: CaaArts.Metadata = CaaArts.Metadata(
            name: "Test NFT",
            description: "Description of Test NFT",
            mediaType: "image/png",
            mediaHash: "hash"
        )

        minter.mintNFT(recipient: nftCollectionRef, metadata: metadata)
    }
}
