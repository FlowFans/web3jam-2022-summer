/* 
*
*  This is an example implementation of a Flow Non-Fungible Token
*  It is not part of the official standard but it assumed to be
*  similar to how many NFTs would implement the core functionality.
*
*  This contract does not implement any sophisticated classification
*  system for its NFTs. It defines a simple NFT with minimal metadata.
*   
*/

import NonFungibleToken from "./standard/NonFungibleToken.cdc"
import MetadataViews from "./standard/MetadataViews.cdc"
import OverluError from "./OverluError.cdc"
import OverluConfig from "./OverluConfig.cdc"
import OverluDNA from "./OverluDNA.cdc"

pub contract OverluModel: NonFungibleToken {


    /**    ___  ____ ___ _  _ ____
       *   |__] |__|  |  |__| [__
        *  |    |  |  |  |  | ___]
         *************************/


    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let MinterStoragePath: StoragePath


    /**    ____ _  _ ____ _  _ ___ ____
       *   |___ |  | |___ |\ |  |  [__
        *  |___  \/  |___ | \|  |  ___]
         ******************************/

    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)

    pub event ModelUpgraded(modelId: UInt64, dnaId: UInt64, dnaType: UInt64, level: Int)
    pub event ModelExpanded(modelId: UInt64, dnaId: UInt64, dnaType: UInt64, slotNum: UInt64)


   
    /**    ____ ___ ____ ___ ____
       *   [__   |  |__|  |  |___
        *  ___]  |  |  |  |  |___
         ************************/

    pub var totalSupply: UInt64

    pub var levelLimit: UInt64

    // metadata 
    access(contract) var predefinedMetadata: {UInt64: {String: AnyStruct}}

    // model owner mapping
    access(account) let ownerMapping: {UInt64: Address}
    /// Reserved parameter fields: {ParamName: Value}
    access(self) let _reservedFields: {String: AnyStruct}


    /**    ____ _  _ _  _ ____ ___ _ ____ _  _ ____ _    _ ___ _   _
       *   |___ |  | |\ | |     |  | |  | |\ | |__| |    |  |   \_/
        *  |    |__| | \| |___  |  | |__| | \| |  | |___ |  |    |
         ***********************************************************/
    

    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
        pub let id: UInt64

        pub let name: String
        pub let description: String
        pub let thumbnail: String
        pub var slotNum: UInt64

        // access(self) let dnas: @{UInt64: [OverluDNA.NFT]}

        access(self) let royalties: [MetadataViews.Royalty]
        access(self) let metadata: {String: AnyStruct}

    
        init(
            id: UInt64,
            name: String,
            description: String,
            thumbnail: String,
            slotNum: UInt64,
            royalties: [MetadataViews.Royalty],
            metadata: {String: AnyStruct},
        ) {
            self.id = id
            self.name = name
            self.description = description
            self.thumbnail = thumbnail
            self.royalties = royalties
            self.metadata = metadata
            self.slotNum = slotNum
            // self.dnas <- {}
        }

        destroy (){
            // destroy self.dnas
        }

        pub fun upgradeModel(dna: @OverluDNA.NFT) {
            pre{
                self.slotNum > UInt64(OverluConfig.getUpgradeRecords(self.id)?.length ?? 0) : OverluError.errorEncode(msg: "Upgrade: slot number not enough", err: OverluError.ErrorCode.EXCEEDED_AMOUNT_LIMIT)
                // OverluDNA.exemptionTypeIds.contains(dna.typeId) : OverluError.errorEncode(msg: "Upgrade: dna type not allowed", err: OverluError.ErrorCode.MISMATCH_RESOURCE_TYPE)
                OverluModel.levelLimit > UInt64(OverluConfig.getUpgradeRecords(self.id)?.length ?? 0): OverluError.errorEncode(msg: "Upgrade: level limit exceeded", err: OverluError.ErrorCode.EXCEEDED_AMOUNT_LIMIT)
                dna.calculateEnergy() >= 100.0: OverluError.errorEncode(msg: "Upgrade: DNA engrgy not enough", err: OverluError.ErrorCode.INSUFFICIENT_ENERGY)
            }

            // let metadata = OverluDNA.getMetadata(dna.typeId)!
            let metadata = dna.getMetadata()
            
            let upgradeable = (metadata["upgradeable"] as? Bool?)! ?? false
            let energy = dna.calculateEnergy()
            assert(upgradeable == true, message: OverluError.errorEncode(msg: "Upgrade: dna type not upgradeable".concat(dna.typeId.toString()), err: OverluError.ErrorCode.MISMATCH_RESOURCE_TYPE))
            assert(energy == 100.0, message: OverluError.errorEncode(msg: "Upgrade: dna not enough energy ", err: OverluError.ErrorCode.INSUFFICIENT_ENERGY))
            metadata["id"] = dna.id
            OverluConfig.setUpgradeRecords(self.id, metadata: metadata)
            OverluConfig.setDNANestRecords(self.id, dnaId: dna.id)

            emit ModelUpgraded(modelId: self.id, dnaId: dna.id, dnaType: dna.typeId, level: OverluConfig.getUpgradeRecords(self.id)!.length)
            destroy dna
        }

        pub fun expandModel(dna: @OverluDNA.NFT) {
            pre{
                // self.slotNum < OverluModel.levelLimit: OverluError.errorEncode(msg: "Upgrade: level limit exceeded", err: OverluError.ErrorCode.EXCEEDED_AMOUNT_LIMIT)
                dna.calculateEnergy() >= 100.0: OverluError.errorEncode(msg: "Upgrade: DNA engrgy not enough", err: OverluError.ErrorCode.INSUFFICIENT_ENERGY)
            }

            let metadata = OverluDNA.getMetadata(dna.typeId)!
            
            let expandable = (metadata["expandable"] as? Bool?)! ?? false

            let energy = dna.calculateEnergy()
            assert(expandable == true, message: OverluError.errorEncode(msg: "Expand: dna type not expandable", err: OverluError.ErrorCode.MISMATCH_RESOURCE_TYPE))
            assert(energy == 100.0, message: OverluError.errorEncode(msg: "Upgrade: dna not enough energy ", err: OverluError.ErrorCode.INSUFFICIENT_ENERGY))

            let slotNum = self.slotNum + 1
            self.slotNum = slotNum

            metadata["id"] = dna.id

            OverluConfig.setExpandRecords(self.id, metadata: metadata)
            OverluConfig.setDNANestRecords(self.id, dnaId: dna.id)

            emit ModelExpanded(modelId: self.id, dnaId: dna.id, dnaType: dna.typeId, slotNum: slotNum)
            
            destroy dna
        }

        pub fun getMetadata(): {String: AnyStruct} {

            let metadata = OverluModel.predefinedMetadata[self.id] ?? {}
            metadata["metadata"] = self.metadata
            metadata["slotNum"] = self.slotNum
            metadata["dnas"] = OverluConfig.getDNANestRecords(self.id)
            metadata["expands"] = OverluConfig.getExpandRecords(self.id)
            metadata["nested"] = OverluConfig.getDNANestRecords(self.id)
            return metadata
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
            let metadata = OverluModel.predefinedMetadata[self.id] ?? {}

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
                    let editionInfo = MetadataViews.Edition(name: "Overlu model NFT", number: self.id, max: nil)
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
                case Type<MetadataViews.ExternalURL>(): // todo
                    return MetadataViews.ExternalURL(self.thumbnail)
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: OverluModel.CollectionStoragePath,
                        publicPath: OverluModel.CollectionPublicPath,
                        providerPath: /private/OverluModelCollection,
                        publicCollection: Type<&OverluModel.Collection{OverluModel.CollectionPublic}>(),
                        publicLinkedType: Type<&OverluModel.Collection{OverluModel.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Receiver,MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&OverluModel.Collection{OverluModel.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Provider,MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <-OverluModel.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let media = MetadataViews.Media(
                        file: MetadataViews.HTTPFile( 
                            url: "https://trello.com/1/cards/62f22a8782c301212eb2bee8/attachments/62f22aae83b02f8c02303b4c/previews/62f22aaf83b02f8c02303b9d/download/image.png"
                        ),
                        mediaType: "image/png"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "The Overlu Avatar NFT",
                        description: "It integrates all adaptation components of LU. Different avatars will have different abilities to combine with LUs, which depend on their basic attributes. Each avatar is given 1 to 3 LUs to be integrated with. Only if the LU that injected into the avatar can produce real utility (upgrade the appearance components and obtain the corresponding rights).",
                        externalURL: MetadataViews.ExternalURL("https://www.overlu.io"),
                        squareImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url:"https://trello.com/1/cards/62f22a8782c301212eb2bee8/attachments/62f22aae83b02f8c02303b4c/previews/62f22aaf83b02f8c02303b9d/download/image.png"
                            ),
                            mediaType: "image/png"
                        ),
                        bannerImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url: "https://trello.com/1/cards/62f22a8782c301212eb2bee8/attachments/62f22aae83b02f8c02303b4c/previews/62f22aaf83b02f8c02303b9d/download/image.png"
                            ),
                            mediaType: "image/png"
                        ),
                        socials: {
                        "twitter": MetadataViews.ExternalURL("https://twitter.com/OVERLU_NFT")
                        }
                    )
                case Type<MetadataViews.Traits>():

                    let metadata = OverluModel.predefinedMetadata[self.id] ?? {}

                    let traitsView = MetadataViews.dictToTraits(dict: metadata, excludedNames: [])

                    // mintedTime is a unix timestamp, we should mark it with a displayType so platforms know how to show it.
                    let mintedTimeTrait = MetadataViews.Trait(name: "mintedTime", value: self.metadata["mintedTime"]!, displayType: "Date", rarity: nil)
                    traitsView.addTrait(mintedTimeTrait)
                    
                    return traitsView

            }
            return nil
        }
    }

    pub resource interface CollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowOverluModel(id: UInt64): &OverluModel.NFT? {
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow OverluModel reference: the ID of the returned reference is incorrect"
            }
        }
    }

    pub resource Collection: CollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
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
            let token <- token as! @OverluModel.NFT
            
            let id: UInt64 = token.id

            // add the new token to the dictionary which removes the old one
            let oldToken <- self.ownedNFTs[id] <- token

            OverluModel.ownerMapping[id] = self.owner!.address!

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
 
        pub fun borrowOverluModel(id: UInt64): &OverluModel.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &OverluModel.NFT
            }

            return nil
        }

        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let OverluModel = nft as! &OverluModel.NFT
            return OverluModel as &AnyResource{MetadataViews.Resolver}
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

    // public function that anyone can call to create a new empty collection
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }

    // Resource that an admin or something similar would own to be
    // able to mint new NFTs
    //
    pub resource NFTMinter {

        // mintNFT mints a new NFT with a new ID
        // and deposit it in the recipients collection using their collection reference
        pub fun mintNFT(
            recipient: &{NonFungibleToken.CollectionPublic},
            name: String,
            description: String,
            thumbnail: String,
            slotNum: UInt64,
            royalties: [MetadataViews.Royalty],
            metadata: {String: AnyStruct}
        ) {
            let currentBlock = getCurrentBlock()
            metadata["mintedBlock"] = currentBlock.height
            metadata["mintedTime"] = currentBlock.timestamp
            metadata["minter"] = recipient.owner!.address


            assert(OverluModel.totalSupply < 10000, message: OverluError.errorEncode(msg: "Mint: Total supply reach max", err: OverluError.ErrorCode.EXCEEDED_AMOUNT_LIMIT))

            let nftId = OverluModel.totalSupply
            // create a new NFT
            var newNFT <- create NFT(
                id: nftId,
                name: name,
                description: description,
                thumbnail: thumbnail,
                slotNum: slotNum,
                royalties: royalties,
                metadata: metadata,
            )

            // deposit it in the recipient's account using their reference
            recipient.deposit(token: <- newNFT)
            OverluModel.ownerMapping[nftId] = recipient.owner!.address!

            OverluModel.totalSupply = OverluModel.totalSupply + UInt64(1)
        }

        pub fun setLevelLimit(_ limit: UInt64) {
            OverluModel.levelLimit = limit
        }

        // UpdateMetadata
        // Update metadata for a typeId
        //  type // max // name // description // thumbnail // royalties
        //
        pub fun updateMetadata(id: UInt64, metadata: {String: AnyStruct}) {
            OverluModel.predefinedMetadata[id] = metadata
        }
    }

    // public funcs


    pub fun getTotalSupply(): UInt64 {
        return self.totalSupply
    }

    pub fun getMetadata(_ id: UInt64): {String: AnyStruct}? {
        let metadata = self.predefinedMetadata[id] ?? {}

        metadata["dnas"] = OverluConfig.getUpgradeRecords(id) ?? []
        metadata["expands"] = OverluConfig.getExpandRecords(id) ?? []

        return metadata
    }

    pub fun getOwner(_ id: UInt64): Address {
        pre {
            self.ownerMapping[id] != nil: OverluError.errorEncode(msg: "getOwner: can not find ", err: OverluError.ErrorCode.EXCEEDED_AMOUNT_LIMIT)
        }
        return self.ownerMapping[id]!
    } 

    init() {
        // Initialize the total supply
        self.totalSupply = 0

        // Set the named paths
        self.CollectionStoragePath = /storage/OverluModelCollection
        self.CollectionPublicPath = /public/OverluModelCollection
        self.MinterStoragePath = /storage/OverluModelMinter
        self._reservedFields = {}
        self.predefinedMetadata = {}
        self.ownerMapping = {}
        self.levelLimit = 0
        

        // Create a Collection resource and save it to storage
        let collection <- create Collection()
        self.account.save(<-collection, to: self.CollectionStoragePath)

        // create a public capability for the collection
        self.account.link<&OverluModel.Collection{NonFungibleToken.CollectionPublic, OverluModel.CollectionPublic, MetadataViews.ResolverCollection}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )

        // Create a Minter resource and save it to storage
        let minter <- create NFTMinter()
        self.account.save(<-minter, to: self.MinterStoragePath)

        emit ContractInitialized()
    }
}