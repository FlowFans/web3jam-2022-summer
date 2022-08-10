
 import TicketNFT from 0xTicketNFT

//  import TicketNFT from "../cadence/TicketNFT.cdc"


import Marketplace from 0xMarketplace
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

pub fun main(account: Address):[NFT]{

    let account = getAccount(account)

    let collectionRef = account.getCapability(TicketNFT.CollectionPublicPath)
                            .borrow<&{TicketNFT.TicketNFTCollectionPublic}>() ?? panic("Could not get  TicketNFTCollectionPublic reference")
    let ids=collectionRef.getIDs()
    var i=0
    var res:[NFT]=[]
    while i<ids.length {
        
        res.append(getNFTMessage(account:account.address,ntfID:ids[i]))
        i=i+1
    }
    return res

}


pub fun getNFTMessage(account: Address,ntfID:UInt64):NFT{
    let account = getAccount(account)
    let collectionRef = account.getCapability(TicketNFT.CollectionPublicPath)
                            .borrow<&{TicketNFT.TicketNFTCollectionPublic}>() ?? panic("Could not get  TicketNFTCollectionPublic reference")
    let ids=collectionRef.getIDs()
    // self.recipientCollectionRef.getIDs().contains(self.minterNFTID): "The next NFT ID should have been minted and delivered"
    assert(ids.contains(ntfID), message: "wrong NFTIDs")
    let nft =collectionRef.borrowTicketNFT(id: ntfID) ?? panic("The NFT does not exist")
    let view = nft.resolveView(Type<TicketNFT.TicketDetail>())!
    let TicketNFTDisplay =view as! TicketNFT.TicketDetail
    let price= Marketplace.ItemsPrice[ntfID]
    return NFT(
        id: ntfID,
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
        owner:account.address,
        price:price,
        number:UInt64(1)
    )

}


