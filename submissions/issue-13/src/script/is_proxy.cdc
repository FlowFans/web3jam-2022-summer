// import TicketNFT from 0xTicketNFT


import TicketNFT from 0xTicketNFT

/***


 */

pub fun main(account :Address):Bool{
    return TicketNFT.middleOwner.containsKey(account)
}