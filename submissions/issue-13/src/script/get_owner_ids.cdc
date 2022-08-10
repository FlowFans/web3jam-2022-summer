
 import TicketNFT from 0xTicketNFT



 pub fun main(owner: Address) :[UInt64]{

    return  TicketNFT.ticketAddress[owner]!
 }
