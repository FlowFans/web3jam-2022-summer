import Marketplace from 0xMarket

// This transaction creates a public sale collection capability that any user can interact with

// Parameters:
//
// tokenReceiverPath: token capability for the account who will receive tokens for purchase
// beneficiaryAccount: the Flow address of the account where a cut of the purchase will be sent
// cutPercentage: how much in percentage the beneficiary will receive from the sale

transaction(beneficiaryAccount: Address, cutPercentage: UFix64) {

    prepare(acct: AuthAccount) {
        if acct.borrow<&Marketplace.SaleCollection>(from: /storage/TicketNFTSaleCollection) == nil {
            let ownerCapability = acct.getCapability(/public/fusdReceiver)
            let beneficiaryCapability = getAccount(beneficiaryAccount).getCapability(/public/fusdReceiver)
            let collection <- Marketplace.createSaleCollection(ownerCapability: ownerCapability, beneficiaryCapability: beneficiaryCapability, cutPercentage: cutPercentage)

            //let collection <- Marketplace.createSaleCollection(ownerVault: receiver)
            acct.save(<-collection, to: /storage/TicketNFTSaleCollection)
            acct.link<&Marketplace.SaleCollection{Marketplace.SalePublic}>(/public/TicketNFTSaleCollection, target: /storage/TicketNFTSaleCollection)
            let capability=acct.getCapability<&Marketplace.SaleCollection>(/public/TicketNFTSaleCollection)
            Marketplace.tokensForSale.append(capability)
        }

    }
}