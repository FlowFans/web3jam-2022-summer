// import NonFungibleToken from "./NonFungibleToken.cdc"
// import MetadataViews from "./MetadataViews.cdc"
// import ExampleNFT from "./ExampleNFT.cdc"
// import FungibleToken from "./FungibleToken.cdc"
import NonFungibleToken from 0x631e88ae7f1d7c20
import MetadataViews from 0x631e88ae7f1d7c20
import ExampleNFT from 0xb10db40892311e63
import FungibleToken from 0x9a0766d93b6608b7

// ExampleMarketplace.cdc
//
// The ExampleMarketplace contract is a very basic sample implementation of an NFT ExampleMarketplace on Flow.
//
// This contract allows users to put their NFTs up for sale. Other users
// can purchase these NFTs with fungible tokens.
//
// Learn more about marketplaces in this tutorial: https://docs.onflow.org/cadence/tutorial/06-marketplace-compose/
//
// This contract is a learning tool and is not meant to be used in production.
// See the NFTStorefront contract for a generic marketplace smart contract that 
// is used by many different projects on the Flow blockchain:
//
// https://github.com/onflow/nft-storefront

pub contract ExampleMarketplace {

    // Event that is emitted when a new NFT is put up for sale
    pub event ForSale(id: UInt64, price: UFix64, owner: Address?)

    // Event that is emitted when the price of an NFT changes
    pub event PriceChanged(id: UInt64, newPrice: UFix64, owner: Address?)

    // Event that is emitted when a token is purchased
    pub event TokenPurchased(id: UInt64, price: UFix64, seller: Address?, buyer: Address?)

    // Event that is emitted when a seller withdraws their NFT from the sale
    pub event SaleCanceled(id: UInt64, seller: Address?)

    pub let SaleStoragePath: StoragePath
    pub let SalePublicPath: PublicPath

    pub var totalIncome: UFix64

    access(self) let salesList: {Address: [UInt64]}

    pub fun list(): {Address: [UInt64]} {
        return self.salesList
    }

    // Interface that users will publish for their Sale collection
    // that only exposes the methods that are supposed to be public
    //
    pub resource interface SalePublic {
        pub fun purchase(tokenID: UInt64, recipient: Capability<&AnyResource{ExampleNFT.ExampleNFTCollectionPublic}>, buyTokens: @FungibleToken.Vault)
        pub fun idPrice(tokenID: UInt64): UFix64?
        pub fun getIDs(): [UInt64]
    }

    // SaleCollection
    //
    // NFT Collection object that allows a user to put their NFT up for sale
    // where others can send fungible tokens to purchase it
    //
    pub resource SaleCollection: SalePublic {

        /// A capability for the owner's collection
        access(self) var ownerCollection: Capability<&NonFungibleToken.Collection>

        // Dictionary of the prices for each NFT by ID
        access(self) var prices: {UInt64: UFix64}

        // The fungible token vault of the owner of this sale.
        // When someone buys a token, this resource can deposit
        // tokens into their account.
        access(account) let ownerVault: Capability<&AnyResource{FungibleToken.Receiver}>

        init (ownerCollection: Capability<&NonFungibleToken.Collection>, 
              ownerVault: Capability<&AnyResource{FungibleToken.Receiver}>) {

            pre {
                // Check that the owner's collection capability is correct
                ownerCollection.check(): 
                    "Owner's NFT Collection Capability is invalid!"

                // Check that the fungible token vault capability is correct
                ownerVault.check(): 
                    "Owner's Receiver Capability is invalid!"
            }
            self.ownerCollection = ownerCollection
            self.ownerVault = ownerVault
            self.prices = {}
        }

        // cancelSale gives the owner the opportunity to cancel a sale in the collection
        pub fun cancelSale(tokenID: UInt64) {
            // remove the price
            self.prices.remove(key: tokenID)
            self.prices[tokenID] = nil
            if self.prices.keys.length == 0 {
                ExampleMarketplace.salesList.remove(key: self.owner?.address!)
            }
            else {
                ExampleMarketplace.salesList[self.owner?.address!] = self.prices.keys
            }
            // Nothing needs to be done with the actual token because it is already in the owner's collection
        }

        // listForSale lists an NFT for sale in this collection
        pub fun listForSale(tokenID: UInt64, price: UFix64) {
            pre {
                self.ownerCollection.borrow()!.borrowNFT(id: tokenID).id == tokenID :
                    "NFT to be listed does not exist in the owner's collection"
            }
            // store the price in the price array
            self.prices[tokenID] = price
            ExampleMarketplace.salesList[self.owner?.address!] = self.prices.keys

            emit ForSale(id: tokenID, price: price, owner: self.owner?.address)
        }

        // changePrice changes the price of a token that is currently for sale
        pub fun changePrice(tokenID: UInt64, newPrice: UFix64) {
            self.prices[tokenID] = newPrice

            ExampleMarketplace.salesList[self.owner?.address!] = self.prices.keys
            emit PriceChanged(id: tokenID, newPrice: newPrice, owner: self.owner?.address)
        }

        // purchase lets a user send tokens to purchase an NFT that is for sale
        pub fun purchase(tokenID: UInt64, recipient: Capability<&AnyResource{ExampleNFT.ExampleNFTCollectionPublic}>, buyTokens: @FungibleToken.Vault) {
            pre {
                self.prices[tokenID] != nil:
                    "No token matching this ID for sale!"
                buyTokens.balance >= (self.prices[tokenID] ?? 0.0):
                    "Not enough tokens to by the NFT!"
                recipient.borrow != nil:
                    "Invalid NFT receiver capability!"
            }

            // get the value out of the optional
            let price = self.prices[tokenID]!

            self.prices[tokenID] = nil
            if self.prices.keys.length == 0 {
                ExampleMarketplace.salesList.remove(key: self.owner?.address!)
            }
            else {
                ExampleMarketplace.salesList[self.owner?.address!] = self.prices.keys
            }
            let vaultRef = self.ownerVault.borrow()
                ?? panic("Could not borrow reference to owner token vault")
            let fee <- buyTokens.withdraw(amount: price * ExampleNFT.saleFee)
            let accountVaultRef = ExampleMarketplace.account.getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver).borrow() ?? panic("no resource")
            accountVaultRef.deposit(from: <-fee)
            // deposit the purchasing tokens into the owners vault
            vaultRef.deposit(from: <-buyTokens)
            ExampleMarketplace.totalIncome = ExampleMarketplace.totalIncome + price * ExampleNFT.saleFee

            // borrow a reference to the object that the receiver capability links to
            // We can force-cast the result here because it has already been checked in the pre-conditions
            let receiverReference = recipient.borrow()!

            // deposit the NFT into the buyers collection
            receiverReference.deposit(token: <-self.ownerCollection.borrow()!.withdraw(withdrawID: tokenID))

            emit TokenPurchased(id: tokenID, price: price, seller: self.owner?.address, buyer: receiverReference.owner?.address)
        }

        // idPrice returns the price of a specific token in the sale
        pub fun idPrice(tokenID: UInt64): UFix64? {
            return self.prices[tokenID]
        }

        // getIDs returns an array of token IDs that are for sale
        pub fun getIDs(): [UInt64] {
            return self.prices.keys
        }
    }

    // createCollection returns a new collection resource to the caller
    pub fun createSaleCollection(ownerCollection: Capability<&ExampleNFT.Collection>, 
                                 ownerVault: Capability<&AnyResource{FungibleToken.Receiver}>): @SaleCollection {
        return <- create SaleCollection(ownerCollection: ownerCollection, ownerVault: ownerVault)
    }

    init() {
        self.SaleStoragePath = /storage/exampleMarketplace
        self.SalePublicPath = /public/exampleMarketplace
        self.salesList = {}
        self.totalIncome = 0.0
    }
}
