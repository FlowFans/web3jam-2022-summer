// import NFTStorefront from "../../contracts/NFTStorefront.cdc"
// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import Main from "../../contracts/Main.cdc"
// import Component from "../../contracts/Component.cdc"
// import Pack from "../../contracts/Pack.cdc"
// import  from "../../contracts/.cdc"

// testnet
import NFTStorefront from 0x94b06cfca1d8a476
import NonFungibleToken from 0x631e88ae7f1d7c20
import Main from 0x76b2527585e45db4
import Component from 0x76b2527585e45db4
import Pack from 0x76b2527585e45db4


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
    
    var res : {String: [UInt64]} = {"Main": [], "Component": [], "Pack": []}

    for listingID in storefrontRef.getListingIDs() {
        var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
        if(listingDetail.purchased == false){
            var nftId = listingDetail.nftID
            switch listingDetail.nftType {
                case Type<@Main.NFT>():
                    res["Main"]!.append(nftId)
                case Type<@Component.NFT>():
                    res["Component"]!.append(nftId)
                case Type<@Pack.NFT>():
                    res["Pack"]!.append(nftId)
            }
        }
    }

    return res
}