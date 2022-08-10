import NonFungibleToken from 0xdaf76cab293e4369
import WakandaPass from 0xdaf76cab293e4369

pub fun main(address: Address, id: UInt64): String? {
  if let collection = getAccount(address).getCapability<&WakandaPass.Collection{NonFungibleToken.CollectionPublic, WakandaPass.WakandaPassCollectionPublic}>(WakandaPass.CollectionPublicPath).borrow() {
    if let item = collection.borrowWakandaPass(id: id) {
      return item.metadata
    }
  }
  return nil
}
