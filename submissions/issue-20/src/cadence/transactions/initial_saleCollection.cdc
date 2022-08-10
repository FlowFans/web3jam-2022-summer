import ExampleMarketplace from 0xb10db40892311e63
import FlowToken from 0x7e60df042a9c0868
import ExampleNFT from 0xb10db40892311e63
import FungibleToken from 0x9a0766d93b6608b7

transaction {
    let ownerCollection: Capability<&ExampleNFT.Collection>
    let ownerVault: Capability<&{FungibleToken.Receiver}>

    prepare(acct: AuthAccount) {
        self.ownerCollection = acct.link<&ExampleNFT.Collection>(/private/exampleNFTCollection,
                                target: ExampleNFT.CollectionStoragePath)
                                ?? panic("no collection resource")
        self.ownerVault = acct.getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver)
        
        acct.save(
            <-ExampleMarketplace.createSaleCollection(ownerCollection: self.ownerCollection, ownerVault: self.ownerVault),
            to: ExampleMarketplace.SaleStoragePath
            )
        
        acct.link<&{ExampleMarketplace.SalePublic}>(ExampleMarketplace.SalePublicPath,
            target:ExampleMarketplace.SaleStoragePath)

    }
}