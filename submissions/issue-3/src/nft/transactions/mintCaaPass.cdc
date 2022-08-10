import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaPass from "../../contracts/CaaPass.cdc"

transaction(address: Address, typeID: UInt64) {

    prepare(signer: AuthAccount) {
        let minter = signer
            .borrow<&CaaPass.Admin>(from: CaaPass.AdminStoragePath)
            ?? panic("Signer is not the admin")

        let nftCollectionRef = getAccount(address).getCapability(CaaPass.CollectionPublicPath)
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not borrow CAA Pass collection public reference")

        minter.mintNFT(recipient: nftCollectionRef, typeID: typeID)
    }
}
