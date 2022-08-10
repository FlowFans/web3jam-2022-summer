//tickeTemplate


//0xd1183642a19fd336
 import TicketNFT from 0xd1183642a19fd336
//0x1dd1316850f649ea, 0xcc2156ea0b55aa52, 0xde0b02c3b3126a85]
//templateAddress
pub fun main(addr: Address):[UInt64]?{
    
     return TicketNFT.templateAddress[addr]
   // return TicketNFT.middleOwner.keys
}
