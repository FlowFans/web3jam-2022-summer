import FungibleToken from 0xFungibleToken
import NonFungibleToken from 0xNonFungibleToken
import OnlyBadges from 0xOnlyBadges
import NFTStorefront from 0xNFTStorefront

pub fun hasItems(_ address: Address): Bool {
  return getAccount(address)
    .getCapability<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath)
    .check()
}

pub fun hasStorefront(_ address: Address): Bool {
  return getAccount(address)
    .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath)
    .check()
}

transaction {
  prepare(acct: AuthAccount) {
    if !hasItems(acct.address) {
      if acct.borrow<&OnlyBadges.Collection>(from: OnlyBadges.CollectionStoragePath) == nil {
        acct.save(<-OnlyBadges.createEmptyCollection(), to: OnlyBadges.CollectionStoragePath)
      }
      acct.unlink(OnlyBadges.CollectionPublicPath)
      acct.link<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath, target: OnlyBadges.CollectionStoragePath)
    }

    if !hasStorefront(acct.address) {
      if acct.borrow<&NFTStorefront.Storefront>(from: NFTStorefront.StorefrontStoragePath) == nil {
        acct.save(<-NFTStorefront.createStorefront(), to: NFTStorefront.StorefrontStoragePath)
      }
      acct.unlink(NFTStorefront.StorefrontPublicPath)
      acct.link<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath, target: NFTStorefront.StorefrontStoragePath)
    }
  }
}