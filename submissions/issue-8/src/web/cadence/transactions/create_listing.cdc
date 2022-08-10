import FungibleToken from 0xFungibleToken
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import OnlyBadges from 0xOnlyBadges
import NFTStorefront from 0xNFTStorefront

pub fun getOrCreateStorefront(account: AuthAccount): &NFTStorefront.Storefront {
  if let storefrontRef = account.borrow<&NFTStorefront.Storefront>(from: NFTStorefront.StorefrontStoragePath) {
    return storefrontRef
  }

  let storefront <- NFTStorefront.createStorefront()

  let storefrontRef = &storefront as &NFTStorefront.Storefront

  account.save(<-storefront, to: NFTStorefront.StorefrontStoragePath)

  account.link<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath, target: NFTStorefront.StorefrontStoragePath)

  return storefrontRef
}

transaction(saleItemID: UInt64, saleItemPrice: UFix64) {

  let flowReceiver: Capability<&FlowToken.Vault{FungibleToken.Receiver}>
  let OnlyBadgesProvider: Capability<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
  let storefront: &NFTStorefront.Storefront

  prepare(account: AuthAccount) {
    // We need a provider capability, but one is not provided by default so we create one if needed.
    let OnlyBadgesCollectionProviderPrivatePath = /private/OnlyBadgesCollectionProviderV14

    self.flowReceiver = account.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)!

    assert(self.flowReceiver.borrow() != nil, message: "Missing or mis-typed FLOW receiver")

    if !account.getCapability<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(OnlyBadgesCollectionProviderPrivatePath)!.check() {
      account.link<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(OnlyBadgesCollectionProviderPrivatePath, target: OnlyBadges.CollectionStoragePath)
    }

    self.OnlyBadgesProvider = account.getCapability<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(OnlyBadgesCollectionProviderPrivatePath)!

    assert(self.OnlyBadgesProvider.borrow() != nil, message: "Missing or mis-typed OnlyBadges.Collection provider")

    self.storefront = getOrCreateStorefront(account: account)
  }

  execute {
    let saleCut = NFTStorefront.SaleCut(
      receiver: self.flowReceiver,
      amount: saleItemPrice
    )

    self.storefront.createListing(
      nftProviderCapability: self.OnlyBadgesProvider,
      nftType: Type<@OnlyBadges.NFT>(),
      nftID: saleItemID,
      salePaymentVaultType: Type<@FlowToken.Vault>(),
      saleCuts: [saleCut]
    )
  }
}