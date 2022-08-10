import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaArts from "../../contracts/CaaArts.cdc"

transaction(addresses: [Address], names: [String], hashes: [String]) {

    prepare(signer: AuthAccount) {
        assert(
            addresses.length == names.length && addresses.length == hashes.length,
            message: "Input length mismatch"
        )

        var index = 0
        while index < addresses.length {
            let minter = signer
                .borrow<&CaaArts.NFTMinter>(from: CaaArts.MinterStoragePath)
                ?? panic("Signer is not the minter")

            let nftCollectionRef = getAccount(addresses[index]).getCapability(CaaArts.CollectionPublicPath)
                .borrow<&{NonFungibleToken.CollectionPublic}>()
                ?? panic("Could not borrow CAA Arts collection public reference: ".concat(addresses[index].toString()))

            let metadata: CaaArts.Metadata = CaaArts.Metadata(
                name: names[index],
                description: "This badge was issued by THiNG.FUND. It belongs to a frontier artist who embraces Crypto Art.",
                mediaType: "image/png",
                mediaHash: hashes[index]
            )

            minter.mintNFT(recipient: nftCollectionRef, metadata: metadata)

            index = index + 1
        }
    }
}
