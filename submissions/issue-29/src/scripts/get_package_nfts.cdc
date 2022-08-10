
import OverluPackage from 0xOverluPackage
// import MetadataViews from 0xMetadataViews

pub fun main(address: Address): [{String: AnyStruct}]? {
  let account = getAccount(address) 

  let collection = account
      .getCapability(OverluPackage.CollectionPublicPath)
      .borrow<&{OverluPackage.CollectionPublic}>()
      ?? panic("Could not borrow a reference to the collection")
  let nfts:[{String: AnyStruct}] = []
  let ids = collection.getIDs()
  for id in ids {
    let nft = collection.borrowOverluPackage(id: id)!
    nfts.append(nft.getMetadata())
  }

  return nfts
}
