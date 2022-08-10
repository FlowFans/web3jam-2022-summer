import { transaction, limit, proposer, payer, authorizations, authz, cdc } from "@onflow/fcl"
import { invariant } from "@onflow/util-invariant"
import { tx } from "../utils/tx"

const CODE = cdc`
import NonFungibleToken from 0x631e88ae7f1d7c20
import WakandaPass from 0xdaf76cab293e4369

transaction {
    prepare(signer: AuthAccount) {
        if signer.borrow<&WakandaPass.Collection>(from: WakandaPass.CollectionStoragePath) == nil {
            let collection <- WakandaPass.createEmptyCollection()
            signer.save(<-collection, to: WakandaPass.CollectionStoragePath)
            signer.link<&WakandaPass.Collection{NonFungibleToken.CollectionPublic,
            WakandaPass.WakandaPassCollectionPublic}>(WakandaPass.CollectionPublicPath,
            target: WakandaPass.CollectionStoragePath)
        }
    }
}
`

const txSetup = (address: string | null, opts = {}) => {
  invariant(address != null, "Tried to initialize an account but no address was supplied")

  return tx([transaction(CODE), limit(70), proposer(authz), payer(authz), authorizations([authz])], opts)
}

export default txSetup
