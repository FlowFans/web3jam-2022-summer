import { send, decode, script, args, arg, cdc } from "@onflow/fcl"
import * as t from "@onflow/types"

const CODE = cdc`
import NonFungibleToken from 0x631e88ae7f1d7c20
import WakandaPass from 0xdaf76cab293e4369

pub fun main(address: Address): Bool {
  let collection: Bool = getAccount(address)
      .getCapability<&{NonFungibleToken.CollectionPublic}>(WakandaPass.CollectionPublicPath)
      .check()
  return collection
}
`

const scriptIsInit = (address: string | null) => {
  if (address == null) return Promise.resolve(null)

  return send([script(CODE), args([arg(address, t.Address)])]).then(decode)
}

export default scriptIsInit
