// emulator
// import NonFungibleToken from "./NonFungibleToken.cdc"
// import SoulMadeComponent from "./SoulMadeComponent.cdc"
// import SoulMadeMain from "./SoulMadeMain.cdc"

// testnet
import NonFungibleToken from 0x631e88ae7f1d7c20
import SoulMadeComponent from 0x421c19b7dc122357
import SoulMadeMain from 0x421c19b7dc122357
import MetadataViews from 0x631e88ae7f1d7c20
import FlowToken from 0x7e60df042a9c0868
import FungibleToken from 0x9a0766d93b6608b7


// mainnet
// import NonFungibleToken from 0x1d7e57aa55817448
// import SoulMadeComponent from 0x543606e9393a64a6
// import SoulMadeMain from 0x543606e9393a64a6

pub contract SoulMadePack: NonFungibleToken {

    pub var totalSupply: UInt64

    access(self) var freeClaim : {Address : String}

    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)

    pub event SoulMadePackOpened(id: UInt64, packDetail: PackDetail, to: Address?)
    pub event SoulMadePackFreeClaim(id: UInt64, from: Address, series: String)

    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let CollectionPrivatePath: PrivatePath

    pub let CollectionFreeClaimStoragePath: StoragePath
    pub let CollectionFreeClaimPublicPath: PublicPath
    pub let CollectionFreeClaimPrivatePath: PrivatePath

    pub struct PackDetail{
        pub let id: UInt64
        pub let scarcity: String
        pub let series: String
        pub let ipfsHash: String

        init(id: UInt64,
                scarcity: String,
                series: String,
                ipfsHash: String){
            self.id = id
            self.scarcity = scarcity
            self.series = series
            self.ipfsHash = ipfsHash
        }
    }

    pub struct MainComponentNftIds{
      pub let mainNftIds: [UInt64]
      pub let componentNftIds: [UInt64]

      init(mainNftIds: [UInt64], componentNftIds: [UInt64]){
        self.mainNftIds = mainNftIds
        self.componentNftIds = componentNftIds
      }

    }

    pub resource interface PackPublic {
        pub let id: UInt64
        pub let packDetail: PackDetail
    }

    pub resource NFT: NonFungibleToken.INFT, PackPublic, MetadataViews.Resolver {
        pub let id: UInt64
        pub let packDetail: PackDetail

        pub var mainNft: @{UInt64: SoulMadeMain.NFT}
        pub var componentNft: @{UInt64: SoulMadeComponent.NFT}

        pub fun getMainComponentIds(): MainComponentNftIds {
          return MainComponentNftIds(mainNftIds: self.mainNft.keys, componentNftIds: self.componentNft.keys)
        }

        pub fun depositMain(mainNft: @SoulMadeMain.NFT) {
          var old <- self.mainNft[mainNft.id] <- mainNft
          destroy old
        }

        pub fun depositComponent(componentNft: @SoulMadeComponent.NFT) {
          var old <- self.componentNft[componentNft.id] <- componentNft
          destroy old
        }

        pub fun withdrawMain(mainNftId: UInt64): @SoulMadeMain.NFT? {
          return <- self.mainNft.remove(key: mainNftId)
        }

        pub fun withdrawComponent(componentNftId: UInt64): @SoulMadeComponent.NFT? {
          return <- self.componentNft.remove(key: componentNftId)
        }

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
                        name: "SoulMadepack",
                        description: "SoulMadePack Contains Main and Component NFTs",
                        thumbnail: MetadataViews.IPFSFile(
                            cid: self.packDetail.ipfsHash,
                            path: nil
                        )
                    )
                case Type<MetadataViews.Royalties>():
                    return MetadataViews.Royalties([
						MetadataViews.Royalty(
							recepient: getAccount(0x9a57dfe5c8ce609c).getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver),
							cut: 0.00, // 5% royalty on secondary sales
							description: "SoulMade Pack Royalties"
						)
					])
                case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL("https://soulmade.art")
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: SoulMadePack.CollectionStoragePath,
                        publicPath: SoulMadePack.CollectionPublicPath,
                        providerPath: /private/SoulMadePackCollection,
                        publicCollection: Type<&Collection{CollectionPublic}>(),
                        publicLinkedType: Type<&Collection{CollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Receiver, MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&Collection{CollectionPublic, NonFungibleToken.CollectionPublic, NonFungibleToken.Provider, MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <- SoulMadePack.createEmptyCollection()
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
                        name: "SoulMadeMain",
                        description: "SoulMade Main Collection",
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

        init(initID: UInt64, packDetail: PackDetail) {
            self.id = initID
            self.packDetail = packDetail
            self.mainNft <- {}
            self.componentNft <- {}
        }

        destroy() {
            destroy self.mainNft
            destroy self.componentNft
        }        
    }

    pub resource interface CollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowViewResolver(id: UInt64): &{MetadataViews.Resolver}
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowPack(id: UInt64): &{SoulMadePack.PackPublic}
    }

    pub resource interface CollectionFreeClaim {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun borrowViewResolver(id: UInt64): &{MetadataViews.Resolver}
        pub fun freeClaim(mainNftCollectionRef: &{SoulMadeMain.CollectionPublic}?, componentNftCollectionRef: &{SoulMadeComponent.CollectionPublic}?)
    }

    pub resource Collection: CollectionPublic, CollectionFreeClaim, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic,MetadataViews.ResolverCollection {
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}
        init () {
            self.ownedNFTs <- {}
        }

        pub fun openPackNft(pack: @SoulMadePack.NFT, mainNftCollectionRef: &{SoulMadeMain.CollectionPublic}?, componentNftCollectionRef: &{SoulMadeComponent.CollectionPublic}?) {
          
          emit SoulMadePackOpened(id: pack.id, packDetail: pack.packDetail , to: self.owner?.address)

          let mainComponentIds = pack.getMainComponentIds()

          let mainNftIds = mainComponentIds.mainNftIds
          let componentNftIds = mainComponentIds.componentNftIds

          if(mainNftIds.length > 0 && mainNftCollectionRef != nil){
            for mainNftId in mainNftIds{
              var nft <- pack.withdrawMain(mainNftId: mainNftId)! as @NonFungibleToken.NFT
              mainNftCollectionRef!.deposit(token: <- nft)
            }
          } else if mainNftIds.length > 0 && mainNftCollectionRef == nil {
            panic("reference is null")
          }

          if(componentNftIds.length > 0 && componentNftIds != nil){
            for componentNftId in componentNftIds{
              var nft <- pack.withdrawComponent(componentNftId: componentNftId)! as @NonFungibleToken.NFT
              componentNftCollectionRef!.deposit(token: <- nft)
            }
          } else if componentNftIds.length > 0 && componentNftCollectionRef == nil {
            panic("reference is null")
          }
  
          destroy pack
        }

      pub fun borrowViewResolver(id: UInt64): &{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let packNFT = nft as! &SoulMadePack.NFT
            return packNFT as &{MetadataViews.Resolver}
        }    

        pub fun openPackFromCollection(id: UInt64, mainNftCollectionRef: &{SoulMadeMain.CollectionPublic}?, componentNftCollectionRef: &{SoulMadeComponent.CollectionPublic}?) {     
          let pack <- self.withdraw(withdrawID: id) as! @SoulMadePack.NFT
          self.openPackNft(pack: <- pack, mainNftCollectionRef: mainNftCollectionRef, componentNftCollectionRef: componentNftCollectionRef)

        }

        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
          let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing Pack NFT")

          emit Withdraw(id: token.id, from: self.owner?.address)

          return <- token
        }

        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @SoulMadePack.NFT
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

        pub fun borrowPack(id: UInt64): &{SoulMadePack.PackPublic} {    
            pre {
                self.ownedNFTs[id] != nil: "Main NFT doesn't exist"
            }
            let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            return ref as! &SoulMadePack.NFT
        }

        pub fun freeClaim(mainNftCollectionRef: &{SoulMadeMain.CollectionPublic}?, componentNftCollectionRef: &{SoulMadeComponent.CollectionPublic}?) {
          pre {
            mainNftCollectionRef!.owner!.address == componentNftCollectionRef!.owner!.address: "Main and Component NFTs must be owned by the same user"
            SoulMadePack.checkClaimed(address: mainNftCollectionRef!.owner!.address) == false: "This address has already claimed"
            self.ownedNFTs.length > 0: "All have been claimed"
          }

          let token <- self.ownedNFTs.remove(key: self.ownedNFTs.keys[0]) ?? panic("Giveaway Pack is not enough")
          
          let pack <- token as! @SoulMadePack.NFT

          var series = pack.packDetail.series

          SoulMadePack.updateClaimDictionary(address: mainNftCollectionRef!.owner!.address, series: series)
          emit SoulMadePackFreeClaim(id: pack.id, from: componentNftCollectionRef!.owner!.address, series: series)

          self.openPackNft(pack: <- pack, mainNftCollectionRef: mainNftCollectionRef, componentNftCollectionRef: componentNftCollectionRef)
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }


    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }
    
    access(account) fun mintPack(scarcity: String, series: String, ipfsHash: String, mainNfts: @[SoulMadeMain.NFT], componentNfts: @[SoulMadeComponent.NFT]) : @NFT {

      let packDetail = PackDetail(
          id: SoulMadePack.totalSupply,
          scarcity: scarcity,
          series: series,
          ipfsHash: ipfsHash        
      )

      var pack <- create NFT(initID: SoulMadePack.totalSupply, packDetail: packDetail)
      SoulMadePack.totalSupply = SoulMadePack.totalSupply + UInt64(1)
      while mainNfts.length > 0{
        pack.depositMain(mainNft: <- mainNfts.removeFirst())
      }

      while componentNfts.length > 0{
        pack.depositComponent(componentNft: <- componentNfts.removeFirst())
      }

      destroy mainNfts
      destroy componentNfts
      return <- pack
    }

    pub fun checkClaimed(address: Address?): Bool {
      if self.freeClaim[address!] != nil {
        return true
      }
      return false
    }

    pub fun getFreeClaimDictionary() : {Address : String} {
      return self.freeClaim
    }

    pub fun getAllFreeClaimAddress() : [Address] {
      return self.freeClaim.keys
    }

    access(account) fun updateClaimDictionary(address:Address, series:String){
      self.freeClaim[address] = series
    }

    access(account) fun renewClaimDictionary(){
      self.freeClaim = {}
    }

    init() {
        self.totalSupply = 0
        self.freeClaim = {}

        self.CollectionPublicPath = /public/SoulMadePackCollection
        self.CollectionStoragePath = /storage/SoulMadePackCollection
        self.CollectionPrivatePath = /private/SoulMadePackCollection
                
        self.CollectionFreeClaimPublicPath = /public/SoulMadePackCollectionFreeClaim
        self.CollectionFreeClaimStoragePath = /storage/SoulMadePackCollectionFreeClaim
        self.CollectionFreeClaimPrivatePath = /private/SoulMadePackCollectionFreeClaim

        emit ContractInitialized()
    }
}