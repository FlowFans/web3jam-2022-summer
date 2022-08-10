 import TicketNFT from 0xTicketNFT



 pub fun main(start:UFix64,end:UFix64) :[UInt64]{
   let tempalteids=getTimeTemplateIds(start:start,end:end)
   return getNFTidsViaTemplateIds(ids:tempalteids)
 }



 pub fun getTimeTemplateIds(start:UFix64,end:UFix64):[UInt64]{
   let timearr= TicketNFT.timeSorted.keys
    var i=0
    var arr:[UInt64]=[]
    while i<timearr.length {
        if start<=timearr[i] && timearr[i]<=end{
            let tmp=TicketNFT.timeSorted[timearr[i]]!
            arr.appendAll(tmp)
        }
    }
    return arr
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