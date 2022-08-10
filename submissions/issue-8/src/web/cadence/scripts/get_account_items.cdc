import NonFungibleToken from 0xNonFungibleToken
import OnlyBadges from 0xOnlyBadges

pub fun main(address: Address): [UInt64] {
  if let collection = getAccount(address).getCapability<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath).borrow() {
    return collection.getIDs()
  }

  return []
}