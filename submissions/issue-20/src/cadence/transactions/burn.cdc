import NonFungibleToken from 0x631e88ae7f1d7c20
import ExampleNFT from 0xb10db40892311e63

transaction(id: UInt64) {
    let collectionRef: &ExampleNFT.Collection
    prepare(acct: AuthAccount) {
        self.collectionRef = acct.borrow<&ExampleNFT.Collection>(from: ExampleNFT.CollectionStoragePath)
                            ?? panic("no resource here")
        let nft <- self.collectionRef.withdraw(withdrawID: id) as! @ExampleNFT.NFT
        ExampleNFT.burn(nft: <-nft)
    }
}