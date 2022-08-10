import TicketNFT from 0xTicketNFT
import NonFungibleToken from 0xNonFungibleToken
import Marketplace from 0xMarketplace
import FUSD from 0xFUSD
import FungibleToken  from 0xFungibleToken



transaction(sellerAddress: Address, tokenID: UInt64, purchaseAmount: UFix64) {
    let collectionRef: &TicketNFT.Collection
    let collectionRef: &TicketNFT.Collection
    let providerRef: &FUSD.Vault{FungibleToken.Provider}


    prepare(acct: AuthAccount) {

        if acct.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath) == nil {

            // create a new TopShot Collection
            let collection <- TicketNFT.createEmptyCollection() as! @TicketNFT.Collection

            // Put the new Collection in storage
            acct.save(<-collection, to: TicketNFT.CollectionStoragePath)

            // create a public capability for the collection
            acct.link<&{NonFungibleToken.CollectionPublic, TicketNFT.TicketNFTCollectionPublic}>(TicketNFT.CollectionPublicPath, target: TicketNFT.CollectionStoragePath)
        }

        self.collectionRef = acct.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath)
        ?? panic("Could not borrow reference to the signer's nft collection")

        let vaultRef = acct.borrow<&FUSD.Vault>(from: /storage/fusdVault)
         ?? panic("Could not borrow reference to the signer's vault")

        self.providerRef = acct.borrow<&FUSD.Vault{FungibleToken.Provider}>(from: /storage/fusdVault)!

    }

    execute {
          let tokens <- self.providerRef.withdraw(amount: purchaseAmount) as! @FUSD.Vault

          let seller = getAccount(sellerAddress)

        // borrow a public reference to the seller's sale collection
        //Marketplace.MarketplaceStoragePath
        let ticketSaleCollection = seller.getCapability(Marketplace.MarketplacePublicPath)
            .borrow<&{Marketplace.SalePublic}>()
            ?? panic("Could not borrow public sale reference")

        // purchase the moment
        let purchasedToken <- ticketSaleCollection.purchase(tokenID: tokenID, buyTokens: <-tokens)

        // deposit the purchased moment into the signer's collection
        self.collectionRef.deposit(token: <-purchasedToken)
    }


}

