import NFTStorefront from "../../contracts/NFTStorefront.cdc"
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import SoulMadePack from "../../contracts/SoulMadePack.cdc"
import SoulMade from "../../contracts/SoulMade.cdc"

// testnet
// import NFTStorefront from 0x94b06cfca1d8a476
// import NonFungibleToken from 0x631e88ae7f1d7c20
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadePack from 0x76b2527585e45db4
// import SoulMade from 0x76b2527585e45db4


/*
Get all NFT Ids that given account is selling
*/


pub fun main(address: Address): {String: [UInt64]} {
    
    let storefrontRef = getAccount(address)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow public storefront from address")
    
    var res : {String: [UInt64]} = {"SoulMadeMain": [], "SoulMadeComponent": [], "SoulMadePack": []}

    for listingID in storefrontRef.getListingIDs() {
        var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
        if(listingDetail.purchased == false){
            var nftId = listingDetail.nftID
            switch listingDetail.nftType {
                case Type<@SoulMadeMain.NFT>():
                    res["SoulMadeMain"]!.append(nftId)
                case Type<@SoulMadeComponent.NFT>():
                    res["SoulMadeComponent"]!.append(nftId)
                case Type<@SoulMadePack.NFT>():
                    res["SoulMadePack"]!.append(nftId)
            }
        }
    }

    return res
}