import NonFungibleToken from 0xdaf76cab293e4369
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