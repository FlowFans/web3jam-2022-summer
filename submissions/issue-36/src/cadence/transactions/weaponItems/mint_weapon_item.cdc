import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import WeaponItems1 from "../../contracts/WeaponItems1.cdc"

// This transction uses the NFTMinter resource to mint a new NFT.
//
// It must be run with the account that has the minter resource
// stored at path /storage/NFTMinter.

transaction(recipient: Address, name: String, attack: UInt8, defence: UInt8, url: String) {

    // local variable for storing the minter reference
    // let minter: &WeaponItems1.NFTMinter
    let minter: @WeaponItems1.NFTMinter

    prepare(signer: AuthAccount) {

        // FREE MINT FOR TEST
        self.minter <- WeaponItems1.getMinter()
    }

    execute {
        // get the public account object for the recipient
        let recipient = getAccount(recipient)

        // borrow the recipient's public NFT collection reference
        let receiver = recipient
            .getCapability(WeaponItems1.CollectionPublicPath)!
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not get receiver reference to the NFT Collection")


        // mint the NFT and deposit it to the recipient's collection
        self.minter.mintNFT(
            recipient: receiver,
            name: name,
            attack: attack,
            defence: defence,
            url: url
        )

        destroy self.minter
    }
}
