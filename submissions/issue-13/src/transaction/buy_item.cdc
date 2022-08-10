import FungibleToken from 0xFungibleToken
import TicketNFT from 0xProfile
import Marketplace from 0xMarket
import FUSD from 0xFUSD

transaction(sellerAddress: Address, tokenID: UInt64, purchaseAmount: UFix64) {
    let collectionRef: &TicketNFT.Collection

    let collectionRef: &TicketNFT.Collection
    let providerRef: &FUSD.Vault{FungibleToken.Provider}

    prepare(acct: AuthAccount) {


        self.collectionRef = acct.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath)
        ?? panic("Could not borrow reference to the signer's nft collecti")

        let vaultRef = acct.borrow<&FUSD.Vault>(from: /storage/fusdVault)
         ?? panic("Could not borrow reference to the signer's vault")
        self.providerRef = acct.borrow<&FUSD.Vault{FungibleToken.Provider}>(from: /storage/fusdVault)!

    }

    execute {
          let tokens <- self.providerRef.withdraw(amount: purchaseAmount) as! @FUSD.Vault

        let seller = getAccount(sellerAddress)

        // borrow a public reference to the seller's sale collection
        let ticketSaleCollection = seller.getCapability(/public/TicketNFTSaleCollection)
            .borrow<&{Marketplace.SalePublic}>()
            ?? panic("Could not borrow public sale reference")

        // purchase the moment
        let purchasedToken <- ticketSaleCollection.purchase(tokenID: tokenID, buyTokens: <-tokens)

        // deposit the purchased moment into the signer's collection
        self.collectionRef.deposit(token: <-purchasedToken)
    }


}