// acct.save(<- MyNFT.createEmptyCollection(), to: /storage/MyNFTCollection)
// acct.link<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}>(/public/MyNFTCollection, target: /storage/MyNFTCollection)
// acct.link<&MyNFT.Collection>(/private/MyNFTCollection, target: /storage/MyNFTCollection)

// let MyNFTCollection = acct.getCapability<&MyNFT.Collection>(/private/MyNFTCollection)
// let FlowTokenVault = acct.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
// acct.save(<- NFTMarketplace.createSaleCollection(MyNFTCollection: MyNFTCollection, FlowTokenVault: FlowTokenVault), to: /storage/MySaleCollection)
// acct.link<&NFTMarketplace.SaleCollection{NFTMarketplace.SaleCollectionPublic}>(/public/MySaleCollection, target: /storage/MySaleCollection)


// acct.save(<- NFTCharityMarket.createAuthorizeCollection(MyNFTCollection: MyNFTCollection, FlowTokenVault: FlowTokenVault), to: /storage/MyAuthorizeCollection)
// acct.link<&NFTCharityMarket.AuthorizeCollection{NFTCharityMarket.AuthorizeCollectionPublic}>(/public/MyAuthorizeCollection, target: /storage/MyAuthorizeCollection)

// acct.save(<- NFTCharityMarket.createEmptyFractionalNFTCollection(), to: /storage/MyFractionalNFTCollection)
// acct.link<&NFTCharityMarket.FractionalNFTCollection>(/public/MyFractionalNFTCollection, target: /storage/MyFractionalNFTCollection)


export const setupUserTx = `
import MyNFT from 0xf2011014fb9bee77
import NonFungibleToken from 0x631e88ae7f1d7c20
import FungibleToken from 0x9a0766d93b6608b7
import FlowToken from 0x7e60df042a9c0868
import NFTMarketplace from 0xf2011014fb9bee77
import NFTCharityMarket from 0xf2011014fb9bee77


transaction {
    prepare(acct: AuthAccount) {
      acct.save(<- MyNFT.createEmptyCollection(), to: /storage/MyNFTCollection)
      acct.link<&MyNFT.Collection{MyNFT.CollectionPublic, NonFungibleToken.CollectionPublic}>(/public/MyNFTCollection, target: /storage/MyNFTCollection)
      acct.link<&MyNFT.Collection>(/private/MyNFTCollection, target: /storage/MyNFTCollection)
      
      let MyNFTCollection = acct.getCapability<&MyNFT.Collection>(/private/MyNFTCollection)
      let FlowTokenVault = acct.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
      acct.save(<- NFTMarketplace.createSaleCollection(MyNFTCollection: MyNFTCollection, FlowTokenVault: FlowTokenVault), to: /storage/MySaleCollection)
      acct.link<&NFTMarketplace.SaleCollection{NFTMarketplace.SaleCollectionPublic}>(/public/MySaleCollection, target: /storage/MySaleCollection)
      
      
      acct.save(<- NFTCharityMarket.createAuthorizeCollection(MyNFTCollection: MyNFTCollection, FlowTokenVault: FlowTokenVault), to: /storage/MyAuthorizeCollection)
      acct.link<&NFTCharityMarket.AuthorizeCollection{NFTCharityMarket.AuthorizeCollectionPublic}>(/public/MyAuthorizeCollection, target: /storage/MyAuthorizeCollection)
      
      acct.save(<- NFTCharityMarket.createEmptyFractionalNFTCollection(), to: /storage/MyFractionalNFTCollection)
      acct.link<&NFTCharityMarket.FractionalNFTCollection>(/public/MyFractionalNFTCollection, target: /storage/MyFractionalNFTCollection)
      
    }
    execute {
      log("A user stored a Collection and a SaleCollection inside their account")
    }
  }

`