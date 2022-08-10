import NonFungibleToken from 0xNonFungibleToken
import TicketNFT from 0xf3b742ab419080e3
import MetadataViews from 0xNonFungibleToken



pub struct NFT {
    pub let id :UInt64
    pub let name: String
    pub let description: String
    pub let url: String
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

pub fun main(address: Address,ids:[UInt64]): [NFT] {

   let account = getAccount(address)

    let collectionRef = account.getCapability(/public/TicketNFTCollection)
                            .borrow<&{TicketNFT.TicketNFTCollectionPublic}>()?? panic("Could not get reference")

    let arr:[NFT]=[]
    var i=0
    while i<ids.length{
           let nft =collectionRef.borrowTicketNFT(id: ids[i]) ?? panic("The NFT does not exist")
     //log(collectionRef.getMetadata(id: id))

    // Get the basic display information for this NFT
    let view = nft.resolveView(Type<MetadataViews.Display>())!
    let expectedRoyaltyView = nft.resolveView(Type<MetadataViews.Royalties>())!
    let royaltyView = expectedRoyaltyView as! MetadataViews.Royalties
    let TicketNFTView = nft.resolveView(Type<TicketNFT.TicketNFTMetadataView>())!
    let display = view as! MetadataViews.Display
    let TicketNFTDisplay =TicketNFTView as! TicketNFT.TicketNFTMetadataView
     i=i+1
    arr.append(NFT(
        id:TicketNFTDisplay.id,
        name: display.name,
        description: display.description,
        url: display.thumbnail.uri(),
        receiver: TicketNFTDisplay.receiver,
        cut:TicketNFTDisplay.cut,
        receiverdesc:TicketNFTDisplay.receiverdesc,
        creator: TicketNFTDisplay.creator,
        externalUrl:TicketNFTDisplay.externalUrl,
        collection:TicketNFTDisplay.collection,
        properties:TicketNFTDisplay.properties,
    ))
    }


    return arr
}