import TicketNFT from 0xTicketNFT


transaction(setAddress :Address){
    prepare(acc :AuthAccount){

    }

    execute {
     assert(getAccount(setAddress).getCapability<&TicketNFT.Collection{TicketNFT.TicketNFTCollectionPublic}>(TicketNFT.CollectionPublicPath).borrow()!=nil)
    }
}