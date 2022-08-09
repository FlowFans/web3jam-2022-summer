
import OverluDNA from 0xOverluDNA
// import MetadataViews from 0xMetadataViews

pub fun main(address: Address, id: UInt64): UFix64 {
  let account = getAccount(address) 

  let collection = account
      .getCapability(OverluDNA.CollectionPublicPath)
      .borrow<&{OverluDNA.CollectionPublic}>()
      ?? panic("Could not borrow a reference to the collection")
  
  let nft = collection.borrowOverluDNA(id: id)!

  return nft.calculateEnergy()
}
