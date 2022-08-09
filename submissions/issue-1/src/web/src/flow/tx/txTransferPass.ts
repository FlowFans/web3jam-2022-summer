import { transaction, limit, proposer, payer, authorizations, authz, cdc, args, arg } from "@onflow/fcl"
import * as t from "@onflow/types"
import { tx } from "../utils/tx"
import { invariant } from "@onflow/util-invariant"

const CODE = cdc`
import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

transaction(recipient: Address, withdrawID: UInt64) {
    prepare(signer: AuthAccount) {
        let recipient = getAccount(recipient)
        let collectionRef = signer.borrow<&WakandaPass.Collection>(from: WakandaPass.CollectionStoragePath)
            ?? panic("Could not borrow a reference to the owner's collection")
        let depositRef = recipient.getCapability(WakandaPass.CollectionPublicPath)!.borrow<&{NonFungibleToken.CollectionPublic}>()!
        let nft <- collectionRef.withdraw(withdrawID: withdrawID)
        depositRef.deposit(token: <-nft)
    }
}
`

const txTransferPass = (recipient: string, withdrawID: Number, opts = {}) => {
  invariant(recipient != null, "transfer({recipient, withdrawID}) -- amount required")
  invariant(withdrawID != null, "transfer({recipient, withdrawID}) -- to required")

  return tx(
    [
      transaction(CODE),
      args([arg(recipient, t.Address), arg(withdrawID, t.UInt64)]),
      proposer(authz),
      payer(authz),
      authorizations([authz]),
      limit(1000),
    ],
    opts
  )
}

export default txTransferPass