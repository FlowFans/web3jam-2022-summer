// import  Marketplace from "../cadence/Marketplace.cdc"


import Marketplace from 0xMarketplace

transaction(beneficiaryAccount: Address, cutPercentage: UFix64) {
    prepare(acct: AuthAccount) {
        if acct.borrow<&Marketplace.SaleCollection>(from: Marketplace.MarketplaceStoragePath) == nil {
            let ownerCapability = acct.getCapability( /public/fusdReceiver)
            //let beneficiaryCapability = getAccount(beneficiaryAccount).getCapability(/public/fusdReceiver)
            let collection <- Marketplace.createSaleCollection(ownerCapability: ownerCapability, beneficiaryCapability: ownerCapability, cutPercentage: cutPercentage)
            acct.save(<-collection, to:  Marketplace.MarketplaceStoragePath)
            acct.link<&Marketplace.SaleCollection{Marketplace.SalePublic}>(Marketplace.MarketplacePublicPath, target:  Marketplace.MarketplaceStoragePath)
        }   
        
    }
}