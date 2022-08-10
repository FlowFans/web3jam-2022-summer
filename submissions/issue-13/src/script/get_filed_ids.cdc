 import TicketNFT from 0xTicketNFT



 pub fun main(fieldSorted:String) :[UInt64]{
   let tempalteids=getfieldSortedTemplateIds(cityfieldSortedName:fieldSorted)
   return getNFTidsViaTemplateIds(ids:tempalteids)
 }



 pub fun getfieldSortedTemplateIds(fieldSorted:String):[UInt64]{
    return TicketNFT.fieldSorted[fieldSorted]!
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