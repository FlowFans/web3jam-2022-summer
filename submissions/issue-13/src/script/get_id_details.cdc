
 import TicketNFT from 0xTicketNFT



 pub fun main(id: UInt64) :TicketNFT.TicketNFTtemplate{

    return  TicketNFT.tickeTemplate[id]!
 }