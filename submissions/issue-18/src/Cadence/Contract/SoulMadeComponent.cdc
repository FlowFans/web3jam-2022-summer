// emulator
// import NonFungibleToken from "./NonFungibleToken.cdc"
// import MetadataViews from "./MetadataViews.cdc"

// testnet
import NonFungibleToken from 0x631e88ae7f1d7c20
import MetadataViews from 0x631e88ae7f1d7c20
import FlowToken from 0x7e60df042a9c0868
import FungibleToken from 0x9a0766d93b6608b7

// mainnet
// import NonFungibleToken from 0x1d7e57aa55817448
// import MetadataViews from 0x1d7e57aa55817448


pub contract SoulMadeComponent: NonFungibleToken {

    pub var totalSupply: UInt64
    
    pub event ContractInitialized()

    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)

    pub event SoulMadeComponentCollectionCreated()
    pub event SoulMadeComponentCreated(componentDetail: ComponentDetail)

    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let CollectionPrivatePath: PrivatePath

    pub struct ComponentDetail{
        pub let id: UInt64
        pub let series: String
        pub let name: String
        pub let description: String
        pub let category: String
        pub let layer: UInt64
        pub let edition: UInt64
        pub let maxEdition: UInt64
        pub let ipfsHash: String

        init(id: UInt64,
            series: String,
            name: String,
            description: String,
            category: String,
            layer: UInt64,
            edition: UInt64,
            maxEdition: UInt64,
            ipfsHash: String) {
                self.id=id
                self.series=series
                self.name=name
                self.description=description
                self.category=category
                self.layer=layer
                self.edition=edition
                self.maxEdition=maxEdition
                self.ipfsHash=ipfsHash
        }
    }

    pub resource interface ComponentPublic {
        pub let id: UInt64
        pub let componentDetail: ComponentDetail
    }

    pub resource NFT: NonFungibleToken.INFT, ComponentPublic, MetadataViews.Resolver{
        pub let id: UInt64
        pub let componentDetail: ComponentDetail

        pub fun getViews(): [Type] {
            return [
                Type<MetadataViews.Display>(),
                Type<MetadataViews.Royalties>(),
                Type<MetadataViews.ExternalURL>(),
                Type<MetadataViews.NFTCollectionData>(),
                Type<MetadataViews.NFTCollectionDisplay>()
            ]
        }

        pub fun resolveView(_ view: Type): AnyStruct? {
            switch view {
                case Type<MetadataViews.Display>():
                    return MetadataViews.Display(
                        name: self.componentDetail.name,
                        description: self.componentDetail.description,
                        thumbnail: MetadataViews.IPFSFile(
                            cid: self.componentDetail.ipfsHash,
                            path: nil
                        )
                    )
                case Type<MetadataViews.Royalties>():
                    return MetadataViews.Royalties([
						MetadataViews.Royalty(
							recepient: getAccount(0x9a57dfe5c8ce609c).getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver),
							cut: 0.00, // 5% royalty on secondary sales
							description: "SoulMade Component Royalties"
						)
					])
                case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL("https://soulmade.art")
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: SoulMadeComponent.CollectionStoragePath,
                        publicPath: SoulMadeComponent.CollectionPublicPath,
                        providerPath: /private/SoulMadeComponentCollection,
                        publicCollection: Type<&Collection{CollectionPublic}>(),
                        publicLinkedType: Type<&Collection{CollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Receiver, MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&Collection{CollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Provider, MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <- SoulMadeComponent.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let squareMedia = MetadataViews.Media(
                        file: MetadataViews.HTTPFile(
                           url: "https://i.imgur.com/Xlfqj5g.png"
                        ),
                        mediaType: "image"
                    )
                    let bannerMedia = MetadataViews.Media(
                        file: MetadataViews.HTTPFile(
                            url: "https://i.imgur.com/HWXhRXt.png"
                        ),
                        mediaType: "image"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "SoulMadeComponent",
                        description: "SoulMade Component Collection",
                        externalURL: MetadataViews.ExternalURL("https://soulmade.art"),
                        squareImage: squareMedia,
                        bannerImage: bannerMedia,
                        socials: {
                            "twitter": MetadataViews.ExternalURL("https://twitter.com/soulmade_nft"),
                            "discord": MetadataViews.ExternalURL("https://discord.com/invite/xtqqXCKW9B")
                        }
                    )
            }

            return nil
        }

        init(id: UInt64,
            componentDetail: ComponentDetail) {
                self.id=id
                self.componentDetail=componentDetail
        }
    }

    pub resource interface CollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowViewResolver(id: UInt64): &{MetadataViews.Resolver}
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowComponent(id: UInt64): &{SoulMadeComponent.ComponentPublic}
    }


    pub resource Collection: CollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}

        init () {
            self.ownedNFTs <- {}
        }

        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing Component NFT")
            emit Withdraw(id: token.id, from: self.owner?.address)
            return <- token
        }

        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @SoulMadeComponent.NFT
            let id: UInt64 = token.id
            let oldToken <- self.ownedNFTs[id] <- token
            emit Deposit(id: id, to: self.owner?.address)
            destroy oldToken
        }

        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }

        pub fun borrowComponent(id: UInt64): &{SoulMadeComponent.ComponentPublic} {
            pre {
                self.ownedNFTs[id] != nil: "Component NFT doesn't exist"
            }
            let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            return ref as! &SoulMadeComponent.NFT
        }

        pub fun borrowViewResolver(id: UInt64): &{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let componentNFT = nft as! &SoulMadeComponent.NFT
            return componentNFT as &{MetadataViews.Resolver}
        }        



        destroy() {
            destroy self.ownedNFTs
        }
    }

    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        emit SoulMadeComponentCollectionCreated()
        return <- create Collection()
    }

    access(account) fun makeEdition(series: String,
                                    name: String,
                                    description: String,
                                    category: String,
                                    layer: UInt64,
                                    currentEdition: UInt64,
                                    maxEdition: UInt64,
                                    ipfsHash: String) : @NFT {
        let componentDetail = ComponentDetail(
            id: SoulMadeComponent.totalSupply,
            series: series,
            name: name,
            description: description,
            category: category,
            layer: layer,
            edition: currentEdition,
            maxEdition: maxEdition,
            ipfsHash: ipfsHash            
        )

        var newNFT <- create NFT(
            id: SoulMadeComponent.totalSupply,
            componentDetail: componentDetail
        )

        emit SoulMadeComponentCreated(componentDetail: componentDetail)
        
        SoulMadeComponent.totalSupply = SoulMadeComponent.totalSupply + UInt64(1)

        return <- newNFT
    }

    init() {
        self.totalSupply = 0
        
        self.CollectionPublicPath = /public/SoulMadeComponentCollection
        self.CollectionStoragePath = /storage/SoulMadeComponentCollection
        self.CollectionPrivatePath = /private/SoulMadeComponentCollection

        emit ContractInitialized()
    }
}