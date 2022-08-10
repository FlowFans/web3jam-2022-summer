import TicketNFT from "../cadence/TicketNFT.cdc"
import Marketplace from "../cadence/Marketplace.cdc" 


transaction(ticketNFT: UInt64, price: UFix64) {
    
    let collectionRef: &TicketNFT.Collection
    let saleCollectionRef: &Marketplace.SaleCollection

    prepare(acct: AuthAccount) {

        // borrow a reference to the TicketNFT Collection
        self.collectionRef = acct.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath)
            ?? panic("Could not borrow from TicketNFT in storage")
      
        self.saleCollectionRef = acct.borrow<&Marketplace.SaleCollection>(from: Marketplace.MarketplaceStoragePath)
            ?? panic("Could not borrow from TicketNFT sale in storage")
    }

    execute {

        // withdraw the specified token from the collection

        let token <- self.collectionRef.withdraw(withdrawID: ticketNFT) as! @TicketNFT.NFT

        self.saleCollectionRef.listForSale(token: <-token, price: price)
    }
}

