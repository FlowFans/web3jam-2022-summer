import FungibleToken from "../../contracts/standard/FungibleToken.cdc"
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import FlowToken from "../../contracts/standard/FlowToken.cdc"
import NFTStorefront from "../../contracts/NFTStorefront.cdc"
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"


// testnet
// import FUSD from 0xe223d8a629e49c68
// import FungibleToken from 0x9a0766d93b6608b7
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20
// import NFTStorefront from 0x94b06cfca1d8a476


transaction(saleItemID: UInt64, saleItemPrice: UFix64) {
    let flowReceiver: Capability<&FlowToken.Vault{FungibleToken.Receiver}>
    let componentNftProvider: Capability<&SoulMadeComponent.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
    let storefront: &NFTStorefront.Storefront

    prepare(acct: AuthAccount) {
        // We need a provider capability, but one is not provided by default so we create one if needed.
        let componentNftCollectionProviderPrivatePath = SoulMadeComponent.CollectionPrivatePath

        self.flowReceiver = acct.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)!
        assert(self.flowReceiver.borrow() != nil, message: "Missing or mis-typed FlowToken receiver")

        if !acct.getCapability<&SoulMadeComponent.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(componentNftCollectionProviderPrivatePath)!.check() {
            acct.link<&SoulMadeComponent.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(componentNftCollectionProviderPrivatePath, target: /storage/NFTCollection)
        }

        self.componentNftProvider = acct.getCapability<&SoulMadeComponent.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(componentNftCollectionProviderPrivatePath)!
        assert(self.componentNftProvider.borrow() != nil, message: "Missing or mis-typed SoulMadeComponent.Collection provider")

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
