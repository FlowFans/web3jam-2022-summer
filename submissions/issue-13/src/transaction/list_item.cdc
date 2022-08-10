import TicketNFT from 0xProfile
import Marketplace from 0xMarket
import FUSD from 0xFUSD



transaction(ticketNFT: UInt64, price: UFix64) {

    let collectionRef: &TicketNFT.Collection
    let saleCollectionRef: &Marketplace.SaleCollection

    prepare(acct: AuthAccount) {

        // borrow a reference to the Top Shot Collection
        self.collectionRef = acct.borrow<&TicketNFT.Collection>(from: /storage/TicketNFTCollection)
            ?? panic("Could not borrow from MomentCollection in storage")

        // borrow a reference to the topshot Sale Collection
        self.saleCollectionRef = acct.borrow<&Marketplace.SaleCollection>(from: /storage/TicketNFTSaleCollection)
            ?? panic("Could not borrow from sale in storage")
    }

    execute {

        // withdraw the specified token from the collection
        let token <- self.collectionRef.withdraw(withdrawID: ticketNFT) as! @TicketNFT.NFT

        // List the specified moment for sale
        self.saleCollectionRef.listForSale(token: <-token, price: price)
    }
}

