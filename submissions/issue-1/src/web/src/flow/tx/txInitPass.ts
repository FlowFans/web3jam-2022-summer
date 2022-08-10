import { transaction, limit, proposer, payer, authorizations, authz, cdc } from "@onflow/fcl"
import { invariant } from "@onflow/util-invariant"
import { tx } from "../utils/tx"

const CODE = cdc`
import NonFungibleToken from 0x631e88ae7f1d7c20
import WakandaPass from 0xdaf76cab293e4369

transaction() {
    let minter: &WakandaPass.Collection
    prepare(signer: AuthAccount) {
        self.minter = signer.borrow<&WakandaPass.Collection>(from: WakandaPass.CollectionStoragePath)
            ?? panic("Could not borrow a reference to the NFT minter")
    }
    execute {
        self.minter.initWakandaPass()
    }
}
`

const txInitPass = (address: string | null, opts = {}) => {
  invariant(address != null, "Tried to initialize an account but no address was supplied")

  return tx([transaction(CODE), limit(1000), proposer(authz), payer(authz), authorizations([authz])], opts)
}

export default txInitPass
