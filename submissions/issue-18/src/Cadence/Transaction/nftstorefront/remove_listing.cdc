import NFTStorefront from "../../contracts/NFTStorefront.cdc"

//testnet
// import NFTStorefront from 0xb4187e54e0ed55a8

transaction(listingResourceID: UInt64) {
    let storefront: &NFTStorefront.Storefront{NFTStorefront.StorefrontManager}

    prepare(acct: AuthAccount) {
        self.storefront = acct.borrow<&NFTStorefront.Storefront{NFTStorefront.StorefrontManager}>(from: NFTStorefront.StorefrontStoragePath)
            ?? panic("Missing or mis-typed NFTStorefront.Storefront")
    }

    execute {
        self.storefront.removeListing(listingResourceID: listingResourceID)
    }
}


// [24306492, 24306493, 24305819, 24306488, 24404397, 24306145, 24306168]