const { send, decode, script, args, arg, cdc }  = require("@onflow/fcl")
const { Address, UInt64 } = require("@onflow/types")

const CODE = cdc`
import NonFungibleToken from 0x631e88ae7f1d7c20
import WakandaPass from 0xdaf76cab293e4369

pub fun main(address: Address, id: UInt64): String? {
  if let collection = getAccount(address).getCapability<&WakandaPass.Collection{NonFungibleToken.CollectionPublic, WakandaPass.WakandaPassCollectionPublic}>(WakandaPass.CollectionPublicPath).borrow() {
    if let item = collection.borrowWakandaPass(id: id) {
      return item.metadata
    }
  }
  return nil
}

`

const scriptTokenURI = (address, id) => {
  if (address == null) return Promise.resolve(null)

  return send([script(CODE), args([arg(address, Address), arg(id, UInt64)])]).then(decode)
}

module.exports = {
  scriptTokenURI
}
