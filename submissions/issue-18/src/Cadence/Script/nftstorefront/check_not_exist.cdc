// testnet
import SoulMade from 0xb4187e54e0ed55a8
import SoulMadePack from 0xb4187e54e0ed55a8
import NFTStorefront from 0xb4187e54e0ed55a8

pub fun getPackListingIdsPerSeries(address: Address): [UInt64] {
    let storefrontRef = getAccount(address)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath)
        .borrow()
        ?? panic("Could not borrow public storefront from address")
    
    var res: [UInt64] = []

    for listingID in storefrontRef.getListingIDs() {
        var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
        if listingDetail.purchased == false && listingDetail.nftType == Type<@SoulMadePack.NFT>() {
          var packNftId = listingDetail.nftID
          res.append(listingID)
        }
    }
    return res
}


pub fun main(address: Address) : [UInt64] {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
        
    var packIdList: [UInt64] = receiverRef.getIDs()
    
    var listingPackIdList: [UInt64] = getPackListingIdsPerSeries(address: address)

    // var res: [UInt64] = []

    // items that are in the Listing but not in the pack
    // for id in listingPackIdList{
    //   if packIdList.contains(id) == true {
    //     res.append(id)
    //   }
    // }

    // items that are in the pack but not in the listing
    // for id in packIdList{
    //   if listingPackIdList.contains(id) == false {
    //     res.append(id)
    //   }
    // }    

    return listingPackIdList
}