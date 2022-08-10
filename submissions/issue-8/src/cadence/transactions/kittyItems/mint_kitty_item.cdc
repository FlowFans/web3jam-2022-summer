import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import OnlyBadges from "../../contracts/OnlyBadges.cdc"

// This transction uses the NFTMinter resource to mint a new NFT.
//
// It must be run with the account that has the minter resource
// stored at path /storage/NFTMinter.

transaction(recipient: Address, kind: UInt8, rarity: UInt8) {

    // local variable for storing the minter reference
    let minter: &OnlyBadges.NFTMinter

    prepare(signer: AuthAccount) {

        // borrow a reference to the NFTMinter resource in storage
        self.minter = signer.borrow<&OnlyBadges.NFTMinter>(from: OnlyBadges.MinterStoragePath)
            ?? panic("Could not borrow a reference to the NFT minter")
    }

    execute {
        // get the public account object for the recipient
        let recipient = getAccount(recipient)

        // borrow the recipient's public NFT collection reference
        let receiver = recipient
            .getCapability(OnlyBadges.CollectionPublicPath)!
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not get receiver reference to the NFT Collection")

        // let kindValue = OnlyBadges.Kind(rawValue: kind) ?? panic("invalid kind")
        // let rarityValue = OnlyBadges.Rarity(rawValue: rarity) ?? panic("invalid rarity")

        // // mint the NFT and deposit it to the recipient's collection
        // self.minter.mintNFT(
        //     recipient: receiver,
        //     kind: kindValue,
        //     rarity: rarityValue,
        // )
    }
}
