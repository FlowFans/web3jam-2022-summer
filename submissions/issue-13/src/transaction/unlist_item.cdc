import TicketNFT from 0xProfile
import Marketplace from 0xMarket

// This transaction is for a user to stop a moment sale in their account
// by withdrawing that moment from their sale collection and depositing
// it into their normal moment collection

// Parameters
//
// tokenID: the ID of the moment whose sale is to be delisted

transaction(tokenID: UInt64) {

    let collectionRef: &TicketNFT.Collection
    let saleCollectionRef: &Marketplace.SaleCollection

    prepare(acct: AuthAccount) {

        self.collectionRef = acct.borrow<&TicketNFT.Collection>(from: /storage/TicketNFTCollection)
            ?? panic("Could not borrow from MomentCollection in storage")

        // borrow a reference to the owner's sale collection
        self.saleCollectionRef = acct.borrow<&Marketplace.SaleCollection>(from: /storage/TicketNFTSaleCollection)
            ?? panic("Could not borrow from sale in storage")
    }

    execute {

        let token <- self.saleCollectionRef.withdraw(tokenID: tokenID)

        self.collectionRef.deposit(token: <-token)
    }
}