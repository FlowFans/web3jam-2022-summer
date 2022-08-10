// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadePack from "../../contracts/SoulMadePack.cdc"
// import SoulMade from "../../contracts/SoulMade.cdc"
// import FungibleToken from 0xee82856bf20e2aa6
// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import FlowToken from 0x0ae53cb6e3f42a79
// import NFTStorefront from "../../contracts/NFTStorefront.cdc"

// testnet
import FUSD from 0xe223d8a629e49c68
import FungibleToken from 0x9a0766d93b6608b7
import SoulMadeMain from 0x76b2527585e45db4
import SoulMade from 0x76b2527585e45db4
import SoulMadeComponent from 0x76b2527585e45db4
import SoulMadePack from 0x76b2527585e45db4
import NonFungibleToken from 0x631e88ae7f1d7c20
import NFTStorefront from 0x94b06cfca1d8a476
import FlowToken from 0x7e60df042a9c0868

transaction(series: String) {
    let paymentVault: @FungibleToken.Vault
    let mainNftCollection: &{NonFungibleToken.Receiver}
    let componentNftCollection: &{NonFungibleToken.Receiver}
    let packCollection : &{NonFungibleToken.Receiver}
    let storefront: &NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}
    let listing: &NFTStorefront.Listing{NFTStorefront.ListingPublic}

    prepare(acct: AuthAccount) {
        // set up account
        if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
            let collection <- SoulMadeMain.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeMain.CollectionStoragePath)
            acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
            // todo: double check if we need this PrivatePath at all. I remeber we actually have used it somewhere.
            acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
        }

        if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
            let collection <- SoulMadeComponent.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
        }

        // todo: actually, the uers do not need pack collection, as the pack will automatically be opened
        if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
            let collection <- SoulMadePack.createEmptyCollection()
            acct.save(<-collection, to: SoulMadePack.CollectionStoragePath)
            acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
            acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
        }
        
        // let platformAddress: Address = 0xf8d6e0586b0a20c7
        let platformAddress: Address = 0x76b2527585e45db4
        self.storefront = getAccount(platformAddress)
            .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath).borrow() ?? panic("Could not borrow Storefront from provided address")

        // todo: there probably should be an force operator
        var packListings = SoulMade.getPackListingIdsPerSeries(address: platformAddress)[series]!
        var randomIndex = unsafeRandom() % UInt64(packListings.length)
        var listingResourceID = packListings[randomIndex]

        self.listing = self.storefront.borrowListing(listingResourceID: listingResourceID) ?? panic("No Offer with that ID in Storefront")
            
        let price = self.listing.getDetails().salePrice
        let mainFlowVault = acct.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)
            ?? panic("Cannot borrow FlowToken vault from acct storage")
        self.paymentVault <- mainFlowVault.withdraw(amount: price)

        // todo: remember to update all these panic to make it more explicit
        self.mainNftCollection = acct.borrow<&{NonFungibleToken.Receiver}>(from: SoulMadeMain.CollectionStoragePath) ?? panic("Cannot borrow Main NFT collection receiver from account")
        self.componentNftCollection = acct.borrow<&{NonFungibleToken.Receiver}>(from: SoulMadeComponent.CollectionStoragePath) ?? panic("Cannot borrow Component NFT collection receiver from account")
        self.packCollection = acct.borrow<&{NonFungibleToken.Receiver}>(from: SoulMadePack.CollectionStoragePath) ?? panic("Cannot borrow Pack NFT collection receiver from account")
    }

    execute {
        let pack <- self.listing.purchase(payment: <-self.paymentVault) as! @SoulMadePack.NFT
        self.packCollection.deposit(token : <- pack)
    }
}
