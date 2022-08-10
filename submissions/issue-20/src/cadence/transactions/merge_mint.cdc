import NonFungibleToken from 0x631e88ae7f1d7c20
import ExampleNFT from 0xb10db40892311e63
import FungibleToken from 0x9a0766d93b6608b7
transaction(id1: UInt64, id2: UInt64, amount: UFix64) {
    let collectionRef: &ExampleNFT.Collection
    prepare(acct: AuthAccount) {
        self.collectionRef = acct.borrow<&ExampleNFT.Collection>(from: ExampleNFT.CollectionStoragePath)
                            ?? panic("no resource here")
        let recipient = acct.borrow<&{NonFungibleToken.CollectionPublic}>(from: ExampleNFT.CollectionStoragePath)
                        ?? panic("no resource here")
        let ftRef = acct.borrow<&FungibleToken.Vault>(from: /storage/flowTokenVault)
                    ?? panic("no flow resource")
        let nft1 <- self.collectionRef.withdraw(withdrawID: id1) as! @ExampleNFT.NFT
        let nft2 <- self.collectionRef.withdraw(withdrawID: id2) as! @ExampleNFT.NFT
        let vault <- ftRef.withdraw(amount: amount)
        let nftCollect <- ExampleNFT.create_tree(nft1: <-nft1, nft2: <-nft2, recipient: recipient, mintFee: <-vault)
        self.collectionRef.deposit(token: <-nftCollect.removeFirst())
        self.collectionRef.deposit(token: <-nftCollect.removeFirst())
        destroy(<-nftCollect)
    }
}