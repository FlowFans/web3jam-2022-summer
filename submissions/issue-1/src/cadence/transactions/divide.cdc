import NonFungibleToken from 0xdaf76cab293e4369
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
