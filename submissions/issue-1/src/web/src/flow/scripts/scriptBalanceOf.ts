import { send, decode, script, args, arg, cdc } from "@onflow/fcl"
import { Address } from "@onflow/types"

const CODE = cdc`
import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

pub fun main(address: Address): Int {
    let account = getAccount(address)
    let collectionRef = account.getCapability(WakandaPass.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic}>()
        ?? panic("Could not borrow capability from public collection")
    return collectionRef.getIDs().length
}
`

const scriptBalanceOf = (address: string | null) => {
  if (address == null) return Promise.resolve(null)

  return send([script(CODE), args([arg(address, Address)])]).then(decode)
}

export default scriptBalanceOf