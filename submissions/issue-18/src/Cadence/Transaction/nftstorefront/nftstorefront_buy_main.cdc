import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import FungibleToken from 0xee82856bf20e2aa6
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import FUSD from 0xf8d6e0586b0a20c7
import NFTStorefront from 0xf8d6e0586b0a20c7

transaction(listingResourceID: UInt64) { 
    let paymentVault: @FungibleToken.Vault
    let exampleNFTCollection: &SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}
    let storefront: &NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}
    let listing: &NFTStorefront.Listing{NFTStorefront.ListingPublic}

    prepare(acct: AuthAccount) {
        self.storefront = getAccount(0xf8d6e0586b0a20c7)
            .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
                NFTStorefront.StorefrontPublicPath
            )!
            .borrow()
            ?? panic("Could not borrow Storefront from provided address")

        self.listing = self.storefront.borrowListing(listingResourceID: listingResourceID)
                    ?? panic("No Offer with that ID in Storefront")
        let price = self.listing.getDetails().salePrice

        let fusdVault = acct.borrow<&FUSD.Vault>(from: /storage/fusdVault)
            ?? panic("Cannot borrow FUSD vault from acct storage")
        self.paymentVault <- fusdVault.withdraw(amount: price)

        self.exampleNFTCollection = acct.borrow<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(
            from: SoulMadeMain.CollectionStoragePath
        ) ?? panic("Cannot borrow NFT collection receiver from account")
    }

    execute {
        let item <- self.listing.purchase(
            payment: <-self.paymentVault
        )

        self.exampleNFTCollection.deposit(token: <-item)

        /* //-
        error: Execution failed:
        computation limited exceeded: 100
        */
        // Be kind and recycle
        //self.storefront.cleanup(listingResourceID: listingResourceID)
    }

    //- Post to check item is in collection?
}
