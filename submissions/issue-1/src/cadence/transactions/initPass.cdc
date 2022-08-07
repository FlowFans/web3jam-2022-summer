import NonFungibleToken from 0xf5c21ffd3438212b
import WakandaPass from 0xf5c21ffd3438212b

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
