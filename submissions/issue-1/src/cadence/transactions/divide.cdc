import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

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
