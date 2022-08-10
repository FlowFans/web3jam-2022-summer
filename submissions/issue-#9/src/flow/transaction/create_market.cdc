
import PioneerMarketplace from "../cadence/PioneerMarketplace.cdc"

transaction() {
    
    prepare(account: AuthAccount){

             if account.borrow<&PioneerMarketplace.Storefront>(from: PioneerMarketplace.StorefrontStoragePath) == nil {

            // Create a new empty .Storefront
            let storefront <- PioneerMarketplace.createStorefront() as! @PioneerMarketplace.Storefront
            
            // save it to the account
            account.save(<-storefront, to: PioneerMarketplace.StorefrontStoragePath)

            // create a public capability for the .Storefront
            account.link<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(PioneerMarketplace.StorefrontPublicPath, target: PioneerMarketplace.StorefrontStoragePath)
        }

    }
    execute{

    }
}