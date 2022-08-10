import ExampleMarketplace from 0xb10db40892311e63
import ExampleNFT from 0xb10db40892311e63

transaction(tokenID: UInt64, price: UFix64) {
    let marketRef: &ExampleMarketplace.SaleCollection
    let collectionRef: &ExampleNFT.Collection
    let nftRef: &ExampleNFT.NFT
    let life: UFix64
    prepare(acct: AuthAccount) {
        self.marketRef = acct.borrow<&ExampleMarketplace.SaleCollection>(from: ExampleMarketplace.SaleStoragePath)
                        ?? panic("no SaleCollection resource")
        self.collectionRef = acct.borrow<&ExampleNFT.Collection>(from: ExampleNFT.CollectionStoragePath)
                        ?? panic("no ExampleNFT collection resource")
        self.nftRef = self.collectionRef.borrowExampleNFT(id: tokenID) ?? panic("no nft")
        let level = self.nftRef.resolveView(Type<ExampleNFT.Level>())
        //todo
        self.life = 9999999999.0
    }
    execute {
        self.marketRef.listForSale(tokenID: tokenID, price: price)
    }
    post {
        self.life + 7776000.0 * self.nftRef.agingRate >= getCurrentBlock().timestamp : "this nft is too old"
    }
}