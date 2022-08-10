import NonFungibleToken from 0xNonFungibleToken
import TicketNFT from 0xProfile
import MetadataViews from 0xNonFungibleToken

pub struct NFT {
    pub let name: String
    pub let description: String
    pub let url: String
    pub let creator: Address
    pub let externalUrl: String
    pub let collection :TicketNFT.Ruple
    pub let properties: {String:String}?


    init( name: String,
        description: String,
        url: String,
        creator: Address,
        externalUrl: String,
        collection :TicketNFT.Ruple,
        properties: {String:String}?,

       ){
            self.name=name
            self.description=description
            self.url=url
            self.creator=creator
            self.externalUrl=externalUrl
            self.collection=collection
          //  self.royalties=royalties
            self.properties=properties
        }
}

pub fun main(address: Address, id: UInt64): NFT {
    let account = getAccount(address)

    let collectionRef = account.getCapability(/public/TicketNFTCollection)
                            .borrow<&{TicketNFT.TicketNFTCollectionPublic}>() ?? panic("no exist")

    let nft =collectionRef.borrowTicketNFT(id: id) ?? panic("The NFT does not exist")
     //log(collectionRef.getMetadata(id: id))

    // Get the basic display information for this NFT
    let view = nft.resolveView(Type<MetadataViews.Display>())!


    let TicketNFTView = nft.resolveView(Type<TicketNFT.TicketNFTMetadataView>())!




    let display = view as! MetadataViews.Display

    let TicketNFTDisplay =TicketNFTView as! TicketNFT.TicketNFTMetadataView

    return NFT(
        name: display.name,
        description: display.description,
        url: display.thumbnail.uri(),
        creator: TicketNFTDisplay.creator,
        externalUrl:TicketNFTDisplay.externalUrl,
        collection:TicketNFTDisplay.collection,
        properties:TicketNFTDisplay.properties,
      //  royalties:nft.getRoyalties()
    )
}