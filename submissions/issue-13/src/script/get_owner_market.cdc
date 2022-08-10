import TicketNFT from 0xTicketNFT
import Marketplace from 0xMarketplace
import NonFungibleToken from  0xNonFungibleToken
import MetadataViews from  0xNonFungibleToken


// import TicketNFT from 0xd1183642a19fd336
// import Marketplace from 0x1dd1316850f649ea
// import NonFungibleToken from  0x631e88ae7f1d7c20
// import MetadataViews from  0x631e88ae7f1d7c20

pub struct NFT {
        pub let id: UInt64
        pub let templateID: UInt64
        pub let typeID: UInt64
        pub let name: String
        pub let desc: String
        pub let ticketType: String
        pub let url: String
        pub let performDate: UFix64
        pub let artists: [String]
        pub let field: String
        pub let fieldImg: String
        pub let detailAddress: String
        pub let city: String
        pub let owner: Address
        pub let price: UFix64?

    init(
        id: UInt64,
        templateID: UInt64,
        typeID: UInt64,
        name: String,
        desc: String,
        ticketType: String,
        url: String,
        performDate: UFix64,
        artists: [String],
        field: String,
        fieldImg: String,
        detailAddress: String,
        city: String,
        owner:Address,
        price: UFix64?
        ){
            self.id=id
            self.templateID=templateID
            self.typeID=typeID
            self.name=name
            self.desc=desc
            self.ticketType=ticketType
            self.url=url
            self.performDate=performDate
            self.artists=artists
            self.field=field
            self.fieldImg=fieldImg
            self.detailAddress=detailAddress
            self.city=city
            self.owner=owner
            self.price=price
        }
}


pub fun get_detail_message(sellerAddress: Address, ticketID: UInt64): NFT {

    let collectionRef = getAccount(sellerAddress).getCapability(Marketplace.MarketplacePublicPath)
        .borrow<&{Marketplace.SalePublic}>()
        ?? panic("Could not get public sale reference")

    let nft =collectionRef.borrowTicketNFT(id: ticketID) ?? panic("Could not borrow a reference to the specified ticket")


   // let view = nft.resolveView(Type<MetadataViews.Display>())!
    let TicketNFTView = nft.resolveView(Type<TicketNFT.TicketDetail>())!
    //let display = view as! MetadataViews.Display

    let TicketNFTDisplay =TicketNFTView as! TicketNFT.TicketDetail
    let price= Marketplace.ItemsPrice[ticketID]
    return NFT(
        id: TicketNFTDisplay.id,
        templateID: TicketNFTDisplay.templateID,
        typeID: TicketNFTDisplay.typeID,
        name: TicketNFTDisplay.name,
        desc: TicketNFTDisplay.desc,
        ticketType: TicketNFTDisplay.ticketType,
        url: TicketNFTDisplay.url,
        performDate: TicketNFTDisplay.performDate,
        artists: TicketNFTDisplay.artists,
        field: TicketNFTDisplay.field,
        fieldImg: TicketNFTDisplay.fieldImg,
        detailAddress: TicketNFTDisplay.detailAddress,
        city: TicketNFTDisplay.city,
        owner:sellerAddress,
        price: price
    )
    
}




pub fun main(address: Address):[NFT]{
    var res:[NFT]=[]
    var i=0
    let collectionRef = getAccount(address)
        .getCapability(Marketplace.MarketplacePublicPath)
        .borrow<&Marketplace.SaleCollection{Marketplace.SalePublic}>()
        ?? panic("Could not borrow capability from public sale")
    let ids =collectionRef.getIDs()
    while i< ids.length{
         res.append(get_detail_message(sellerAddress: address, ticketID: ids[i]))
         i=i+1
    }
    return res
 
}