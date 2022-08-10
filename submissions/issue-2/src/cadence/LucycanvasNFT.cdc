/*
*
*  This is an Example implementation of a Flow Non-Fungible Token
*  It is not part of the official standard but it assumed to be
*  similar to how many NFTs would implement the core functionality.
*
*  This contract does not implement any sophisticated classification
*  system for its NFTs. It defines a simple NFT with minimal metadata.
*
*/

import NonFungibleToken from 0x1d7e57aa55817448
import MetadataViews from 0x1d7e57aa55817448

pub contract ExampleNFT: NonFungibleToken {

    pub var totalSupply: UInt64

    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event Minted(id:UInt64,to:Address?)


    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let MinterStoragePath: StoragePath
    pub let MinterPublicPath: PublicPath //all can miner


    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
        pub let id: UInt64

        pub let name: String
        pub let description: String
        pub let thumbnail: String

        init(
            id: UInt64,
            name: String,
            description: String,
            thumbnail: String,
        ) {
            self.id = id
            self.name = name
            self.description = description
            self.thumbnail = thumbnail
        }

        pub fun getViews(): [Type] {
            return [
                Type<MetadataViews.Display>(),
                Type<MetadataViews.Editions>(),
                Type<MetadataViews.ExternalURL>(),
                Type<MetadataViews.NFTCollectionData>(),
                Type<MetadataViews.NFTCollectionDisplay>(),
                Type<MetadataViews.Serial>()
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
                case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL("https://Example-nft.onflow.org/".concat(self.id.toString()))
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: ExampleNFT.CollectionStoragePath,
                        publicPath: ExampleNFT.CollectionPublicPath,
                        providerPath: /private/ExampleNFTCollection,
                        publicCollection: Type<&ExampleNFT.Collection{ExampleNFT.ExampleNFTCollectionPublic}>(),
                        publicLinkedType: Type<&ExampleNFT.Collection{ExampleNFT.ExampleNFTCollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Receiver,MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&ExampleNFT.Collection{ExampleNFT.ExampleNFTCollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Provider,MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <-ExampleNFT.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let media = MetadataViews.Media(
                        file: MetadataViews.HTTPFile(
                            url: "https://assets.website-files.com/5f6294c0c7a8cdd643b1c820/5f6294c0c7a8cda55cb1c936_Flow_Wordmark.svg"
                        ),
                        mediaType: "image/svg+xml"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "The Example Collection",
                        description: "This collection is used as an Example to help you develop your next Flow NFT.",
                        externalURL: MetadataViews.ExternalURL("https://Example-nft.onflow.org"),
                        squareImage: media,
                        bannerImage: media,
                        socials: {
                            "twitter": MetadataViews.ExternalURL("https://twitter.com/flow_blockchain")
                        }
                    )
            }
            return nil
        }
    }

    pub resource interface ExampleNFTCollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowExampleNFT(id: UInt64): &ExampleNFT.NFT? {
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow ExampleNFT reference: the ID of the returned reference is incorrect"
            }
        }
    }

    pub resource Collection: ExampleNFTCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
        // dictionary of NFT conforming tokens
        // NFT is a resource type with an `UInt64` ID field
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}

        init () {
            self.ownedNFTs <- {}
        }

        // withdraw removes an NFT from the collection and moves it to the caller
        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing NFT")

            emit Withdraw(id: token.id, from: self.owner?.address)

            return <-token
        }

        // deposit takes a NFT and adds it to the collections dictionary
        // and adds the ID to the id array
        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @ExampleNFT.NFT

            let id: UInt64 = token.id

            // add the new token to the dictionary which removes the old one
            let oldToken <- self.ownedNFTs[id] <- token

            emit Deposit(id: id, to: self.owner?.address)

            destroy oldToken
        }

        // getIDs returns an array of the IDs that are in the collection
        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        // borrowNFT gets a reference to an NFT in the collection
        // so that the caller can read its metadata and call its methods
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }

        pub fun borrowExampleNFT(id: UInt64): &ExampleNFT.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &ExampleNFT.NFT
            }

            return nil
        }

        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let ExampleNFT = nft as! &ExampleNFT.NFT
            return ExampleNFT as &AnyResource{MetadataViews.Resolver}
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

    // public function that anyone can call to create a new empty collection
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }

    // public function that anyone can call to create a new miner
    //pub fun createMiner(): @NFTMinter {
    //    return <- create NFTMinter()
    //}


    // Resource that an admin or something similar would own to be
    // able to mint new NFTs
    //
    pub resource NFTMinter {
        //获得一个随机整数，tag作为种子随机要素，建议是用户的地址字符串
        pub fun betterRandom(tag:String):UInt256 {
          let rand_int = unsafeRandom()
          let rand_data: [UInt8] = rand_int.toBigEndianBytes()  // is `[73, 150, 2, 210]`

          let data = HashAlgorithm.KECCAK_256.hashWithTag(rand_data, tag:tag) //[UInt8]

          var data_int:UInt256 = 0
          var data_len = UInt256(data.length)

         //[UInt8] 转 UInt256
          for item in data {
            var ratio:UInt256 = 1

            var i:UInt256 = 0
            while (i<data_len-1) {
                ratio = ratio*256
                i = i + 1
            }
            data_int = data_int + UInt256(item)*ratio
            data_len = data_len - 1
          }
          return data_int
        }

         pub fun get_rand_nft(item_prob:{String:UFix64},tag:String): String {
            let prob_list = item_prob.values
            let nft_list = item_prob.keys

            //step 1, build area
            let ratio:UFix64 = 1000.0
            var nft_area_list:[UFix64] = [0.0]
            var prob_sum:UFix64 = 0.0

            for item in prob_list {
              prob_sum = prob_sum + item*ratio
              nft_area_list.append(prob_sum)
            }


            //step 2, get index
            //let big_int = unsafeRandom() //UInt64,can't run in playground, need testnet or emu
            //let big_int:UInt64 = 999923
            let big_int = self.betterRandom(tag:tag) //uInt256

            let base_mod = UInt256(ratio) //same to ratio
            let rand_index = UInt32(big_int % base_mod)


            var item_index = 0
            for item in nft_area_list {
              if 0.0 == item {  // 第一个不算
                continue
              }

              if UFix64(rand_index) < item {
                break
              }

              item_index = item_index + 1
            }

            let rand_nft = nft_list[item_index]
            return rand_nft
         }


        // mintNFT mints a new NFT with a new ID
        // and deposit it in the recipients collection using their collection reference
        pub fun mintNFT(
            recipient: &{NonFungibleToken.CollectionPublic},
            user_address:Address
        ) {

           let tag = user_address.toString() //random seeds
            //质量概率分布
           //let quality_prob = {"纸巾":0.5, "鼠标":0.3, "键盘":0.14,"iPad":0.05,"Macbook":0.01}
           let quality_prob = ITEM_PROB_DICT //需要替换的内容
           let quality = self.get_rand_nft(item_prob:quality_prob, tag:tag)


            // create a new NFT
            var newNFT <- create NFT(
                id: ExampleNFT.totalSupply,
                name: quality,
                description: "ExampleNFT",
                thumbnail: "http://flow.study/1.png",
            )

            // deposit it in the recipient's account using their reference
            recipient.deposit(token: <-newNFT)
            emit Minted(id:ExampleNFT.totalSupply,to:user_address)


            ExampleNFT.totalSupply = ExampleNFT.totalSupply + UInt64(1)
        }
    }

    init() {
        // Initialize the total supply
        self.totalSupply = 0

        // Set the named paths
        self.CollectionStoragePath = /storage/ExampleNFTCollection
        self.CollectionPublicPath = /public/ExampleNFTCollection
        self.MinterStoragePath = /storage/ExampleNFTMinter
        self.MinterPublicPath = /public/ExampleNFTMinter


        // Create a Collection resource and save it to storage
        let collection <- create Collection()
        self.account.save(<-collection, to: self.CollectionStoragePath)

        // create a public capability for the collection
        self.account.link<&ExampleNFT.Collection{NonFungibleToken.CollectionPublic, ExampleNFT.ExampleNFTCollectionPublic, MetadataViews.ResolverCollection}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )

        // Create a Minter resource and save it to storage
        let minter <- create NFTMinter()
        self.account.save(<-minter, to: self.MinterStoragePath)
        self.account.link<&ExampleNFT.NFTMinter>(self.MinterPublicPath, target:self.MinterStoragePath)

        emit ContractInitialized()
    }
}
