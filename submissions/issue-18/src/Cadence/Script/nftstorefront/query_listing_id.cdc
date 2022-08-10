//import NFTStorefront from 0xf8d6e0586b0a20c7

// testnet
import NFTStorefront from 0xb4187e54e0ed55a8

pub fun main(listingResourceID: UInt64): NFTStorefront.ListingDetails {
    let storefrontRef = getAccount(0xb4187e54e0ed55a8)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow public storefront from address")

    let listing = storefrontRef.borrowListing(listingResourceID: listingResourceID)
        ?? panic("No item with that ID")
    
    return listing.getDetails()
}