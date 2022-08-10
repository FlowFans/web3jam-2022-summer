
import OverluDNA from 0xOverluDNA
// import MetadataViews from 0xMetadataViews

pub fun main(address: Address): [{String: AnyStruct}]? {
  let account = getAccount(address) 

  let collection = account
      .getCapability(OverluDNA.CollectionPublicPath)
      .borrow<&{OverluDNA.CollectionPublic}>()
      ?? panic("Could not borrow a reference to the collection")
  let nfts:[{String: AnyStruct}] = []
  let ids = collection.getIDs()
  for id in ids {
    let nft = collection.borrowOverluDNA(id: id)!
    nfts.append(nft.getMetadata())
  }

  return nfts
}
