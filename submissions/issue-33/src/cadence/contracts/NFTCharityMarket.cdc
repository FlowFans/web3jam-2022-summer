import MyNFT from 0xf2011014fb9bee77
import NonFungibleToken from 0x631e88ae7f1d7c20
import FungibleToken from 0x9a0766d93b6608b7
import FlowToken from 0x7e60df042a9c0868

pub contract NFTCharityMarket {

  pub event FractionalDeposit(id: UInt64, to: Address?)


  pub struct SaleItem {
    pub let price: UFix64
    
    pub let nftRef: &MyNFT.NFT
    
    init(_price: UFix64, _nftRef: &MyNFT.NFT) {
      self.price = _price
      self.nftRef = _nftRef
    }
  }

  pub resource FractionalNFT {
    pub let id: UInt64
    pub let authorizationNFT: Capability<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}>

    init(_id: UInt64, _authorizationNFT:Capability<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}> ) {
      self.id = _id
      self.authorizationNFT = _authorizationNFT
    }
  }

  pub resource FractionalNFTCollection {
    pub var ownedFractionalNFTs: @{UInt64: FractionalNFT}




    init() {
      self.ownedFractionalNFTs <- {}  
    }

    pub fun deposit(token: @FractionalNFT) {
      let nft <- token as! @FractionalNFT
      emit FractionalDeposit(id: nft.id, to: self.owner?.address)
      self.ownedFractionalNFTs[nft.id] <-! nft
    }

    pub fun getIDs(): [UInt64] {
      return self.ownedFractionalNFTs.keys
    }

    pub fun borrowEntireNFT(id: UInt64): &FractionalNFT {
      let reference = &self.ownedFractionalNFTs[id] as auth &FractionalNFT?
      return reference as! &FractionalNFT
    }

    destroy() {
      destroy self.ownedFractionalNFTs
    }
  }

  pub resource interface AuthorizeCollectionPublic {
    pub fun getAuthorizePrice(id: UInt64): UFix64
    pub fun getAuthorizeIDs(): [UInt64]
    pub fun authorize(id: UInt64, 
                      recipientCollection: &FractionalNFTCollection, 
                      payment: @FlowToken.Vault,
                      authorizationNFT: Capability<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}> )
  }

  pub resource AuthorizeCollection : AuthorizeCollectionPublic {
    // maps the id of the NFT --> the price of that NFT
    pub var forAuthorize: {UInt64 : UFix64}
    pub let MyNFTCollection: Capability<&MyNFT.Collection>
    pub let FlowTokenVault: Capability<&FlowToken.Vault{FungibleToken.Receiver}>

    pub fun listForAuthorize(id: UInt64, price: UFix64)  {
      pre {
        price > 0.0: "The price is less than 0.0"
        self.MyNFTCollection.borrow()!.getIDs().contains(id): "This SaleCollection owner does not have this NFT"
        //!self.getIDs().contains(id): "You cannot authorize NFT for sell"
      }
      
      self.forAuthorize[id] = price
    }

    pub fun unlistFromAuthorize(id: UInt64)  {
      self.forAuthorize.remove(key: id)
    }

    pub fun authorize(id: UInt64, 
                      recipientCollection: &FractionalNFTCollection, 
                      payment: @FlowToken.Vault,
                      authorizationNFT: Capability<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}> ) {
      pre {
        payment.balance == self.forAuthorize[id]: "The payment is not equal to the price of the NFT"
      }

      let fractionalNFT <- create FractionalNFT(_id: id, _authorizationNFT: authorizationNFT)
      recipientCollection.deposit(token: <- fractionalNFT)
      self.FlowTokenVault.borrow()!.deposit(from: <- payment)
    }


    init(_MyNFTCollection: Capability<&MyNFT.Collection>, _FlowTokenVault: Capability<&FlowToken.Vault{FungibleToken.Receiver}>) {
      self.forAuthorize = {}
      self.MyNFTCollection = _MyNFTCollection
      self.FlowTokenVault = _FlowTokenVault    
    }

    pub fun getAuthorizeIDs(): [UInt64] {
      return self.forAuthorize.keys
    }

    pub fun getAuthorizePrice(id: UInt64): UFix64 {
      return self.forAuthorize[id]!
    }

  }

  pub fun createEmptyFractionalNFTCollection(): @FractionalNFTCollection {
    return <- create FractionalNFTCollection()
  }  

  pub fun createAuthorizeCollection(MyNFTCollection: Capability<&MyNFT.Collection>, FlowTokenVault: Capability<&FlowToken.Vault{FungibleToken.Receiver}>): @AuthorizeCollection {
    return <- create AuthorizeCollection(_MyNFTCollection: MyNFTCollection, _FlowTokenVault: FlowTokenVault)
  }

  init() {

  }

}

