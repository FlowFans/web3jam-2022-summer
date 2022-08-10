export const CREATE_ADMIN_STORE = `
import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken
import FlowToken from 0xFlowToken
import FungibleToken from 0xFungibleToken
import PioneerMarketplace from 0xPioneerNFT

transaction() {
    
    prepare(admin:AuthAccount){

         if admin.borrow<&PioneerMarketplace.Storefront>(from: PioneerMarketplace.StorefrontActivityStoragePath) == nil {

            // Create a new empty .Storefront
            let storefront <- PioneerMarketplace.createStorefront() as! @PioneerMarketplace.Storefront
            
            // save it to the account
            admin.save(<-storefront, to: PioneerMarketplace.StorefrontActivityStoragePath)

            // create a public capability for the .Storefront
            admin.link<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(PioneerMarketplace.StorefrontActivityPublicPath, target: PioneerMarketplace.StorefrontActivityStoragePath)
        }
    }
    execute{

    }
}

`