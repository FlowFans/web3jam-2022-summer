import NonFungibleToken from 0xNonFungibleToken
import OnlyBadges from 0xOnlyBadges
import MetadataViews from 0xMetadataViews

transaction(name: String, imageFile: String) {

    // local variable for storing the minter reference
    let minter: &OnlyBadges.AdminMinter

    let newMinter: AuthAccount;

    prepare(adminMinter: AuthAccount, newMinter: AuthAccount) {

        // borrow a reference to the NFTMinter resource in storage
        self.minter = adminMinter.borrow<&OnlyBadges.AdminMinter>(from: OnlyBadges.AdminMinterStoragePath)
            ?? panic("Could not borrow a reference to the NFT minter")
        self.newMinter = newMinter;

        if newMinter.borrow<&OnlyBadges.Collection>(from: OnlyBadges.CollectionStoragePath) == nil {

            // create a new empty collection
            let collection <- OnlyBadges.createEmptyCollection()
            
            // save it to the account
            newMinter.save(<-collection, to: OnlyBadges.CollectionStoragePath)

            // create a public capability for the collection
            newMinter.link<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic, MetadataViews.ResolverCollection}>(OnlyBadges.CollectionPublicPath, target: OnlyBadges.CollectionStoragePath)
        }
    }

    execute {
        // add new minter for specific token type
        self.minter.addMinter(minterAccount: self.newMinter, minterName: name, minterImageFile: imageFile)
    }
}