import Marketplace from 0xMarketplace

import TicketNFT from 0xTicketNFT

// import TicketNFT from 0xd1183642a19fd336

// import Marketplace from 0x1dd1316850f649ea


transaction(tokenID: UInt64) {

    let collectionRef: &TicketNFT.Collection
    let saleCollectionRef: &Marketplace.SaleCollection

    prepare(acct: AuthAccount) {
        self.collectionRef = acct.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath)
            ?? panic("Could not borrow from TicketNFT in storage")
        self.saleCollectionRef = acct.borrow<&Marketplace.SaleCollection>(from: Marketplace.MarketplaceStoragePath)
            ?? panic("Could not borrow from sale in storage")
    }

    execute {
    
        let token <- self.saleCollectionRef.withdraw(tokenID: tokenID)

        self.collectionRef.deposit(token: <-token)
    }
}   