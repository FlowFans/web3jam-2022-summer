
import OverluDNA from 0xOverluDNA

pub fun main(address: Address, id: UInt64): {String: AnyStruct}? {
  
  let collection = getAccount(address).getCapability(OverluDNA.CollectionPublicPath)
      .borrow<&{OverluDNA.CollectionPublic}>()
      ?? panic("Could not borrow a reference to the collection")

  let nft = collection.borrowOverluDNA(id: id)!
  return OverluDNA.getMetadata(nft.typeId)
}
