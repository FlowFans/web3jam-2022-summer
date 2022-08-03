import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

// This transaction uses the Collection resource to mint a new NFT.
//
// It must be run with the account that has the minter resource
// stored at path WakandaPass.CollectionStoragePath

transaction() {

    // local variable for storing the minter reference
    let minter: &WakandaPass.Collection

    prepare(signer: AuthAccount) {

        // borrow a reference to the NFTMinter resource in storage
        self.minter = signer.borrow<&WakandaPass.Collection>(from: WakandaPass.CollectionStoragePath)
            ?? panic("Could not borrow a reference to the NFT minter")
    }

    execute {
        // mint the NFT and deposit it to minter's collection
        self.minter.initWakandaPass()
    }
}
