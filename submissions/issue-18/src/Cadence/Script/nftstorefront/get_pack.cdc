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


pub struct SaleItemDetail {
    // todo: oh, shit, there should also be an listingId
    pub let listingId: UInt64
    pub let nftId: UInt64
    pub let price: UFix64
    // main, component, category
    // todo: should this be string or?
    pub let nftType: String
    pub let mainDetail: SoulMadeMain.MainDetail?
    pub let componentDetail: SoulMadeComponent.ComponentDetail?
    pub let packDetail: SoulMadePack.PackDetail?
    pub let listingDetail: NFTStorefront.ListingDetails

    init(listingId: UInt64, 
            nftId: UInt64,
            price: UFix64,
            nftType: String,
            mainDetail: SoulMadeMain.MainDetail?,
            componentDetail: SoulMadeComponent.ComponentDetail?,
            packDetail: SoulMadePack.PackDetail?,
            listingDetail: NFTStorefront.ListingDetails){
                self.listingId = listingId
                self.nftId = nftId
                self.price = price
                self.nftType = nftType
                self.mainDetail = mainDetail
                self.componentDetail = componentDetail
                self.packDetail = packDetail
                self.listingDetail = listingDetail
    }
}

pub fun main(): [SaleItemDetail] {
    //testnet
    // let platformAddress: Address = 0x76b2527585e45db4
    let platformAddress: Address = 0xf8d6e0586b0a20c7
    let storefrontRef = getAccount(platformAddress)
        .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
            NFTStorefront.StorefrontPublicPath
        )
        .borrow()
        ?? panic("Could not borrow public storefront from address")
    
    var res : [SaleItemDetail] = []
    for listingID in storefrontRef.getListingIDs() {
        var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
        if(listingDetail.purchased == false){
            // if(listingDetail.nftType == Type<@SoulMadeComponent.NFT>()){
            //     var nftId = listingDetail.nftID
            //     var componentDetail = SoulMade.getComponentDetail(address: platformAddress, componentNftId: nftId)
            //     var detail = SaleItemDetail(listingId: listingID, nftId: listingDetail.nftID, price: listingDetail.salePrice, nftType: "SoulMadeComponent", mainDetail: nil, componentDetail: componentDetail , packDetail: nil, listingDetail: listingDetail)
            //     res.append(detail)
            //     // todo: is there a break in Cadence?
            //     continue
            // }else if(listingDetail.nftType == Type<@SoulMadeMain.NFT>()){
            //     var nftId = listingDetail.nftID
            //     var mainDetail = SoulMade.getMainDetail(address: platformAddress, mainNftId: nftId)
            //     var detail = SaleItemDetail(listingId: listingID, nftId: listingDetail.nftID, price: listingDetail.salePrice, nftType: "SoulMadeMain", mainDetail: mainDetail, componentDetail: nil , packDetail: nil, listingDetail: listingDetail)
            //     res.append(detail)
            //     // todo: is there a break in Cadence?
            //     continue
            // }else 
            if(listingDetail.nftType == Type<@SoulMadePack.NFT>()){
                var nftId = listingDetail.nftID
                var packDetail = SoulMade.getPackDetail(address: platformAddress, packNftId: nftId)
                var detail = SaleItemDetail(listingId: listingID, nftId: listingDetail.nftID, price: listingDetail.salePrice, nftType: "SoulMadePack", mainDetail: nil, componentDetail: nil , packDetail: packDetail, listingDetail: listingDetail)
                res.append(detail)
                // todo: is there a break in Cadence?
                continue
            }
        }
    }

    return res
}



/*
Result: 
[
s.4ab1a500f0f8271d193d7252c47dff82c9d4539f827e53bf9bc7678c64016ef1.SaleItemDetail(listingId: 24414768, nftId: 88, price: 0.33000000, nftType: "SoulMadeComponent", mainDetail: nil, componentDetail: A.76b2527585e45db4.SoulMadeComponent.ComponentDetail(id: 88, series: "Souly", name: "Normal Hand", description: "Normal Hand", category: "Hand", layer: 7, color: "Purple", edition: 3, maxEdition: 5, ipfsHash: "Qm14"), packDetail: nil, listingDetail: A.94b06cfca1d8a476.NFTStorefront.ListingDetails(storefrontID: 24253339, purchased: false, nftType: Type<A.76b2527585e45db4.SoulMadeComponent.NFT>(), nftID: 88, salePaymentVaultType: Type<A.7e60df042a9c0868.FlowToken.Vault>(), salePrice: 0.33000000, saleCuts: [A.94b06cfca1d8a476.NFTStorefront.SaleCut(receiver: Capability<&A.7e60df042a9c0868.FlowToken.Vault{A.9a0766d93b6608b7.FungibleToken.Receiver}>(address: 0x76b2527585e45db4, path: /public/flowTokenReceiver), amount: 0.33000000)])), 
s.4ab1a500f0f8271d193d7252c47dff82c9d4539f827e53bf9bc7678c64016ef1.SaleItemDetail(listingId: 24414763, nftId: 0, price: 2.00000000, nftType: "SoulMadeMain", mainDetail: A.76b2527585e45db4.SoulMadeMain.MainDetail(id: 0, name: "Guisong", description: "", ipfsHash: "", componentDetails: [A.76b2527585e45db4.SoulMadeComponent.ComponentDetail(id: 93, series: "Souly", name: "Blue Sky", description: "Blue Sky", category: "Background", layer: 1, color: "Blue", edition: 3, maxEdition: 5, ipfsHash: "Qm1")]), componentDetail: nil, packDetail: nil, listingDetail: A.94b06cfca1d8a476.NFTStorefront.ListingDetails(storefrontID: 24253339, purchased: false, nftType: Type<A.76b2527585e45db4.SoulMadeMain.NFT>(), nftID: 0, salePaymentVaultType: Type<A.7e60df042a9c0868.FlowToken.Vault>(), salePrice: 2.00000000, saleCuts: [A.94b06cfca1d8a476.NFTStorefront.SaleCut(receiver: Capability<&A.7e60df042a9c0868.FlowToken.Vault{A.9a0766d93b6608b7.FungibleToken.Receiver}>(address: 0x76b2527585e45db4, path: /public/flowTokenReceiver), amount: 2.00000000)]))
]
*/