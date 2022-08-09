import { send, decode, script, args, arg, cdc } from "@onflow/fcl"
import * as t from "@onflow/types"

const CODE = cdc`
import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

pub fun main(address: Address, id: UInt64): String? {
  if let collection = getAccount(address).getCapability<&WakandaPass.Collection{NonFungibleToken.CollectionPublic, WakandaPass.WakandaPassCollectionPublic}>(WakandaPass.CollectionPublicPath).borrow() {
    if let item = collection.borrowWakandaPass(id: id) {
      return item.metadata
    }
  }
  return nil
}

`

const scriptTokenURI = (address: string | null | undefined, id: Number) => {
  if (address == null) return Promise.resolve(null)

  return send([script(CODE), args([arg(address, t.Address), arg(id, t.UInt64)])]).then(decode)
}

export default scriptTokenURI