// testnet
import SoulMade from 0xb4187e54e0ed55a8
import SoulMadePack from 0xb4187e54e0ed55a8
import NFTStorefront from 0xb4187e54e0ed55a8

pub fun main(address: Address,packID: UInt64) : {UInt64:UInt64}  {
    let storefrontRef = getAccount(address)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath)
        .borrow()
        ?? panic("Could not borrow public storefront from address")
    

    for listingID in storefrontRef.getListingIDs() {
        var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
        if listingDetail.purchased == false && listingDetail.nftType == Type<@SoulMadePack.NFT>() {
          var packNftId = listingDetail.nftID
          if packNftId == packID{
            return {listingID:packNftId}
          }
        }
    }

    return {0:0}
}