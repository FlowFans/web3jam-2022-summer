import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import OnlyBadges from "../../contracts/OnlyBadges.cdc"
import FungibleToken from "../../contracts/FungibleToken.cdc"
import FlowToken from "../../contracts/FlowToken.cdc"
import NFTStorefront from "../../contracts/NFTStorefront.cdc"

// This transction uses the NFTMinter resource to mint a new NFT.

transaction(recipient: Address, kind: UInt8, rarity: UInt8) {

    // local variable for storing the minter reference
    let minter: &OnlyBadges.NFTMinter
    let flowReceiver: Capability<&FlowToken.Vault{FungibleToken.Receiver}>
    let OnlyBadgesProvider: Capability<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
    let storefront: &NFTStorefront.Storefront

    prepare(signer: AuthAccount) {

        // borrow a reference to the NFTMinter resource in storage
        self.minter = signer.borrow<&OnlyBadges.NFTMinter>(from: OnlyBadges.MinterStoragePath)
            ?? panic("Could not borrow a reference to the NFT minter")

         // We need a provider capability, but one is not provided by default so we create one if needed.
        let OnlyBadgesCollectionProviderPrivatePath = /private/OnlyBadgesCollectionProviderV14

        self.flowReceiver = signer.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)!

        assert(self.flowReceiver.borrow() != nil, message: "Missing or mis-typed FLOW receiver")

        if !signer.getCapability<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(OnlyBadgesCollectionProviderPrivatePath)!.check() {
            signer.link<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(OnlyBadgesCollectionProviderPrivatePath, target: OnlyBadges.CollectionStoragePath)
        }

        self.OnlyBadgesProvider = signer.getCapability<&OnlyBadges.Collection{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(OnlyBadgesCollectionProviderPrivatePath)!

        assert(self.OnlyBadgesProvider.borrow() != nil, message: "Missing or mis-typed OnlyBadges.Collection provider")

        self.storefront = signer.borrow<&NFTStorefront.Storefront>(from: NFTStorefront.StorefrontStoragePath)
            ?? panic("Missing or mis-typed NFTStorefront Storefront")
    }

    execute {
        // get the public account object for the recipient
        let recipient = getAccount(recipient)

        // borrow the recipient's public NFT collection reference
        let receiver = recipient
            .getCapability(OnlyBadges.CollectionPublicPath)!
            .borrow<&{NonFungibleToken.CollectionPublic}>()
            ?? panic("Could not get receiver reference to the NFT Collection")

        // mint the NFT and deposit it to the recipient's collection
        let kindValue = OnlyBadges.Kind(rawValue: kind) ?? panic("invalid kind")
        let rarityValue = OnlyBadges.Rarity(rawValue: rarity) ?? panic("invalid rarity")

        // mint the NFT and deposit it to the recipient's collection
        self.minter.mintNFT(
            recipient: receiver,
            kind: kindValue,
            rarity: rarityValue,
        )

        let saleCut = NFTStorefront.SaleCut(
            receiver: self.flowReceiver,
            amount: OnlyBadges.getItemPrice(rarity: rarityValue)
        )
        
        self.storefront.createListing(
            nftProviderCapability: self.OnlyBadgesProvider,
            nftType: Type<@OnlyBadges.NFT>(),
            nftID: OnlyBadges.totalSupply - 1,
            salePaymentVaultType: Type<@FlowToken.Vault>(),
            saleCuts: [saleCut]
        )
    }
}
