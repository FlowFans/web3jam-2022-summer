import NFTStorefront from "../../contracts/NFTStorefront.cdc"
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import SoulMade from "../../contracts/SoulMade.cdc"
import SoulMadePack from "../../contracts/SoulMadePack.cdc"


// testnet
// import NFTStorefront from 0x94b06cfca1d8a476
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20

// main, component, pack
// todo: actually this function will be used in many places: for example, Drop page, Own Collection
// todo: so maybe this function should be added to contract as an independent function

/*
pub struct SaleItemDetail{
    pub let nftId: UInt64
    // todo: should we use optional, or should it just be empty?
    pub let price: UFix64
    pub let name: String?
    // main, component, category
    pub let nftType: String
    pub let scarcity: String
    pub let series: String?
    pub let description: String?
    pub let category: String
    pub let layer: UInt64
    pub let color: String
    pub let edition: UInt64
    pub let maxEdition: UInt64
    pub let ipfsHash: String?
    pub let componentDetails: [SoulMadeComponent.ComponentDetail?]

    init(id: UInt64,
        series: String,
        name: String,
        description: String,
        category: String,
        layer: UInt64,
        color: String,
        edition: UInt64,
        maxEdition: UInt64,
        ipfsHash: String) {
            self.id=id
            self.series=series
            self.name=name
            self.description=description
            self.category=category
            self.layer=layer
            self.color=color
            self.edition=edition
            self.maxEdition=maxEdition
            self.ipfsHash=ipfsHash
    }
}
*/




// for drop page


// Return ListingID and NFT details
pub fun main(): [UInt64] {
    let platformAddress : Address = 0x76b2527585e45db4
    let storefrontRef = getAccount(platformAddress)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow public storefront from address")

    var res : [UInt64] = []
    for listingID in storefrontRef.getListingIDs() {
        var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
        if(listingDetail.purchased == false){

            if(listingDetail.nftType == Type<@SoulMadeMain.NFT>()){
                var mainNftDeatil = SoulMade.getMainDetail(address: platformAddress, mainNftId: listingDetail.nftID)
                
                res.append(listingDetail.nftID)
            }else if(listingDetail.nftType == Type<@SoulMadeComponent.NFT>()){

            }

        }

    }

    return res
}



/*
pub fun main(address: Address, componentNftId: UInt64) : SoulMadeComponent.ComponentInfo? {

    let receiverRef = getAccount(address)
                      //.getCapability<&{NonFungibleToken.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
                      .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    //log(receiverRef.getIDs())
    
    return receiverRef.borrowComponentNFT(nftId : componentNftId)!.getComponentInfo()
    // return receiverRef.getIDs()
    //return nil
    
}


pub fun main(address: Address, mainNftId: UInt64) : {String : SoulMadeComponent.ComponentInfo} {

    let receiverRef = getAccount(address)
                      // todo: confirm this Interface
                      .getCapability<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    // return receiverRef.getIDs()
    return receiverRef.borrowMain(nftId: mainNftId).getAllComponents()
}
 */