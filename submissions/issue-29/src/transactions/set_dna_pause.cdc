import OverluDNA from 0xOverluDNA

transaction(flag: Bool) {
    let minter: &OverluDNA.NFTMinter

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluDNA.NFTMinter>(from: OverluDNA.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
    }

    execute {
        self.minter.setPause(flag)
    }
}