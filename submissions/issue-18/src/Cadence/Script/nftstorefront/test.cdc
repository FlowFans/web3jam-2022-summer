// import NFTStorefront from "../../contracts/NFTStorefront.cdc"
// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadePack from "../../contracts/SoulMadePack.cdc"
// import SoulMade from "../../contracts/SoulMade.cdc"


// testnet
import NFTStorefront from 0x94b06cfca1d8a476
import NonFungibleToken from 0x631e88ae7f1d7c20
import SoulMadeMain from 0x76b2527585e45db4
import SoulMadeComponent from 0x76b2527585e45db4
import SoulMadePack from 0x76b2527585e45db4
import SoulMade from 0x76b2527585e45db4

pub struct SaleItemDetail {
    // todo: oh, shit, there should also be an listingId
    pub let nftId: UInt64
    pub let price: UFix64
    // main, component, category
    // todo: should this be string or?
    pub let nftType: String
    pub let mainDetail: SoulMadeMain.MainDetail?
    pub let componentDetail: SoulMadeComponent.ComponentDetail?
    pub let packDetail: SoulMadePack.PackDetail?

    init(nftId: UInt64,
            price: UFix64,
            nftType: String,
            mainDetail: SoulMadeMain.MainDetail?,
            componentDetail: SoulMadeComponent.ComponentDetail?,
            packDetail: SoulMadePack.PackDetail?){
                self.nftId = nftId
                self.price = price
                self.nftType = nftType
                self.mainDetail = mainDetail
                self.componentDetail = componentDetail
                self.packDetail = packDetail
    }

}

//pub fun main(): [SaleItemDetail] {
  pub fun main(): [UInt64] {
    //testnet
    let platformAddress: Address = 0x76b2527585e45db4
    // let platformAddress: Address = 0xf8d6e0586b0a20c7
    let storefrontRef = getAccount(platformAddress)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow public storefront from address")
    
    var res : [SaleItemDetail] = []
    var res2 : [UInt64] = []
    
    for listingID in storefrontRef.getListingIDs() {
        var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
        if(listingDetail.purchased == false){
          if(listingDetail.nftType == Type<@SoulMadeMain.NFT>()){
                var nftId: UInt64 = listingDetail.nftID
                // todo: the problem is here
                // nftId = 0
                var mainDetail: SoulMadeMain.MainDetail = SoulMade.getMainDetail(address: platformAddress, mainNftId: 7)
                var detail = SaleItemDetail(nftId: listingDetail.nftID, price: listingDetail.salePrice, nftType: "SoulMadeMain", mainDetail: mainDetail, componentDetail: nil , packDetail: nil)
                res.append(detail)
                res2.append(nftId)
        }
      }
    }

    return res2
}