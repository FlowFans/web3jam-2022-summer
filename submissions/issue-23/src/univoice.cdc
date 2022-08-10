import Univoice from 0x231727804b3acfe3
import MetadataViews from 0x631e88ae7f1d7c20

pub fun main(address: Address, id: UInt64): Univoice.Voice{

  let account = getAccount(address)

  let collection = account.getCapability(Univoice.CollectionPublicPath).borrow<&{Univoice.UnivoiceCollectionPublic}>()?? panic("Could not borrow a reference to the collection")

  let nft = collection.borrowUnivoice(id: id)!

  return nft.voice
}

