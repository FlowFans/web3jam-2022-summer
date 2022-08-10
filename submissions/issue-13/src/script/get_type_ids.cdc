 import TicketNFT from 0xTicketNFT



 pub fun main(typeSorted:String) :[UInt64]{
   let tempalteids=gettypeSortedTemplateIds(typeSorted:fieldSorted)
   return getNFTidsViaTemplateIds(ids:tempalteids)
 }



 pub fun gettypeSortedTemplateIds(typeSorted:String):[UInt64]{
    return TicketNFT.typeSorted[typeSorted]!
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


