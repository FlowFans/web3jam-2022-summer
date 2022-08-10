import ExampleNFT from 0xb10db40892311e63

transaction(id: UInt64) {

    prepare(acct: AuthAccount) {
        let collectionRef = acct.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath)
                        .borrow()
                        ?? panic("no resource")
        let nftRef = collectionRef.borrowExampleNFT(id: id)
        nftRef!.claim_experience()
        nftRef!.upgrade()
    }

    execute {
        
    }
}