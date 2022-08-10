import  Marketplace from 0xMarketplace
import FungibleToken from 0xFungibleToken


// import Marketplace from 0x979e7e58e3094b89

// This transaction creates a public sale collection capability 
//that any user can interact with

transaction() {
    prepare(acct: AuthAccount) {
        if acct.borrow<&Marketplace.SaleCollection>(from: /storage/TicketNFTSaleCollection) == nil {
            let ownerCapability = acct.getCapability(/public/flowTokenBalance)
            if ownerCapability.borrow<&{FungibleToken.Receiver}>() == nil{
                panic("xxxx")
            }
        }   
        
    }
}