//0x357f8f9fb065d3e9


// import Marketplace from "../cadence/Marketplace.cdc"

import TicketNFT from 0xTicketNFT
transaction {

    //pub let  market_ex: &Marketplace.SalePublic

    prepare(signer: AuthAccount){
     
      let market <- signer.load<@TicketNFT.Collection>(from: /storage/TicketNFTCollection)

      //let capability=signer.getCapability<&Marketplace.SaleCollection>(/public/TicketNFTSaleCollection)
         //Marketplace.tokensForSale=[]
     
      destroy  market 

    }

    execute{
    
    }

}


