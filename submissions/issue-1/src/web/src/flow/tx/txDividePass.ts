import { transaction, limit, proposer, payer, authorizations, authz, cdc, args, arg } from "@onflow/fcl"
import * as t from "@onflow/types"
import { tx } from "../utils/tx"
import { invariant } from "@onflow/util-invariant"

const CODE = cdc`
import NonFungibleToken from 0x631e88ae7f1d7c20
import WakandaPass from 0xdaf76cab293e4369

transaction(id: UInt64) {
  let divider: &WakandaPass.Collection
  prepare(signer: AuthAccount) {
    self.divider = signer.borrow<&WakandaPass.Collection>(from: WakandaPass.CollectionStoragePath)
  ?? panic("Could not borrow a reference to the NFT minter")
  }
  execute {
    self.divider.divide(id: id)
  }
}
`

const txDividePass = (id: Number, opts = {}) => {
  invariant(id != null, "divide({id}) -- to required")

  return tx(
    [
      transaction(CODE),
      args([arg(id, t.UInt64)]),
      proposer(authz),
      payer(authz),
      authorizations([authz]),
      limit(1000),
    ],
    opts
  )
}

export default txDividePass
