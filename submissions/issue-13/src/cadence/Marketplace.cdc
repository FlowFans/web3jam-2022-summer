
import FungibleToken from 0x9a0766d93b6608b7

import NonFungibleToken from 0x631e88ae7f1d7c20

import TicketNFT from 0xa3c1282a571e9c9e


// import FungibleToken from "./FungibleToken.cdc"

// import NonFungibleToken from "./NonFungibleToken.cdc"

// import TicketNFT from "./TicketNFT.cdc"




pub contract Marketplace {

    // -----------------------------------------------------------------------
    // TicketNFT Market contract Event definitions
    // -----------------------------------------------------------------------

    // emitted when a TicketNFT TicketNFT is listed for sale
    pub event TicketNFTListed(id: UInt64, price: UFix64, seller: Address?)
    // emitted when the price of a listed TicketNFT has changed
    pub event TicketNFTPriceChanged(id: UInt64, newPrice: UFix64, seller: Address?)
    // emitted when a token is purchased from the market
    pub event TicketNFTPurchased(id: UInt64, price: UFix64, seller: Address?)
    // emitted when a TicketNFT has been withdrawn from the sale
    pub event TicketNFTWithdrawn(id: UInt64, owner: Address?)
    // emitted when the cut percentage of the sale has been changed by the owner
    pub event CutPercentageChanged(newPercent: UFix64, seller: Address?)



    pub let MarketplaceStoragePath: StoragePath

    /// StorefrontPublicPath
    /// The public location for a Storefront link.
    pub let MarketplacePublicPath: PublicPath


    //pub var orderMapping: {UInt64:Ordering}

    pub var ItemsPrice:{UInt64: UFix64}

    pub var nextOrderId: UInt64

    pub resource interface SalePublic {
        pub var cutPercentage: UFix64
        pub fun purchase(tokenID: UInt64, buyTokens: @FungibleToken.Vault): @TicketNFT.NFT {
            post {
                result.id == tokenID: "The ID of the withdrawn token must be the same as the requested ID"
            }
        }
        pub fun getPrice(tokenID: UInt64): UFix64?
        pub fun getIDs(): [UInt64]
        pub fun borrowTicketNFT(id: UInt64): &TicketNFT.NFT? {
            // If the result isn't nil, the id of the returned reference
            // should be the same as the argument to the function
            post {
                (result == nil) || (result?.id == id): 
                    "Cannot borrow TicketNFT reference: The ID of the returned reference is incorrect"
            }
        }
    }

    pub resource SaleCollection: SalePublic {

        // A collection of the TicketNFTs that the user has for sale
        access(self) var forSale: @TicketNFT.Collection

        // Dictionary of the low low prices for each NFT by ID
        access(self) var prices: {UInt64: UFix64}

        // The fungible token vault of the seller
        // so that when someone buys a token, the tokens are deposited
        // to this Vault
        access(self) var ownerCapability: Capability

        // The capability that is used for depositing 
        // the beneficiary's cut of every sale
        access(self) var beneficiaryCapability: Capability

        // The percentage that is taken from every purchase for the beneficiary
        // For example, if the percentage is 15%, cutPercentage = 0.15
        pub var cutPercentage: UFix64

        init (ownerCapability: Capability, beneficiaryCapability: Capability, cutPercentage: UFix64) {
            pre {
                // Check that both capabilities are for fungible token Vault receivers
                ownerCapability.borrow<&{FungibleToken.Receiver}>() != nil: 
                    "Owner's Receiver Capability is invalid!"
                beneficiaryCapability.borrow<&{FungibleToken.Receiver}>() != nil: 
                    "Beneficiary's Receiver Capability is invalid!" 
            }
            
            // create an empty collection to store the TicketNFTs that are for sale
            self.forSale <- TicketNFT.createEmptyCollection() as! @TicketNFT.Collection
            self.ownerCapability = ownerCapability
            self.beneficiaryCapability = beneficiaryCapability
            // prices are initially empty because there are no TicketNFTs for sale
            self.prices = {}
            self.cutPercentage = cutPercentage
        }

        // listForSale lists an NFT for sale in this sale collection
        // at the specified price
        //
        // Parameters: token: The NFT to be put up for sale
        //             price: The price of the NFT
        pub fun listForSale(token: @TicketNFT.NFT, price: UFix64) {
            pre {
                TicketNFT.middleOwner[self.owner!.address]==true : "Do not have permission to perform this operation"
            }
            
            // get the ID of the token
            let id = token.id
            Marketplace.ItemsPrice[id]=price
            // Set the token's price
            self.prices[token.id] = price

            // Deposit the token into the sale collection
            self.forSale.deposit(token: <-token)

            emit TicketNFTListed(id: id, price: price, seller: self.owner?.address)
        }

        // Withdraw removes a TicketNFT that was listed for sale
        // and clears its price
        //
        // Parameters: tokenID: the ID of the token to withdraw from the sale
        //
        // Returns: @TicketNFT.NFT: The nft that was withdrawn from the sale
        pub fun withdraw(tokenID: UInt64): @TicketNFT.NFT {

            // Remove and return the token.
            // Will revert if the token doesn't exist
            let token <- self.forSale.withdraw(withdrawID: tokenID) as! @TicketNFT.NFT

            // Remove the price from the prices dictionary
            self.prices.remove(key: tokenID)

            // Set prices to nil for the withdrawn ID
            self.prices[tokenID] = nil
            
            // Emit the event for withdrawing a TicketNFT from the Sale
            emit TicketNFTWithdrawn(id: token.id, owner: self.owner?.address)

            // Return the withdrawn token
            return <-token
        }

        // purchase lets a user send tokens to purchase an NFT that is for sale
        // the purchased NFT is returned to the transaction context that called it
        //
        // Parameters: tokenID: the ID of the NFT to purchase
        //             butTokens: the fungible tokens that are used to buy the NFT
        //
        // Returns: @TicketNFT.NFT: the purchased NFT
        pub fun purchase(tokenID: UInt64, buyTokens: @FungibleToken.Vault): @TicketNFT.NFT {
            pre {
                self.forSale.ownedNFTs[tokenID] != nil && self.prices[tokenID] != nil:
                    "No token matching this ID for sale!"           
                buyTokens.balance == (self.prices[tokenID] ?? UFix64(0)):
                    "Not enough tokens to buy the NFT!"
            }

            // Read the price for the token
            let price = self.prices[tokenID]!

            // Set the price for the token to nil
            self.prices[tokenID] = nil

            // Take the cut of the tokens that the beneficiary gets from the sent tokens
            let beneficiaryCut <- buyTokens.withdraw(amount: price*self.cutPercentage)

            // Deposit it into the beneficiary's Vault
            self.beneficiaryCapability.borrow<&{FungibleToken.Receiver}>()!
                .deposit(from: <-beneficiaryCut)
            
            // Deposit the remaining tokens into the owners vault
            self.ownerCapability.borrow<&{FungibleToken.Receiver}>()!
                .deposit(from: <-buyTokens)

            emit TicketNFTPurchased(id: tokenID, price: price, seller: self.owner?.address)
       
            return <-self.withdraw(tokenID: tokenID)
        }

        // changePrice changes the price of a token that is currently for sale
        //
        // Parameters: tokenID: The ID of the NFT's price that is changing
        //             newPrice: The new price for the NFT
        pub fun changePrice(tokenID: UInt64, newPrice: UFix64) {
            pre {
                self.prices[tokenID] != nil: "Cannot change the price for a token that is not for sale"
            }
            // Set the new price
            self.prices[tokenID] = newPrice

            emit TicketNFTPriceChanged(id: tokenID, newPrice: newPrice, seller: self.owner?.address)
        }

        // changePercentage changes the cut percentage of the tokens that are for sale
        //
        // Parameters: newPercent: The new cut percentage for the sale
        pub fun changePercentage( newPercent: UFix64) {
            pre {
                newPercent <= 1.0: "Cannot set cut percentage to greater than 100%"
            }
            self.cutPercentage = newPercent

            emit CutPercentageChanged(newPercent: newPercent, seller: self.owner?.address)
        }

        // changeOwnerReceiver updates the capability for the sellers fungible token Vault
        //
        // Parameters: newOwnerCapability: The new fungible token capability for the account 
        //                                 who received tokens for purchases
        pub fun changeOwnerReceiver(_ newOwnerCapability: Capability) {
            pre {
                newOwnerCapability.borrow<&{FungibleToken.Receiver}>() != nil: 
                    "Owner's Receiver Capability is invalid!"
            }
            self.ownerCapability = newOwnerCapability
        }

        // changeBeneficiaryReceiver updates the capability for the beneficiary of the cut of the sale
        //
        // Parameters: newBeneficiaryCapability the new capability for the beneficiary of the cut of the sale
        //
        pub fun changeBeneficiaryReceiver(_ newBeneficiaryCapability: Capability) {
            pre {
                newBeneficiaryCapability.borrow<&{FungibleToken.Receiver}>() != nil: 
                    "Beneficiary's Receiver Capability is invalid!" 
            }
            self.beneficiaryCapability = newBeneficiaryCapability
        }

        // getPrice returns the price of a specific token in the sale
        // 
        // Parameters: tokenID: The ID of the NFT whose price to get
        //
        // Returns: UFix64: The price of the token
        pub fun getPrice(tokenID: UInt64): UFix64? {
            return self.prices[tokenID]
        }

        // getIDs returns an array of token IDs that are for sale
        pub fun getIDs(): [UInt64] {
            return self.forSale.getIDs()
        }

        // borrowTicketNFT Returns a borrowed reference to a TicketNFT in the collection
        // so that the caller can read data from it
        //
        // Parameters: id: The ID of the TicketNFT to borrow a reference to
        //
        // Returns: &TicketNFT.NFT? Optional reference to a TicketNFT for sale 
        //                        so that the caller can read its data
        //
        pub fun borrowTicketNFT(id: UInt64): &TicketNFT.NFT? {
            let ref = self.forSale.borrowTicketNFT(id: id)
            return ref
        }

        // If the sale collection is destroyed, 
        // destroy the tokens that are for sale inside of it
        destroy() {
            destroy self.forSale
        }
    }


    pub fun createSaleCollection(ownerCapability: Capability, beneficiaryCapability: Capability, cutPercentage: UFix64): @SaleCollection {
        return <- create SaleCollection(ownerCapability: ownerCapability, beneficiaryCapability: beneficiaryCapability, cutPercentage: cutPercentage)
    }
    init(){
     self.nextOrderId=0
     self.ItemsPrice={}
     self.MarketplaceStoragePath = /storage/TicketNFTSaleCollection
     self.MarketplacePublicPath = /public/TicketNFTSaleCollection
  
    }
}
 