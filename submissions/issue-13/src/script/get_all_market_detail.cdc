// import TicketNFT from 0xa3c1282a571e9c9e
// import Marketplace from 0xa49ebdc0c27b0aa8
// import NonFungibleToken from  0x631e88ae7f1d7c20
// import MetadataViews from  0x631e88ae7f1d7c20



import TicketNFT from 0xTicketNFT
import Marketplace from 0xMarketplace
import NonFungibleToken from  0xNonFungibleToken
import MetadataViews from  0xNonFungibleToken

pub var nftarr:{UInt64:NFT}={}

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
        pub let number: UInt64

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
        price: UFix64?,
        number: UInt64
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
            self.number=number
        }
}


pub fun get_detail_message(sellerAddress: Address, ticketID: UInt64): NFT {

    let collectionRef = getAccount(sellerAddress).getCapability(Marketplace.MarketplacePublicPath)
        .borrow<&{Marketplace.SalePublic}>()
        ?? panic("Could not get public sale reference")

    let nft =collectionRef.borrowTicketNFT(id: ticketID) ?? panic("Could not borrow a reference to the specified ticket")


    let view = nft.resolveView(Type<MetadataViews.Display>())!
    let TicketNFTView = nft.resolveView(Type<TicketNFT.TicketDetail>())!
    let display = view as! MetadataViews.Display

    let TicketNFTDisplay =TicketNFTView as! TicketNFT.TicketDetail
    let price= Marketplace.ItemsPrice[ticketID]
    nftarr[ticketID]= NFT(
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
        price: price,
        number:0,
    )
    return nftarr[ticketID]!
}

pub fun get_all_nft():[NFT]{

    let arr :[NFT]=[]
    var i=0
    let keys=TicketNFT.middleOwner.keys
    while i < keys.length{
         let collectionRef = getAccount(keys[i])
        .getCapability(Marketplace.MarketplacePublicPath)
        .borrow<&Marketplace.SaleCollection{Marketplace.SalePublic}>()
        ?? panic("Could not borrow capability from public sale")
        log(i)
        let ids =collectionRef.getIDs()
        var j=0
            while j<ids.length{
                arr.append(get_detail_message(sellerAddress:keys[i],ticketID:ids[j]))
                j=j+1
            }
        i=i+1
    }
    return arr
}


pub fun get_nft_detail_by_ids(ids:[UInt64]):[NFT]{

     let arr :[NFT]=[]

     var i=0

     while i<ids.length {
        arr.append(nftarr[ids[i]]!)
        i=i+1
     }
     return arr
}

pub fun main():[NFT]{
    let arr =get_all_nft()
    return arr
}

