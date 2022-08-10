import NonFungibleToken from "./NonFungibleToken.cdc"
import MetadataViews from "./MetadataViews.cdc"

pub contract SinGirlsNFT: NonFungibleToken {

    pub var totalSupply: UInt64
    pub let totalNFT: UInt64

    // event
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event NFTMinted(id: UInt64)
    pub event StoryWritten(id: UInt64, Story: String)

    // Declare the NFT resource type
  pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
    pub let id: UInt64
    pub let name: String
    pub let description: String
    pub let thumbnail: String
    access(self) let royalties: [MetadataViews.Royalty]
    access(self) let metadata: {String: AnyStruct}
    pub var inspiration: UInt64
    pub var fame: UInt64
    pub var smoothness: UInt64
    pub var level: UInt64

    pub var StorySpace: {String: UInt64}

    init(_name: String, _description: String, _thumbnail: String, _royalty: [MetadataViews.Royalty], _metadata: {String: AnyStruct}) {
      self.id = self.uuid
      self.name = _name
      self.description = _description
      self.thumbnail = _thumbnail
      self.royalties = _royalty
      self.metadata = _metadata
      self.inspiration = 0
      self.fame = 0
      self.smoothness = 0
      self.level = 1
      self.StorySpace = {}
    }

    pub fun upgradeNFT(type: String){
      switch type{
        case "inspiration":
          self.inspiration = self.inspiration + 1
        case "fame":
          self.fame = self.fame + 1
        case "smoothness":
          self.smoothness = self.smoothness + 1
        case "level":
          self.level = self.level + 1
      }
    }

    pub fun WriteStory(Story: String){
        self.StorySpace.insert(key: Story, 0)
    }

    pub fun likeStory(story: String){
      pre {
        self.StorySpace.containsKey(story): "Story doesn't exist"
      }
      self.StorySpace[story] = self.StorySpace[story]! + 1
    }

    pub fun getViews(): [Type] {
      return [
        Type<MetadataViews.Display>(),
        Type<MetadataViews.Royalties>(),
        Type<MetadataViews.Editions>(),
        Type<MetadataViews.ExternalURL>(),
        Type<MetadataViews.NFTCollectionData>(),
        Type<MetadataViews.NFTCollectionDisplay>(),
        Type<MetadataViews.Serial>(),
        Type<MetadataViews.Traits>()
      ]
    }

    pub fun resolveView(_ view: Type): AnyStruct? {
      switch view {
        case Type<MetadataViews.Display>():
                    return MetadataViews.Display(
                        name: self.name,
                        description: self.description,
                        thumbnail: MetadataViews.HTTPFile(
                            url: self.thumbnail
                        )
                    )
        case Type<MetadataViews.Editions>():
                    // There is no max number of NFTs that can be minted from this contract
                    // so the max edition field value is set to nil
                    let editionInfo = MetadataViews.Edition(name: "Example NFT Edition", number: self.id, max: nil)
                    let editionList: [MetadataViews.Edition] = [editionInfo]
                    return MetadataViews.Editions(
                        editionList
                    )
        case Type<MetadataViews.Serial>():
                    return MetadataViews.Serial(
                        self.id
                    )
        case Type<MetadataViews.Royalties>():
                    return MetadataViews.Royalties(
                        self.royalties
                    )
        case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL("https://example-nft.onflow.org/".concat(self.id.toString()))
        case Type<MetadataViews.NFTCollectionDisplay>():
                    let media = MetadataViews.Media(
                        file: MetadataViews.HTTPFile(
                            url: "https://assets.website-files.com/5f6294c0c7a8cdd643b1c820/5f6294c0c7a8cda55cb1c936_Flow_Wordmark.svg" //ipfs
                        ),
                        mediaType: "image/svg+xml"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "The Example Collection",
                        description: "This collection is used as an example to help you develop your next Flow NFT.",
                        externalURL: MetadataViews.ExternalURL("https://example-nft.onflow.org"),
                        squareImage: media,
                        bannerImage: media,
                        socials: {
                            "twitter": MetadataViews.ExternalURL("https://twitter.com/flow_blockchain")
                        }
                    )
        case Type<MetadataViews.Traits>():
                    // exclude mintedTime and foo to show other uses of Traits
                    let excludedTraits = ["mintedTime", "foo"]
                    let traitsView = MetadataViews.dictToTraits(dict: self.metadata, excludedNames: excludedTraits)

                    // mintedTime is a unix timestamp, we should mark it with a displayType so platforms know how to show it.
                    let mintedTimeTrait = MetadataViews.Trait(name: "mintedTime", value: self.metadata["mintedTime"]!, displayType: "Date", rarity: nil)
                    traitsView.addTrait(mintedTimeTrait)

          // foo is a trait with its own rarity
                    let fooTraitRarity = MetadataViews.Rarity(score: 10.0, max: 100.0, description: "Common")
                    let fooTrait = MetadataViews.Trait(name: "foo", value: self.metadata["foo"], displayType: nil, rarity: fooTraitRarity)
                    traitsView.addTrait(fooTrait)

                    return traitsView

            }
            return nil
        }
  }


      pub resource interface CollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowAuthNFT(id: UInt64): &NFT
    }


    pub resource Collection: CollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic  {//, MetadataViews.ResolverCollection {
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}


        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing Main NFT")

            emit Withdraw(id: token.id, from: self.owner?.address)

            return <- token
        }

        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @SinGirlsNFT.NFT
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

        pub fun borrowAuthNFT(id: UInt64): &NFT {
            pre {
                self.ownedNFTs[id] != nil: "Main NFT doesn't exist"
            }
            let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            return ref as! &NFT
        }

        destroy() {
            destroy self.ownedNFTs
        }

        init () {
            self.ownedNFTs <- {}
        }
    }

    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }


  pub resource Minter {

    pub fun createNFT(
                _name: String,
                _description: String,
                _thumbnail: String,
                _royalty: [MetadataViews.Royalty],
                _metadata: {String: AnyStruct}
            ): @NFT {
      post {
        SinGirlsNFT.totalSupply <= SinGirlsNFT.totalNFT: "All NFT Minted"
      }
      SinGirlsNFT.totalSupply = SinGirlsNFT.totalSupply +1
      return <- create NFT(
                _name: _name,
                _description: _description,
                _thumbnail: _thumbnail,
                _royalty: _royalty,
                _metadata: _metadata,
            )
    }

    pub fun createMinter(): @Minter {
      return <- create Minter()
    }

  }


  init() {
    self.totalSupply = 0
    self.totalNFT = 6666
    emit ContractInitialized()
    self.account.save(<- create Minter(), to: /storage/Minter)
  }
}
