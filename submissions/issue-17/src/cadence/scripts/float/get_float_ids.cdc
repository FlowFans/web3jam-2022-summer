import FLOAT from "../../contracts/float/FLOAT.cdc"

pub fun main(account: Address): [UInt64] {
  let floatCollection = getAccount(account).getCapability(FLOAT.FLOATCollectionPublicPath)
                        .borrow<&FLOAT.Collection{FLOAT.CollectionPublic}>()
                        ?? panic("Could not borrow the Collection from the account.")
  return floatCollection.getIDs()
}
