const { send, decode, script, args, arg, cdc }  = require("@onflow/fcl")
const { Address } = require("@onflow/types")

const CODE = cdc`
import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

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