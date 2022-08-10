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
import FungibleToken from "./standard/FungibleToken.cdc"
import MetadataViews from "./standard/MetadataViews.cdc"
import OverluConfig from "./OverluConfig.cdc"
import OverluError from "./OverluError.cdc"


pub contract OverluDNA: NonFungibleToken {

    /**    ___  ____ ___ _  _ ____
       *   |__] |__|  |  |__| [__
        *  |    |  |  |  |  | ___]
         *************************/

    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let MinterStoragePath: StoragePath
    pub let MinterPublicPath: PublicPath

    /**    ____ _  _ ____ _  _ ___ ____
       *   |___ |  | |___ |\ |  |  [__
        *  |___  \/  |___ | \|  |  ___]
         ******************************/

    pub event ContractInitialized()

    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event TypeTransfered(id: UInt64, typeId: UInt64, to: Address?)
    pub event Destroyed(id: UInt64, typeId: UInt64, operator: Address?)

    /**    ____ ___ ____ ___ ____
       *   [__   |  |__|  |  |___
        *  ___]  |  |  |  |  |___
         ************************/

    pub var totalSupply: UInt64
    pub var currentSupply: UInt64
    pub var baseURI: String


    pub var pause: Bool

    pub var intervalPerEnergy: UFix64
     access(contract) var exemptionTypeIds : [UInt64]

    // multi edition count for metadata
    access(contract) var supplyOfTypes: {UInt64: UInt64}

    /// Reserved parameter fields: {ParamName: Value}
    access(contract) let _reservedFields: {String: AnyStruct}

    // energy records
    access(contract) let energyAddedRecords: {UInt64: [UFix64]}

    // metadata 
    access(contract) var predefinedMetadata: {UInt64: {String: AnyStruct}}

    // rarity records
    access(contract) var rarityMapping: {String: AnyStruct}



    /**    ____ _  _ _  _ ____ ___ _ ____ _  _ ____ _    _ ___ _   _
       *   |___ |  | |\ | |     |  | |  | |\ | |__| |    |  |   \_/
        *  |    |__| | \| |___  |  | |__| | \| |  | |___ |  |    |
         ***********************************************************/
    

    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
        pub let id: UInt64
        pub let name: String
        pub let description: String
        pub let thumbnail: String
        pub let typeId: UInt64
        access(self) let royalties: [MetadataViews.Royalty]
        access(self) let metadata: {String: AnyStruct}
    
        init(
            id: UInt64,
            typeId: UInt64,
            name: String,
            description: String,
            thumbnail: String,
            royalties: [MetadataViews.Royalty],
            metadata: {String: AnyStruct},
        ) {
            self.id = id
            self.typeId = typeId
            self.name = name
            self.description = description
            self.thumbnail = thumbnail
            self.royalties = royalties
            self.metadata = metadata
        }

         destroy (){
            
            // destroy self.dnas
            let typeSupply = OverluDNA.supplyOfTypes[self.typeId]!
            assert(typeSupply > 0 && OverluDNA.currentSupply > 0, message: OverluError.errorEncode(msg: "DNA: type supply should be greater than 0", err: OverluError.ErrorCode.INVALID_PARAMETERS))
            OverluDNA.supplyOfTypes[self.typeId] = typeSupply - UInt64(1)
            OverluDNA.currentSupply = OverluDNA.currentSupply - UInt64(1)

            emit Destroyed(id: self.id, typeId: self.typeId, operator: self.owner?.address)
        }

        pub fun calculateEnergy(): UFix64 {
            var energy = 0.0
            if OverluDNA.exemptionTypeIds.contains(self.typeId) {
                return 100.0
            }
            // calc added energy
            let energyAdded = OverluDNA.energyAddedRecords[self.id] ?? []
            for e in energyAdded {
                energy = energy + e
            }

            if OverluDNA.intervalPerEnergy == 0.0 {
                return energy
            }
            
            let mintedTime = (self.metadata["mintedTime"] as? UFix64?)!
            let currentTime = getCurrentBlock().timestamp
            let timeDiff = currentTime - mintedTime!
            
            let energyBase = timeDiff / OverluDNA.intervalPerEnergy
            energy = energy + energyBase

           
            // fully energy
            if energy > 100.0 {
                energy = 100.0
            }
            return energy
        }

        pub fun getMetadata(): {String: AnyStruct} {
            let metadata = OverluDNA.predefinedMetadata[self.typeId] ?? {}
            // todo other meta data
            if OverluDNA.exemptionTypeIds.contains(self.typeId) {
                metadata["exemption"] = true
            }
            metadata["metadata"] = self.metadata
            metadata["energy"] = self.calculateEnergy()
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
            let metadata = OverluDNA.predefinedMetadata[self.typeId]!
            switch view {
                case Type<MetadataViews.Display>():
                    let name = (metadata["name"] as? String?)!
                    let description = (metadata["description"] as? String?)!
                    let thumbnail = (metadata["thumbnail"] as? String?)!
                    return MetadataViews.Display(
                        name: name!,
                        description: description!,
                        thumbnail: MetadataViews.HTTPFile(
                            url: thumbnail!
                        )
                    )
                case Type<MetadataViews.Editions>():
                    let number = (self.metadata["number"] as? UInt64?)!
                    let max = (metadata["max"] as? UInt64?)!
                    // There is no max number of NFTs that can be minted from this contract
                    // so the max edition field value is set to nil
                    let editionInfo = MetadataViews.Edition(name: "Overlu DNA NFT", number: number!, max: max! )
                    let editionList: [MetadataViews.Edition] = [editionInfo]
                    return MetadataViews.Editions(
                        editionList
                    )
                case Type<MetadataViews.Serial>():
                    let serial = (self.metadata["number"] as? UInt64?)!
                    return MetadataViews.Serial(serial!)
                case Type<MetadataViews.Royalties>():
                    let royalties = (metadata["royalties"] as? [MetadataViews.Royalty]?)!
                    return MetadataViews.Royalties(royalties!)
                case Type<MetadataViews.ExternalURL>():
                    let url = OverluDNA.baseURI.concat(self.typeId.toString())
                    return MetadataViews.ExternalURL(url!)
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: OverluDNA.CollectionStoragePath,
                        publicPath: OverluDNA.CollectionPublicPath,
                        providerPath: /private/OverluDNACollection,
                        publicCollection: Type<&OverluDNA.Collection{OverluDNA.CollectionPublic}>(),
                        publicLinkedType: Type<&OverluDNA.Collection{OverluDNA.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Receiver,MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&OverluDNA.Collection{OverluDNA.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Provider,MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <- OverluDNA.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let media = MetadataViews.Media(
                        file: MetadataViews.HTTPFile(
                            url: OverluDNA.baseURI
                        ),
                        mediaType: "image/png"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "The Overlu LU Collection",
                        description: "LU is of significance that carries info and value. It not only records changes in appearance but also is a component and a proof of utility in the real world. There are currently five types of initial LU acting on 5 different parts of the avatar. Noted that it’s irreversible when initial LU functions, but when it does, the utility follows.",
                        externalURL: MetadataViews.ExternalURL("https://www.overlu.io"), 
                         squareImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url:"https://trello.com/1/cards/62f22a8782c301212eb2bee8/attachments/62f22ac549eec37d05a12068/previews/62f22ac649eec37d05a1217b/download/image.png" 
                            ),
                            mediaType: "image/png"
                        ),
                        bannerImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url: "https://trello.com/1/cards/62f22a8782c301212eb2bee8/attachments/62f22ac549eec37d05a12068/previews/62f22ac649eec37d05a1217b/download/image.png" 
                            ),
                            mediaType: "image/png"
                        ),
                        socials: {
                            "twitter": MetadataViews.ExternalURL("https://twitter.com/OVERLU_NFT") 
                        }
                    )
                case Type<MetadataViews.Traits>():
                    // exclude mintedTime and foo to show other uses of Traits
                    // let excludedTraits = ["mintedTime"]
                    let metadata = OverluDNA.predefinedMetadata[self.typeId]!
                    let traitsView = MetadataViews.dictToTraits(dict: metadata, excludedNames: nil)

                    // let traitsTest = MetadataViews.dictToTraits(dict: metadataStruct , excludedNames: nil)
                    // mintedTime is a unix timestamp, we should mark it with a displayType so platforms know how to show it.
                    let mintedTimeTrait = MetadataViews.Trait(name: "mintedTime", value: self.metadata["mintedTime"]!, displayType: "Date", rarity: nil)
                    let numberTrait = MetadataViews.Trait(name: "number", value: self.metadata["number"]!, displayType: "Number", rarity: nil)
                    traitsView.addTrait(mintedTimeTrait)
                    traitsView.addTrait(numberTrait)
                    
                    return traitsView

            }
            return nil
        }
    }

    pub resource interface CollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowOverluDNA(id: UInt64): &OverluDNA.NFT? {
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow OverluDNA reference: the ID of the returned reference is incorrect"
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
             pre {
                OverluDNA.pause == false : OverluError.errorEncode(msg: "DNA: contract pause", err: OverluError.ErrorCode.CONTRACT_PAUSE)
            }
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing NFT")
            let dna <- token as! @OverluDNA.NFT
            let typeId = dna.typeId
            if !OverluDNA.exemptionTypeIds.contains(typeId) {
                let energy = dna.calculateEnergy()
                assert(energy >= 100.0, message: OverluError.errorEncode(msg: "DNA: energy not enough to transfer", err: OverluError.ErrorCode.INSUFFICIENT_ENERGY))
            }
            // for DNA that use to upgrade do not allow transfer
            if OverluConfig.getDNANestRecords(dna.id) != nil {
                panic(OverluError.errorEncode(msg: "DNA: withdraw not allow after upgrade", err: OverluError.ErrorCode.ACCESS_DENY))
            }

            emit Withdraw(id: dna.id, from: self.owner?.address)

            return <- dna
        }

        // deposit takes a NFT and adds it to the collections dictionary
        // and adds the ID to the id array
        pub fun deposit(token: @NonFungibleToken.NFT) {
           
            let token <- token as! @OverluDNA.NFT

            let id: UInt64 = token.id
            emit TypeTransfered(id: id, typeId:token.typeId, to: self.owner?.address)

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
 
        pub fun borrowOverluDNA(id: UInt64): &OverluDNA.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &OverluDNA.NFT
            }

            return nil
        }

        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let OverluDNA = nft as! &OverluDNA.NFT
            return OverluDNA as &AnyResource{MetadataViews.Resolver}
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

  
    // pub resource interface MinterPublic {
    //     pub fun openPackage(userCertificateCap: Capability<&{OverluConfig.IdentityCertificate}>)
    // }

    // Resource that an admin or something similar would own to be
    // able to mint new NFTs
    //
    pub resource NFTMinter {

        // mintNFT mints a new NFT with a new ID
        // and deposit it in the recipients collection using their collection reference
        pub fun mintNFT(
            typeId: UInt64,
            recipient: &{NonFungibleToken.CollectionPublic},
            name: String,
            description: String,
            thumbnail: String,
            royalties: [MetadataViews.Royalty]
        ) {
            let preMetadata = OverluDNA.predefinedMetadata[typeId]!
            let metadata: {String: AnyStruct} = {}
            let currentBlock = getCurrentBlock()
            metadata["mintedBlock"] = currentBlock.height
            metadata["mintedTime"] = currentBlock.timestamp
            metadata["minter"] = recipient.owner!.address

            var NFTNum: UInt64 = 0

            let typeSupply = OverluDNA.supplyOfTypes[typeId] ?? 0
            let max = (preMetadata["max"] as? UInt64?)!
            if typeSupply == max! {
              panic(OverluError.errorEncode(msg: "DNA: edition number exceed", err: OverluError.ErrorCode.EDITION_NUMBER_EXCEED))
            }
            if typeSupply == 0 {
              OverluDNA.supplyOfTypes[typeId] = 1

            } else {
              OverluDNA.supplyOfTypes[typeId] = typeSupply + (1 as UInt64)
              NFTNum = typeSupply
            }
            metadata["number"] = NFTNum

            // create a new NFT
            var newNFT <- create NFT(
                id: OverluDNA.totalSupply,
                typeId: typeId,
                name: name,
                description: description,
                thumbnail: thumbnail,
                royalties: royalties,
                metadata: metadata,
            )

            // deposit it in the recipient's account using their reference
            recipient.deposit(token: <-newNFT)

            OverluDNA.totalSupply = OverluDNA.totalSupply + UInt64(1)
            OverluDNA.currentSupply = OverluDNA.currentSupply + UInt64(1)
           
        }
        // energy logic
        pub fun setEnergy(id: UInt64, energies: [UFix64]) {
            // pre{
            //     energies.length > 0 : OverluError.errorEncode(msg: "DNA: energy array is empty", err: OverluError.ErrorCode.INVALID_PARAMETERS)
            // }
            OverluDNA.energyAddedRecords[id] = energies
        }

        pub fun addEnergy(id: UInt64, energy: UFix64) {
           let energies = OverluDNA.energyAddedRecords[id] ?? []
           energies.append(energy)
           OverluDNA.energyAddedRecords[id] = energies
        }

        pub fun setInterval(_ interval: UFix64) {
           OverluDNA.intervalPerEnergy = interval
        }

        pub fun setPause(_ pause: Bool) {
            OverluDNA.pause = pause
        }

        pub fun AddExemptionTypeIds(_ id: UInt64) {
            pre {
                OverluDNA.exemptionTypeIds.contains(id) != true: OverluError.errorEncode(msg: "DNA: exemption type id already exists", err: OverluError.ErrorCode.ALREADY_EXIST)
            }
            OverluDNA.exemptionTypeIds.append(id)
        }

        pub fun removeExemptionTypeIds(_ id: UInt64) {
            let idx = OverluDNA.exemptionTypeIds.firstIndex(of: id)
            OverluDNA.exemptionTypeIds.remove(at: idx!)
        }


        pub fun setBaseURI(_ uri: String) {
            OverluDNA.baseURI = uri
        }


        // UpdateMetadata
        // Update metadata for a typeId
        //  type // max // name // description // thumbnail // royalties
        //
        pub fun updateMetadata(typeId: UInt64, metadata: {String: AnyStruct}) {
            let currentSupply = OverluDNA.supplyOfTypes[typeId] ?? 0
            let max = (metadata["max"] as? UInt64?)!

            if currentSupply != nil && currentSupply > 0 {
                assert(currentSupply! <= max!, message: "Can not set max lower than supply")
            }
            OverluDNA.predefinedMetadata[typeId] = metadata
        }

        init() {
            
        }

        destroy (){

        }
    }


    // public function that anyone can call to create a new empty collection
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }
    

    // getTypeSupply
    // Get NFT supply of typeId
    //
    pub fun getTypeSupply(_ typeId: UInt64): UInt64? {
        return OverluDNA.supplyOfTypes[typeId]
    }


    // Get metadata
    //
    pub fun getMetadata(_ typeId: UInt64): {String: AnyStruct} {
        return OverluDNA.predefinedMetadata[typeId] ?? {}
    }

    pub fun getExemptionTypeIds(): [UInt64] {
        return OverluDNA.exemptionTypeIds
    }

    




    init() {
        // Initialize the total supply
        self.totalSupply = 0
        self.currentSupply = 0

        // Set the named paths
        self.CollectionStoragePath = /storage/OverluDNACollection
        self.CollectionPublicPath = /public/OverluDNACollection
        self.MinterStoragePath = /storage/OverluDNAMinter
        self.MinterPublicPath = /public/OverluDNAMinter

        self.predefinedMetadata = {}
        self._reservedFields = {}
        self.intervalPerEnergy = 0.0
        self.energyAddedRecords = {}
        self.supplyOfTypes = {}
        self.baseURI = ""

        self.pause = true

        self.exemptionTypeIds = []
        self.rarityMapping = {}

        // Create a Collection resource and save it to storage
        let collection <- create Collection()
        self.account.save(<- collection, to: self.CollectionStoragePath)

        // create a public capability for the collection
        self.account.link<&OverluDNA.Collection{NonFungibleToken.CollectionPublic, OverluDNA.CollectionPublic, MetadataViews.ResolverCollection}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )

        // Create a Minter resource and save it to storage
        let minter <- create NFTMinter()
        self.account.save(<-minter, to: self.MinterStoragePath)
        // self.account.link<&OverluDNA.NFTMinter{OverluDNA.MinterPublic}>(self.MinterPublicPath, target: self.MinterStoragePath)

        emit ContractInitialized()
    }
}