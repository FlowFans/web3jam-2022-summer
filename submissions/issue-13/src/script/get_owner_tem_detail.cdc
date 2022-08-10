

//  import TicketNFT from 0xTicketNFT


 import TicketNFT from 0xTicketNFT




 pub fun main(owner: Address) :[TicketNFT.TicketNFTtemplate]{
    var arr:[UInt64]=[]
    if TicketNFT.templateAddress[owner]!=nil{
        arr=TicketNFT.templateAddress[owner]!
    }

    var i=0
    var res :[TicketNFT.TicketNFTtemplate]=[]
    while i<arr.length {
        if TicketNFT.tickeTemplate.containsKey(arr[i]){
            res.append(TicketNFT.tickeTemplate[arr[i]]!)
        }
        i=i+1
    }
    return res
 }

//  pub  fun main(owner: Address):[Address]{
//     let keys_word =TicketNFT.templateAddress.keys

//     return keys_word
//  }
