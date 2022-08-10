import OnlyBadges from 0xOnlyBadges
import MetadataViews from 0xMetadataViews

transaction(adminMinter: Address, recipient: Address, name: String, description: String, badge_image_cid: String, badge_image_path: String,max: UInt64, claim_code: String?, royalty_cut: UFix64?, royalty_description: String?, royalty_receiver: Address?, externalURL: String?) {

    // local variable for storing the minter reference
    let minter: &OnlyBadges.NFTMinter

    prepare(signer: AuthAccount) {

        // borrow a reference to the NFTMinter resource in storage
        self.minter = signer.borrow<&OnlyBadges.NFTMinter>(from: OnlyBadges.MinterStoragePath)
            ?? panic("mint_badges: Could not borrow a reference to the NFT minter")
    }

    execute {
        // mint the NFT and deposit it to the recipient's collection
        self.minter.mintNFT(adminMinter: adminMinter, recipient: recipient, name: name, description: name, badge_image: MetadataViews.IPFSFile(cid: badge_image_cid, path: badge_image_path), max: max, claim_code: claim_code, royalty_cut: royalty_cut, royalty_description: royalty_description, royalty_receiver: royalty_receiver, externalURL:externalURL)
    }
}