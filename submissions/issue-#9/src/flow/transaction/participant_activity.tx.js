//参加活动
export const PARTICIPANT_ACTIVITY = `
import PioneerNFTs from 0xPioneerNFT
import NonFungibleToken from 0xNonFungibleToken
import PioneerFlowToken from 0xPioneerNFT
import FungibleToken from 0xFungibleToken
import PioneerMarketplace from 0xPioneerNFT

transaction(listingResourceID: UInt64, storefrontAddress: Address) {

    let paymentVault: @FungibleToken.Vault
    let storefront: &PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}
    let saleOffer: &PioneerMarketplace.Listing{PioneerMarketplace.ListingPublic}

    prepare(account: AuthAccount) {
        self.storefront = getAccount(storefrontAddress)
            .getCapability<&PioneerMarketplace.Storefront{PioneerMarketplace.StorefrontPublic}>(
                PioneerMarketplace.StorefrontPublicPath
            )!
            .borrow()
            ?? panic("Cannot borrow Storefront from provided address")

        self.saleOffer = self.storefront.borrowListing(listingResourceID: listingResourceID)
            ?? panic("No offer with that ID in Storefront")

        let price = self.saleOffer.getDetails().salePrice!

        let mainflowTokenVault = account.borrow<&PioneerFlowToken.Vault>(from: /storage/flowTokenVault)
            ?? panic("Cannot borrow flow vault from account storage")

        self.paymentVault <- mainflowTokenVault.withdraw(amount: price)

        if account.borrow<&PioneerNFTs.Collection>(from: PioneerNFTs.CollectionStoragePath) == nil {

          // create a new TopShot Collection
          let collection <- PioneerNFTs.createEmptyCollection() as! @PioneerNFTs.Collection

          // Put the new Collection in storage
          account.save(<-collection, to: PioneerNFTs.CollectionStoragePath)

          // create a public capability for the collection
          account.link<&{NonFungibleToken.CollectionPublic, PioneerNFTs.PioneerNFTCollectionPublic}>(PioneerNFTs.CollectionPublicPath, target: PioneerNFTs.CollectionStoragePath)
      }
    }

    execute {
        let admin = getAccount(storefrontAddress)
        if admin.getCapability<&PioneerFlowToken.Vault{FungibleToken.Provider,FungibleToken.Receiver}>(/public/flowTokenReceiver) == nil {

            // Create a new empty .Storefront
            let flowToken <- PioneerFlowToken.createEmptyVault() as! @FungibleToken.Vault
            
            // save it to the account
            admin.save(<-flowToken, to: PioneerFlowToken.flowTokenVaultStoragePath)

            // create a public capability for the .Storefront
            admin.link<&FlowToken.Vault{FungibleToken.Provider,FungibleToken.Receiver}>(PioneerFlowToken.flowTokenVaultPublicPath, target: PioneerFlowToken.flowTokenVaultStoragePath)
        }
        let storeFrontTokenVault =  admin.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(PioneerFlowToken.flowTokenVaultPublicPath).borrow()?? panic("Could not borrow a reference to the receiver")
        storeFrontTokenVault.deposit(from: <-self.paymentVault)
        log("Transfer succeeded!")

    }
}
`
