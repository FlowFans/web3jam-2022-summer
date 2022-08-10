import NFTStorefront from 0x94b06cfca1d8a476

// This transaction installs the Storefront ressource in an account.

transaction {
    prepare(acct: AuthAccount) {

        // // If the account doesn't already have a Storefront
        // if acct.borrow<&NFTStorefront.Storefront>(from: NFTStorefront.StorefrontStoragePath) == nil {

        //     // Create a new empty .Storefront
        //     let storefront <- NFTStorefront.createStorefront() as! @NFTStorefront.Storefront
            
        //     // save it to the account
        //     acct.save(<-storefront, to: NFTStorefront.StorefrontStoragePath)

        //     // create a public capability for the .Storefront
        //     acct.link<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath, target: NFTStorefront.StorefrontStoragePath)
        // }
        
        //acct.link<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath, target: NFTStorefront.StorefrontStoragePath)
        //acct.unlink(NFTStorefront.StorefrontPublicPath)
        // todo: so the borrow must be exactly as "when linking", for example if link is <&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>, getCapability<&NFTStorefront.Storefront> won't work
        acct.link<&NFTStorefront.Storefront>(NFTStorefront.StorefrontPublicPath, target: NFTStorefront.StorefrontStoragePath)
    }
}
