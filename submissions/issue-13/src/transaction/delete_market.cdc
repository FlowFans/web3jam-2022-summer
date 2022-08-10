// import Marketplace from "../cadence/Marketplace.cdc"

import Marketplace from 0x979e7e58e3094b89
transaction {

    //pub let  market_ex: &Marketplace.SalePublic

    prepare(signer: AuthAccount){
     
      let market <- signer.load<@Marketplace.SaleCollection>(from: /storage/TicketNFTSaleCollection)

      let capability=signer.getCapability<&Marketplace.SaleCollection>(/public/TicketNFTSaleCollection)
         Marketplace.tokensForSale.remove(at:2)
     
      destroy  market 

    }

    execute{
    
    }

}