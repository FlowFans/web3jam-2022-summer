import NFTStorefront from 0xb4187e54e0ed55a8
import SoulMadePack from 0xb4187e54e0ed55a8
import SoulMade from 0xb4187e54e0ed55a8

pub fun main(listingID: UInt64): SoulMadePack.PackDetail {
    //testnet
    let platformAddress: Address = 0xb4187e54e0ed55a8
    let storefrontRef = getAccount(platformAddress)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath)
        .borrow()
        ?? panic("Could not borrow public storefront from address")
        
    var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()

    return SoulMade.getPackDetail(address: platformAddress, packNftId: listingDetail.nftID)
}