import OverluDNA from 0xOverluDNA

transaction(uri: String) {
    let minter: &OverluDNA.NFTMinter

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluDNA.NFTMinter>(from: OverluDNA.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
    }

    execute {
        self.minter.setBaseURI(uri)
    }
}
 