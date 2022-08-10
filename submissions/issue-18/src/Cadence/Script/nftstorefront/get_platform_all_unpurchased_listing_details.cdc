//import NFTStorefront from 0xf8d6e0586b0a20c7

// testnet
import NFTStorefront from 0x94b06cfca1d8a476

// This script returns an array of all the nft uuids for sale through a Storefront

// pub fun main(): [UInt64] {
//     let storefrontRef = getAccount(0xf8d6e0586b0a20c7)
//         .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
//             NFTStorefront.StorefrontPublicPath
//         )
//         .borrow()
//         ?? panic("Could not borrow public storefront from address")
    
//     return storefrontRef.getListingIDs()
// }


// Return ListingDetails of each ListingID
pub fun main(): [{UInt64: NFTStorefront.ListingDetails}] {
    let storefrontRef = getAccount(0x76b2527585e45db4)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow public storefront from address")
    

    var res : [{UInt64: NFTStorefront.ListingDetails}] = []
    for listingID in storefrontRef.getListingIDs() {
        if !storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails().purchased{
            res.append({listingID : storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()})
        }
        
    }

    return res
}
