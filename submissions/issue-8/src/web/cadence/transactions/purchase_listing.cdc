import FungibleToken from 0xFungibleToken
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import OnlyBadges from 0xOnlyBadges
import NFTStorefront from 0xNFTStorefront

pub fun getOrCreateCollection(account: AuthAccount): &OnlyBadges.Collection{NonFungibleToken.Receiver} {
  if let collectionRef = account.borrow<&OnlyBadges.Collection>(from: OnlyBadges.CollectionStoragePath) {
    return collectionRef
  }

  // create a new empty collection
  let collection <- OnlyBadges.createEmptyCollection() as! @OnlyBadges.Collection

  let collectionRef = &collection as &OnlyBadges.Collection
  
  // save it to the account
  account.save(<-collection, to: OnlyBadges.CollectionStoragePath)

  // create a public capability for the collection
  account.link<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath, target: OnlyBadges.CollectionStoragePath)

  return collectionRef
}

transaction(listingResourceID: UInt64, storefrontAddress: Address) {
  let paymentVault: @FungibleToken.Vault
  let OnlyBadgesCollection: &OnlyBadges.Collection{NonFungibleToken.Receiver}
  let storefront: &NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}
  let listing: &NFTStorefront.Listing{NFTStorefront.ListingPublic}

  prepare(account: AuthAccount) {
    self.storefront = getAccount(storefrontAddress)
      .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
          NFTStorefront.StorefrontPublicPath
      )!
      .borrow()
      ?? panic("Could not borrow Storefront from provided address")

    self.listing = self.storefront.borrowListing(listingResourceID: listingResourceID)
      ?? panic("No Listing with that ID in Storefront")
      
    let price = self.listing.getDetails().salePrice

    let mainFLOWVault = account.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)
      ?? panic("Cannot borrow FLOW vault from account storage")
      
    self.paymentVault <- mainFLOWVault.withdraw(amount: price)

    self.OnlyBadgesCollection = getOrCreateCollection(account: account)
  }

  execute {
    let item <- self.listing.purchase(
      payment: <-self.paymentVault
    )

    self.OnlyBadgesCollection.deposit(token: <-item)

    self.storefront.cleanup(listingResourceID: listingResourceID)
  }
}