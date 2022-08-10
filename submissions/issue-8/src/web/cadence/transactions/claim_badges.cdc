import OnlyBadges from 0xOnlyBadges
import NonFungibleToken from 0xNonFungibleToken
import MetadataViews from 0xMetadataViews

pub fun hasItems(_ address: Address): Bool {
return getAccount(address)
    .getCapability<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath)
    .check()
}

transaction(adminMinter: Address, recipient: Address, claimCode: String) {

    prepare(acct: AuthAccount) {
        if !hasItems(acct.address) {
            if acct.borrow<&OnlyBadges.Collection>(from: OnlyBadges.CollectionStoragePath) == nil {
                acct.save(<-OnlyBadges.createEmptyCollection(), to: OnlyBadges.CollectionStoragePath)
            }
            acct.unlink(OnlyBadges.CollectionPublicPath)
            acct.link<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath, target: OnlyBadges.CollectionStoragePath)
        }
    }

    execute {
        // mint the NFT and deposit it to the recipient's collection
        let adminAccount = getAccount(adminMinter)
         // borrow a reference to the NFTMinter resource in storage
        let adminCapability = adminAccount.getCapability<&{OnlyBadges.ClaimablePublic}>(OnlyBadges.AdminClaimPath)
        let adminClaimable = adminCapability.borrow() ?? panic("Could not get receiver reference to the Admin Claimable")
        adminClaimable.claimNFT(recipient: recipient, claimCode: claimCode)
    }
}