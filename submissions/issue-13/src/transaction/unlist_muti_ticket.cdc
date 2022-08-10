import TicketNFT from 0xTicketNFT
import Marketplace from 0xMarketplace


// import TicketNFT from 0x9c80d7e9707288a0
// import Marketplace from 0x9c80d7e9707288a0


transaction(tokensID: [UInt64]) {

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
        var i=0
    
      
        while i<tokensID.length{
              let token <- self.saleCollectionRef.withdraw(tokenID: tokensID[i])
              self.collectionRef.deposit(token: <-token)
              i=i+1
        }

        
    }
}   