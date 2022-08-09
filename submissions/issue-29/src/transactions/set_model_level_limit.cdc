import OverluModel from 0xOverluModel

transaction(limit: UInt64) {
    let minter: &OverluModel.NFTMinter

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluModel.NFTMinter>(from: OverluModel.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
    }

    execute {
        self.minter.setLevelLimit(limit)
    }
}
 