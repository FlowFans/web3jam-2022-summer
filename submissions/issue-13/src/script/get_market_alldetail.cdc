import Marketplace from 0xMarket
import NonFungibleToken from 0xNonFungibleToken
import TicketNFT from 0xProfile
import MetadataViews from 0xNonFungibleToken


pub struct NFT {
    pub let id :UInt64
    pub let name: String
    pub let description: String
    pub let url: String
    pub let price: UFix64?
    pub let sellerAddress :Address
    pub let creator: Address
    pub let externalUrl: String
    pub let collection :TicketNFT.Ruple
    pub let properties: {String:String}?
    pub let receiver: [Address]
    pub let cut: [UFix64]
    pub let receiverdesc: [String]

    init(id : UInt64,
        name: String,
        description: String,
        url: String,
        price: UFix64?,
        sellerAddress :Address,
        receiver: [Address],
        cut: [UFix64],
        receiverdesc: [String],
        creator: Address,
        externalUrl: String,
        collection :TicketNFT.Ruple,
        properties: {String:String}?,
       ){
            self.id=id
            self.name=name
            self.description=description
            self.url=url
            self.price=price
            self.sellerAddress=sellerAddress
            self.receiver=receiver
            self.cut=cut
            self.receiverdesc=receiverdesc
            self.creator=creator
            self.externalUrl=externalUrl
            self.collection=collection
            //self.royalties=royalties
            self.properties=properties
        }
}


pub fun get_detail_message(sellerAddress: Address, ticketID: UInt64): NFT {

    let collectionRef = getAccount(sellerAddress).getCapability(/public/TicketNFTSaleCollection)
        .borrow<&{Marketplace.SalePublic}>()
        ?? panic("Could not get public sale reference")

    let nft =collectionRef.borrowTicketNFT(id: ticketID) ?? panic("Could not borrow a reference to the specified ticket")


    let view = nft.resolveView(Type<MetadataViews.Display>())!
    let TicketNFTView = nft.resolveView(Type<TicketNFT.TicketNFTMetadataView>())!
    let display = view as! MetadataViews.Display

    let TicketNFTDisplay =TicketNFTView as! TicketNFT.TicketNFTMetadataView

    return NFT(
        id:TicketNFTDisplay.id,
        name: display.name,
        description: display.description,
        url: display.thumbnail.uri(),
        price:collectionRef.getPrice(tokenID:ticketID),
        sellerAddress:sellerAddress,
        receiver: TicketNFTDisplay.receiver,
        cut:TicketNFTDisplay.cut,
        receiverdesc:TicketNFTDisplay.receiverdesc,
        creator: TicketNFTDisplay.creator,
        externalUrl:TicketNFTDisplay.externalUrl,
        collection:TicketNFTDisplay.collection,
        properties:TicketNFTDisplay.properties,
    )
}

pub fun main(): [NFT]{
    let arr :[NFT]=[]
    var i=0
    while i < Marketplace.tokensForSale.length{
         let collectionRef = getAccount(Marketplace.tokensForSale[i].address)
        .getCapability(/public/TicketNFTSaleCollection)
        .borrow<&{Marketplace.SalePublic}>()
        ?? panic("Could not borrow capability from public sale")
        let ids =collectionRef.getIDs()
        var j=0
            while j<ids.length{
                arr.append(get_detail_message(sellerAddress:Marketplace.tokensForSale[i].address,ticketID:ids[j]))
                j=j+1
            }
        i=i+1
    }
    return arr

}