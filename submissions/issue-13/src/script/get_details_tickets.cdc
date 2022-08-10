 import TicketNFT from 0xTicketNFT

import Marketplace from 0xMarketplace




pub struct  ticket{
   pub var price :UFix64
   pub var detail:TicketNFTtemplate

   init(
      price :UFix64,
      detail:TicketNFTtemplate
   ){
      self.price=price
      self.detail=detail
   }

}

pub fun main():[ticket]{
   var ticketdetail:[ticket]=[]

}

fun get_ticket_detail(address:Address ,id:UInt64):ticket{

   let price= Marketplace.ItemsPrice[id]!

   let detail =TicketNFT.tickeTemplate[id]!

   return ticket(
      price:price,
      detail:detail
   )
}


 pub fun getcitys(cityName:String) :[UInt64]{
   let tempalteids=getCityTemplateIds(cityName:cityName)
   return getNFTidsViaTemplateIds(ids:tempalteids)
 }



 pub fun getCityTemplateIds(cityName:String):[UInt64]{

    return TicketNFT.citySorted[cityName]!
 }

 pub fun getNFTidsViaTemplateIds(ids :[UInt64]):[UInt64]{
    var result:[UInt64]=[]
    var i=0
    while  i<ids.length {
        let nftids=TicketNFT.tickeMapping[ids[i]]!
        result.appendAll(nftids)
        i=i+1
    }
    return result
 }