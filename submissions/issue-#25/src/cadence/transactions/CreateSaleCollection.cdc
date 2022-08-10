import ExampleToken from "../ExampleToken.cdc"
import ExampleNFT from "../ExampleNFT.cdc"
import NonFungibleToken from "../NonFungibleToken.cdc"
import ExampleMarketplace from "../ExampleMarketplace.cdc"

transaction {
    let ftref: Capability<&{ExampleToken.Receiver}>
    let nftref: Capability<&NonFungibleToken.Collection>
    prepare(acct: AuthAccount) {
        self.ftref = acct.getCapability<&{ExampleToken.Receiver}>(/public/FungibleTokenReceive)
        self.nftref = acct.getCapability<&NonFungibleToken.Collection>(ExampleNFT.CollectionStoragePath)
        acct.save(
            <- ExampleMarketplace.createSaleCollection(ownerCollection: self.nftref, ownerVault: self.ftref),
            to: /storage/ExampleMarketplace
        )
        acct.link<&{ExampleMarketplace.SalePublic}>(
            /public/ExampleMarketplace,
            target: /storage/ExampleMarketplace
        )
    }
}