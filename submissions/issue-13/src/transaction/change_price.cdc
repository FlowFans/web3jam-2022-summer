import TicketNFT from 0xProfile
import Marketplace from 0xMarket

// This transaction changes the price of a moment that a user has for sale

// Parameters:
//
// tokenID: the ID of the moment whose price is being changed
// newPrice: the new price of the moment

transaction(tokenID: UInt64, newPrice: UFix64) {

    // Local variable for the account's topshot sale collection
    let ticketnftSaleCollectionRef: &Marketplace.SaleCollection

    prepare(acct: AuthAccount) {

        // borrow a reference to the owner's sale collection
        self.ticketnftSaleCollectionRef = acct.borrow<&Marketplace.SaleCollection>(from: /storage/TicketNFTSaleCollection)
            ?? panic("Could not borrow from sale in storage")
    }

    execute {

        // Change the price of the moment
        self.ticketnftSaleCollectionRef.changePrice(tokenID: tokenID, newPrice: newPrice)
    }


}