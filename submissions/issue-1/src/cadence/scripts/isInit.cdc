import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

pub fun main(address: Address): Bool {
  let collection: Bool = getAccount(address)
      .getCapability<&{NonFungibleToken.CollectionPublic}>(WakandaPass.CollectionPublicPath)
      .check()
  return collection
}
