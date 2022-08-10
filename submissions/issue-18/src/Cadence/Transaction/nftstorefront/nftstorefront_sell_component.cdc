import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
import FungibleToken from 0xee82856bf20e2aa6
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import FlowToken from 0x0ae53cb6e3f42a79
import NFTStorefront from "../../contracts/NFTStorefront.cdc"

// testnet
// import FUSD from 0xe223d8a629e49c68
// import FungibleToken from 0x9a0766d93b6608b7
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20
// import NFTStorefront from 0x94b06cfca1d8a476
// import FlowToken from 0x7e60df042a9c0868


transaction(saleItemID: UInt64, saleItemPrice: UFix64) {
  let flowReceiver: Capability<&FlowToken.Vault{FungibleToken.Receiver}>
  //let fusdReceiver: Capability<&FUSD.Vault{FungibleToken.Receiver}>
  let componentNftProvider: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
  let storefront: &NFTStorefront.Storefront

  prepare(acct: AuthAccount) {
    // todo: for FUSD 
    // self.fusdReceiver = acct.getCapability<&FUSD.Vault{FungibleToken.Receiver}>(/public/fusdReceiver)
    // assert(self.fusdReceiver.borrow() != nil, message: "Missing or mis-typed FUSD receiver")
    self.flowReceiver = acct.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
    assert(self.flowReceiver.borrow() != nil, message: "Missing or mis-typed FlowToken receiver")

    self.componentNftProvider = acct.getCapability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(SoulMadeComponent.CollectionPrivatePath)!
    assert(self.componentNftProvider.borrow() != nil, message: "Missing or mis-typed  provider")

    self.storefront = acct.borrow<&NFTStorefront.Storefront>(from: NFTStorefront.StorefrontStoragePath)
        ?? panic("Missing or mis-typed NFTStorefront Storefront")
  }

  execute {
    let saleCut = NFTStorefront.SaleCut(
        receiver: self.flowReceiver,
        amount: saleItemPrice
    )
    self.storefront.createListing(
        nftProviderCapability: self.componentNftProvider,
        nftType: Type<@SoulMadeComponent.NFT>(),
        nftID: saleItemID,
        salePaymentVaultType: Type<@FlowToken.Vault>(),
        saleCuts: [saleCut]
    )
  }
}
