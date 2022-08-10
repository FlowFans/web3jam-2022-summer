// import TicketNFT from "../cadence/TicketNFT.cdc"
// import Marketplace from "../cadence/Marketplace.cdc" 

import TicketNFT from 0xTicketNFT
import Marketplace from  0xMarketplace


transaction() {
    
    let collectionRef: &TicketNFT.Collection
    let saleCollectionRef: &Marketplace.SaleCollection

    prepare(acct: AuthAccount) {
        // assert(ticketNFT.length!=price.length, message: "param is wrong")
        // borrow a reference to the TicketNFT Collection
        self.collectionRef = acct.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath)
            ?? panic("Could not borrow from TicketNFT in storage")
      
        self.saleCollectionRef = acct.borrow<&Marketplace.SaleCollection>(from: Marketplace.MarketplaceStoragePath)
            ?? panic("Could not borrow from TicketNFT sale in storage")
    }

    execute {

        var ticketNFT :[UInt64]=[5,6,7]
        var prices=[1.1,1.1,1.1]
        var i=0
        while i<ticketNFT.length {
             let token <- self.collectionRef.withdraw(withdrawID: ticketNFT[i]) as! @TicketNFT.NFT

             self.saleCollectionRef.listForSale(token: <-token, price: prices[i])
             i=i+1
        }

       
    }
}

