 import TicketNFT from 0xTicketNFT
 import MetadataViews from 0xNonFungibleToken
 import Marketplace from 0xMarketplace


    pub struct Ordering{
        pub let id: UInt64
        pub let orderName: String
        pub let orderAmount: UFix64
        pub let ticketPrice: UFix64
        pub let ticketAmount: UFix64
        pub let ticketDetail: TicketNFT.TicketDetail
        init(
        id: UInt64,
        orderName:String,
        orderAmount:UFix64,
        ticketPrice: UFix64,
        ticketAmount:UFix64,
        ticketDetail:TicketNFT.TicketDetail
        ){
            self.id=id
            self.orderName=orderName
            self.orderAmount=orderAmount
            self.ticketPrice=ticketPrice
            self.ticketAmount=ticketAmount
            self.ticketDetail=ticketDetail
        }
    }


    pub fun main(){


    }


    pub fun  getOrdering(address: Address,tokenID: UInt64,ticketNum: UFix64):Ordering{
        let account = getAccount(address)
        let collectionRef = account.getCapability(/public/TicketNFTCollection)
                            .borrow<&{TicketNFT.TicketNFTCollectionPublic}>() ?? panic("message")

        let nft =collectionRef.borrowTicketNFT(id: tokenID) ?? panic("The NFT does not exist")


        let TicketNFTView = nft.resolveView(Type<TicketNFT.TicketDetail>())!

        let TicketNFTDisplay =TicketNFTView as! TicketNFT.TicketDetail

        let detail = TicketNFT.TicketDetail(
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
        )

        return Ordering(
            id:1,
            orderName:"",
            orderAmount: ticketNum*Marketplace.ItemsPrice[tokenID]!,
            ticketPrice: Marketplace.ItemsPrice[tokenID]!,
            ticketAmount:ticketNum,
            ticketDetail:detail
        )

    }


