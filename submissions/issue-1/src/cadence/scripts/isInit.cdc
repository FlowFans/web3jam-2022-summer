import NonFungibleToken from 0xdaf76cab293e4369
import WakandaPass from 0xdaf76cab293e4369

pub fun main(address: Address): Bool {
  let collection: Bool = getAccount(address)
      .getCapability<&{NonFungibleToken.CollectionPublic}>(WakandaPass.CollectionPublicPath)
      .check()
  return collection
}
