import ExampleNFT from 0xb10db40892311e63

transaction(id: UInt64,addr: Address) {

    execute {
        let acct = getAccount(addr)
        let collectionRef = acct.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath)
                        .borrow()
                        ?? panic("no resource")
        let nftRef = collectionRef.borrowExampleNFT(id: id)
        ExampleNFT.claim_sharing(nft: nftRef!)
    }
}