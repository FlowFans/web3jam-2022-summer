import NonFungibleToken from 0x631e88ae7f1d7c20
import Univoice from 0x231727804b3acfe3
import MetadataViews from 0x631e88ae7f1d7c20
import FungibleToken from 0x9a0766d93b6608b7

/// This script uses the NFTMinter resource to mint a new NFT
/// It must be run with the account that has the minter resource
/// stored in /storage/NFTMinter
transaction(
    recipient: Address,
    name: String,
    description: String,
    thumbnail: String,
    voiceName : String,
    voiceModel : String
) {

    /// local variable for storing the minter reference
    let minter: &Univoice.NFTMinter

    /// Reference to the receiver's collection
    let recipientCollectionRef: &{NonFungibleToken.CollectionPublic}

    /// Previous NFT ID before the transaction executes
    let mintingIDBefore: UInt64


    prepare(signer: AuthAccount) {
        self.mintingIDBefore = Univoice.totalSupply

        // borrow a reference to the NFTMinter resource in storage
        self.minter = signer.borrow<&Univoice.NFTMinter>(from: Univoice.MinterStoragePath)
            ?? panic("Account does not store an object at the specified path")

        // Borrow the recipient's public NFT collection reference
        self.recipientCollectionRef = getAccount(recipient)
            .getCapability(Univoice.CollectionPublicPath)
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not get receiver reference to the NFT Collection")
    }


    execute {


        // Mint the NFT and deposit it to the recipient's collection
        self.minter.mintNFT(
            recipient: self.recipientCollectionRef,
            name: name,
            description: description,
            thumbnail: thumbnail,
            _voiceName : voiceName,
            _voiceModel : voiceModel
        )
    }

    post {
        self.recipientCollectionRef.getIDs().contains(self.mintingIDBefore): "The next NFT ID should have been minted and delivered"
        Univoice.totalSupply == self.mintingIDBefore + 1: "The total supply should have been increased by 1"
    }
}