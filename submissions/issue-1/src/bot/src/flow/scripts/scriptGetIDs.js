const { send, decode, script, args, arg, cdc }  = require("@onflow/fcl")
const { Address } = require("@onflow/types")

const CODE = cdc`
import NonFungibleToken from 0x631e88ae7f1d7c20
import WakandaPass from 0xdaf76cab293e4369

pub fun main(address: Address): [UInt64] {
    let account = getAccount(address)
    let collectionRef = account.getCapability(WakandaPass.CollectionPublicPath)!.borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Could not borrow capability from public collection")
    return collectionRef.getIDs()
}
`

const scriptGetIDs = (address) => {
  if (address == null) return Promise.resolve(null)

  return send([script(CODE), args([arg(address, Address)])]).then(decode)
}

module.exports = {
  scriptGetIDs
}
