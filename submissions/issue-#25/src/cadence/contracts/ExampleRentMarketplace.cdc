import NonFungibleToken from 0x631e88ae7f1d7c20
import MetadataViews from 0x631e88ae7f1d7c20
import ExampleNFTUser from 0xb096b656ab049551
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

pub contract ExampleRentMarketplace {

    // Event that is emitted when a new NFT is put up for sale
    pub event ForSale(id: UInt64, price: UFix64, expired: UInt64, owner: Address?)

    // Event that is emitted when the price of an NFT changes
    pub event PriceChanged(id: UInt64, newPrice: UFix64, owner: Address?)

    // Event that is emitted when a token is purchased
    pub event TokenPurchased(id: UInt64, price: UFix64, seller: Address?, buyer: Address?)

    // Event that is emitted when a seller withdraws their NFT from the sale
    pub event SaleCanceled(id: UInt64, seller: Address?)

    // Interface that users will publish for their Sale collection
    // that only exposes the methods that are supposed to be public
    //
    pub resource interface SalePublic {
        pub fun rent(tokenID: UInt64, recipient: Capability<&AnyResource{ExampleNFTUser.NFTUserCollectionPublic}>, buyTokens: @FungibleToken.Vault, expired: UInt64)
        pub fun idPrice(tokenID: UInt64): UFix64?
        pub fun getExpired(tokenID: UInt64): UInt64?
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

        access(self) var setExpired: {UInt64: UInt64}

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
            self.setExpired = {}
        }

        // cancelSale gives the owner the opportunity to cancel a sale in the collection
        pub fun cancelSale(tokenID: UInt64) {
            // remove the price
            self.prices.remove(key: tokenID)
            self.prices[tokenID] = nil
            self.setExpired.remove(key: tokenID)
            self.setExpired[tokenID] = nil

            // Nothing needs to be done with the actual token because it is already in the owner's collection
        }

        // listForSale lists an NFT for sale in this collection
        pub fun listForSale(tokenID: UInt64, price: UFix64, expired: UInt64) {
            pre {
                !ExampleNFTUser.getExpired(uuid: self.ownerCollection.borrow()!.borrowNFT(id: tokenID).uuid): "the nft is lending"
                expired > getCurrentBlock().height: "Time is illegal!"
            }
            self.prices[tokenID] = price
            self.setExpired[tokenID] = expired
            emit ForSale(id: tokenID, price: price, expired: expired, owner: self.owner?.address)
            // store the price in the price array
        }

        // changePrice changes the price of a token that is currently for sale
        pub fun changePrice(tokenID: UInt64, newPrice: UFix64) {
            self.prices[tokenID] = newPrice

            emit PriceChanged(id: tokenID, newPrice: newPrice, owner: self.owner?.address)
        }

        // purchase lets a user send tokens to purchase an NFT that is for sale
        pub fun rent(tokenID: UInt64, recipient: Capability<&AnyResource{ExampleNFTUser.NFTUserCollectionPublic}>, buyTokens: @FungibleToken.Vault,expired: UInt64) {
            pre {
                self.prices[tokenID] != nil:
                    "No token matching this ID for sale!"
                self.setExpired[tokenID]! >= expired:
                    "The nft is over the lend time!"
                buyTokens.balance >= self.prices[tokenID]! * UFix64(expired - getCurrentBlock().height) / 86400.0 :
                    "Not enough tokens to rent the NFT!"
                recipient.borrow != nil:
                    "Invalid NFTUser receiver capability!"
            }

            // get the value out of the optional
            let price = self.prices[tokenID]! * UFix64(expired - getCurrentBlock().height) / 86400.0

            self.prices[tokenID] = nil

            let vaultRef = self.ownerVault.borrow()
                ?? panic("Could not borrow reference to owner token vault")

            // deposit the purchasing tokens into the owners vault
            vaultRef.deposit(from: <-buyTokens)

            // borrow a reference to the object that the receiver capability links to
            // We can force-cast the result here because it has already been checked in the pre-conditions
            let receiverReference = recipient.borrow()!

            self.ownerCollection.borrow()!.deposit(token:<- ExampleNFTUser.createUserNFT(token:<- self.ownerCollection.borrow()!.withdraw(withdrawID: tokenID),expired:expired,recipient:recipient))
            // deposit the NFT into the buyers collection 将下面的token 使用我们createuser 返回的就OK了，然后传入时间

            //self.ownerCollection.borrow()!.deposit(token: <-(ExampleNFTUser.createUserNFT(token: <- (self.ownerCollection.borrow()!.withdraw(withdrawID: tokenID) as! @AnyResource{NonFungibleToken.INFT,MetadataViews.Resolver}),expired:expired,recipient:recipient) as! @NonFungibleToken.NFT))
        }

        // idPrice returns the price of a specific token in the sale
        pub fun idPrice(tokenID: UInt64): UFix64? {
            return self.prices[tokenID]
        }

        // getIDs returns an array of token IDs that are for sale
        pub fun getIDs(): [UInt64] {
            return self.prices.keys
        }

        pub fun getExpired(tokenID: UInt64): UInt64? {
            return self.setExpired[tokenID]
        }
    }

    // createCollection returns a new collection resource to the caller
    pub fun createSaleCollection(ownerCollection: Capability<&NonFungibleToken.Collection>, 
                                 ownerVault: Capability<&AnyResource{FungibleToken.Receiver}>): @SaleCollection {
        return <- create SaleCollection(ownerCollection: ownerCollection, ownerVault: ownerVault)
    }
}
