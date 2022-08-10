import ExampleNFT from 0xb10db40892311e63

transaction(id: UInt64, time: Int, addr: Address) {
    let mintRef: &ExampleNFT.NFTMinter
    let collectionRef: &{ExampleNFT.ExampleNFTCollectionPublic}
    prepare(acct: AuthAccount) {
        self.mintRef = acct.borrow<&ExampleNFT.NFTMinter>(from: ExampleNFT.MinterStoragePath)
                        ?? panic("no minter resource")
        let account = getAccount(addr)
        self.collectionRef = account.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath)
                        .borrow()
                        ?? panic("no resource")
    }
    execute {
        
        let nftRef = self.collectionRef.borrowExampleNFT(id: id)!
        let experience = UFix64(time) / (1.0 - nftRef.focusRate)
        self.mintRef.store_experience(id: id, experience: Int(experience))
    }
}