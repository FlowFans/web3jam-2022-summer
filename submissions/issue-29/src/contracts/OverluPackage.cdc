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
import OverluModel from "./OverluModel.cdc"
import OverluDNA from "./OverluDNA.cdc"
import OverluError from "./OverluError.cdc"

pub contract OverluPackage: NonFungibleToken {

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
    pub event PackageSold(amount: UInt8, price: UFix64, to: Address?, totalPurchsed: UInt8, vaultReceiver: Address)
    pub event PackageOpened(packId: UInt64, modelId: UInt64, dnaId: UInt64, to: Address?)
    pub event Destroyed(id: UInt64, operator: Address?)

    /**    ____ ___ ____ ___ ____
       *   [__   |  |__|  |  |___
        *  ___]  |  |  |  |  |___
         ************************/

    pub var totalSupply: UInt64
    pub var baseURI: String

    pub var pause: Bool
    pub var price: UFix64
    pub var saleLimit: UInt8
    pub var isOpen: Bool

    // multi edition count for metadata
    access(contract) var supplyOfTypes: {UInt64: UInt64}

    /// Reserved parameter fields: {ParamName: Value}
    access(contract) let _reservedFields: {String: AnyStruct}

    access(contract) var saleRecords: {Address: [UInt64]}

    access(contract) var predefinedMetadata: {UInt64: {String: AnyStruct}}

    access(contract) var vaultReceiver: Capability<&{FungibleToken.Receiver}>?



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

            emit Destroyed(id: self.id, operator: self.owner?.address)
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

        pub fun getMetadata(): {String: AnyStruct} {
            let metadata = OverluPackage.predefinedMetadata[self.typeId] ?? {}
            return metadata
        }

        pub fun resolveView(_ view: Type): AnyStruct? {
            let metadata = OverluPackage.predefinedMetadata[self.typeId]!
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
                    let editionInfo = MetadataViews.Edition(name: "Overlu package NFT", number: number!, max: max! )
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
                    let url = (metadata["baseURI"] as? String?)!
                    return MetadataViews.ExternalURL(url!)
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: OverluPackage.CollectionStoragePath,
                        publicPath: OverluPackage.CollectionPublicPath,
                        providerPath: /private/OverluPackageCollection,
                        publicCollection: Type<&OverluPackage.Collection{OverluPackage.CollectionPublic}>(),
                        publicLinkedType: Type<&OverluPackage.Collection{OverluPackage.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Receiver,MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&OverluPackage.Collection{OverluPackage.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Provider,MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <- OverluPackage.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let media = MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url: OverluPackage.baseURI
                            ),
                            mediaType: "image/png"
                        )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "The OverBox Collection",
                        description: "Each exquisitely designed OverBox of OVERLU is an NFT. Once the OverBox is opened, it’s also burned to obtain an avatar and an initial LU, in which rarity, function, and equity are all random.",
                        externalURL: MetadataViews.ExternalURL("https://www.overlu.io"),
                        squareImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url:"" // todo
                            ),
                            mediaType: "image/png"
                        ),
                        bannerImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url: "" // todo
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
                    let metadata = OverluPackage.predefinedMetadata[self.typeId]!
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
        pub fun borrowOverluPackage(id: UInt64): &OverluPackage.NFT? {
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow OverluPackage reference: the ID of the returned reference is incorrect"
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
                OverluPackage.pause == false : OverluError.errorEncode(msg: "Package: contract pause", err: OverluError.ErrorCode.CONTRACT_PAUSE)
            }
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing NFT")

            emit Withdraw(id: token.id, from: self.owner?.address)

            return <-token
        }

        // deposit takes a NFT and adds it to the collections dictionary
        // and adds the ID to the id array
        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @OverluPackage.NFT

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
 
        pub fun borrowOverluPackage(id: UInt64): &OverluPackage.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &OverluPackage.NFT
            }

            return nil
        }

        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let OverluPackage = nft as! &OverluPackage.NFT
            return OverluPackage as &AnyResource{MetadataViews.Resolver}
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

  
    pub resource interface MinterPublic {
        // pub fun getVaultBal(): UFix64
        // pub fun getVaultType(): String
    }

    // Resource that an admin or something similar would own to be
    // able to mint new NFTs
    //
    pub resource NFTMinter: MinterPublic {

        priv var vault: @FungibleToken.Vault?

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
            let preMetadata = OverluPackage.predefinedMetadata[typeId]!
            let metadata: {String: AnyStruct} = {}
            let currentBlock = getCurrentBlock()
            metadata["mintedBlock"] = currentBlock.height
            metadata["mintedTime"] = currentBlock.timestamp
            metadata["minter"] = recipient.owner!.address

            var NFTNum: UInt64 = 0
            log(preMetadata)
            let typeSupply = OverluPackage.supplyOfTypes[typeId] ?? 0
            let max = (preMetadata["max"] as? UInt64?)!
            if typeSupply == max! {
              panic("Edition number reach max with typeId: ".concat(typeId.toString()))
            }
            if typeSupply == 0 {
              OverluPackage.supplyOfTypes[typeId] = 1
              NFTNum = 1
            } else {
              let num = typeSupply + (1 as UInt64)
              OverluPackage.supplyOfTypes[typeId] = num
              NFTNum = num
            }
            metadata["number"] = NFTNum

            assert(OverluPackage.totalSupply < 10000, message: OverluError.errorEncode(msg: "Mint: Total supply reach max", err: OverluError.ErrorCode.EXCEEDED_AMOUNT_LIMIT))

            // create a new NFT
            var newNFT <- create NFT(
                id: OverluPackage.totalSupply,
                typeId: typeId,
                name: name,
                description: description,
                thumbnail: thumbnail,
                royalties: royalties,
                metadata: metadata,
            )

            // deposit it in the recipient's account using their reference
            recipient.deposit(token: <-newNFT)

            OverluPackage.totalSupply = OverluPackage.totalSupply + UInt64(1)
           
        }

        pub fun setPrice(_ price: UFix64) {
            OverluPackage.price = price
        }

        pub fun setPause(_ pause: Bool) {
            OverluPackage.pause = pause
        }

        pub fun setSaleLimit(_ limit: UInt8) {
            OverluPackage.saleLimit = limit
        }

        pub fun setBaseURI(_ uri: String) {
            OverluPackage.baseURI = uri
        }

        pub fun setOpenFlag(_ flag: Bool) {
            OverluPackage.isOpen = flag
        }

        pub fun setVault(_ vault: @FungibleToken.Vault) {
           let vaultRef = &self.vault as &FungibleToken.Vault?

           if vaultRef != nil && vaultRef!.balance > 0.0 {
                panic(OverluError.errorEncode(msg: "Package: vault exist and has balance in it", err: OverluError.ErrorCode.RESOURCE_ALREADY_EXIST))
           } else {
                self.vault <-! vault
           }
        }

        pub fun setVaultReceiver(_ cap: Capability<&{FungibleToken.Receiver}>) {
            OverluPackage.vaultReceiver = cap
        }


        pub fun getVaultRef(): &FungibleToken.Vault? {
            return &self.vault as &FungibleToken.Vault?
        }


        // UpdateMetadata
        // Update metadata for a typeId
        //
        pub fun updateMetadata(typeId: UInt64, metadata: {String: AnyStruct}) {
            let currentSupply = OverluPackage.supplyOfTypes[typeId] ?? 0
            let max = (metadata["max"] as? UInt64?)!
            if currentSupply != nil && currentSupply > 0 {
                assert(currentSupply <= max!, message: "Can not set max lower than supply")
            }
            OverluPackage.predefinedMetadata[typeId] = metadata
        }

        pub fun cleanSaleRecords() {
            OverluPackage.saleRecords = {}
        }

        init() {
            self.vault <- nil
        }

        destroy(){
            destroy self.vault
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
        return OverluPackage.supplyOfTypes[typeId]
    }

    // getTypeSupply
    // Get NFT supply of typeId
    //
    pub fun getMetadata(_ typeId: UInt64): {String: AnyStruct}? {
        let metadata = OverluPackage.predefinedMetadata[typeId] ?? {}
        let keys = metadata.keys
        // for key in keys {
        //     if key == "royalties" {
        //         let royalties = (metadata["royalties"] as? [MetadataViews.Royalty]?)!
        //         let royaltiesData:[String] = []
        //         for royalty in royalties! {
        //             let address = royalty.receiver.borrow()?.owner?.address
        //             let cut = royalty.cut
        //             let description = royalty.description
        //             royaltiesData.append("Description: ".concat(description).concat(", Cut:").concat(cut.toString()).concat(address?.toString() ?? ""))
        //         }
        //         metadata["royalties"] = royaltiesData
        //     }
        // }
        return metadata
    }


    // purchase package
    pub fun purchasePackage(userCertificateCap: Capability<&{OverluConfig.IdentityCertificate}>, amount: UInt8, feeToken: @FungibleToken.Vault) {
        pre {
            OverluPackage.pause == false: OverluError.errorEncode(msg: "Purchase not open yet", err: OverluError.ErrorCode.NOT_OPEN)
            OverluPackage.price > 0.0: OverluError.errorEncode(msg: "Price not set", err: OverluError.ErrorCode.NOT_OPEN)
            OverluPackage.saleLimit > 0: OverluError.errorEncode(msg: "Sale limit not set", err: OverluError.ErrorCode.NOT_OPEN)
        }

        let address = userCertificateCap.borrow()!.owner!.address
        let records = OverluPackage.saleRecords[address] ?? []
        
        assert((UInt8(records!.length) + amount) <= OverluPackage.saleLimit, message: OverluError.errorEncode(msg: "Cannot buy more", err: OverluError.ErrorCode.EXCEEDED_AMOUNT_LIMIT))
        
        let totalPrice = OverluPackage.price * UFix64(amount) 

        assert(feeToken.balance >= totalPrice, message: OverluError.errorEncode(msg: "Not enough balance", err: OverluError.ErrorCode.NOT_ENOUGH_BALANCE))

        let minter = OverluPackage.account.borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)!
        // let vaultRef = minter.getVaultRef()

        let vaultReceiver = OverluPackage.vaultReceiver!.borrow() ?? panic(OverluError.errorEncode(msg: "Vault receiver not set", err: OverluError.ErrorCode.INVALID_PARAMETERS))

        emit PackageSold(
            amount: amount,
            price: feeToken.balance,
            to: address,
            totalPurchsed: UInt8(records.length) + amount,
            vaultReceiver: vaultReceiver.owner!.address
            )

        // vaultRef!.deposit(from: <- feeToken)
        vaultReceiver!.deposit(from: <- feeToken)
        
        var idx: UInt8 = 0
        while idx < amount {
            minter.mintNFT(
                typeId: UInt64(1),
                recipient: getAccount(address).getCapability<&{NonFungibleToken.CollectionPublic}>(OverluPackage.CollectionPublicPath).borrow()!,
                name: "",
                description: "",
                thumbnail: "",
                royalties: []
            )
            idx = idx + 1
            records.append(OverluPackage.totalSupply - 1 as UInt64)
        }

        self.saleRecords[address] = records 

    }


    pub fun openPackage(userCertificateCap: Capability<&{OverluConfig.IdentityCertificate}>, nft: @OverluPackage.NFT): @[NonFungibleToken.NFT] {
        pre {
            // nft.getType().identifier == OverluPackage.NFT.getType().identifier : OverluError.errorEncode(msg: "Package: mismatch nft type".concat(nft.getType().identifier).concat(" : ").concat(OverluPackage.NFT.getType().identifier), err: OverluError.ErrorCode.MISMATCH_RESOURCE_TYPE)
            OverluPackage.isOpen == true: OverluError.errorEncode(msg: "Package: can not open yet", err: OverluError.ErrorCode.NOT_OPEN)
        }
        // todo
        let modelCollection = OverluPackage.account.borrow<&OverluModel.Collection>(from: OverluModel.CollectionStoragePath)!
        let dnaCollection = OverluPackage.account.borrow<&OverluDNA.Collection>(from: OverluDNA.CollectionStoragePath)!
        let modelIds = modelCollection.getIDs()
        let modelAvailable = modelIds.length
        let dnaIds = dnaCollection.getIDs()
        let dnaAvailable = dnaIds.length
        
        assert( modelAvailable > 0 && dnaAvailable > 0, message: OverluError.errorEncode(msg: "OpenPack: Not enough NFT: model available: ".concat(modelAvailable.toString()).concat(" DNA available: ").concat(dnaAvailable.toString()), err: OverluError.ErrorCode.NOT_ENOUGH_BALANCE))

        let nfts: @[NonFungibleToken.NFT] <- []

        let openModelIdx= OverluConfig.getRandomId(modelAvailable)
        let openDNAIdx = OverluConfig.getRandomId(dnaAvailable)
        

        nfts.append(<- modelCollection.withdraw(withdrawID: modelIds[openModelIdx]))
        nfts.append(<- dnaCollection.withdraw(withdrawID: dnaIds[openDNAIdx]))

        let address = userCertificateCap.borrow()!.owner!.address
        emit PackageOpened(packId: nft.id, modelId: modelIds[openModelIdx], dnaId: dnaIds[openDNAIdx], to: address)

        // destory package
        destroy nft
        return <- nfts
    }

    pub fun getVaultBal(): UFix64 {
        let minter = OverluPackage.account.borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)
        var bal = 0.0
        let vaultRef = minter!.getVaultRef()
        if vaultRef != nil {
            bal = vaultRef!.balance
        }
        return bal
    }

    pub fun getVaultType(): String {
        let minter = OverluPackage.account.borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)
        var vaultType = ""
        let vaultRef = minter!.getVaultRef()
        if vaultRef != nil {
            vaultType = vaultRef!.getType().identifier
        }
        return vaultType
    }

    pub fun getVaultReceiver(): Address? {
        return OverluPackage.vaultReceiver!.borrow()!.owner?.address
    }


    pub fun getSaleRecords(_ address: Address): [UInt64] {
        let records = OverluPackage.saleRecords[address] ?? []
        return records
    }

    pub fun getAllSaleRecords(): {Address: [UInt64]}{
        return OverluPackage.saleRecords
    }


    init() {
        // Initialize the total supply
        self.totalSupply = 0

        // Set the named paths
        self.CollectionStoragePath = /storage/OverluPackageCollection
        self.CollectionPublicPath = /public/OverluPackageCollection
        self.MinterStoragePath = /storage/OverluPackageMinter
        self.MinterPublicPath = /public/OverluPackageMinter

        self.predefinedMetadata = {}
        self._reservedFields = {}
        self.saleRecords = {}
        self.supplyOfTypes = {}
        self.baseURI = ""

        self.pause = true
        self.price = 0.0
        self.saleLimit = 0
        self.isOpen = false
        self.vaultReceiver = nil


        // Create a Collection resource and save it to storage
        let collection <- create Collection()
        self.account.save(<-collection, to: self.CollectionStoragePath)

        // create a public capability for the collection
        self.account.link<&OverluPackage.Collection{NonFungibleToken.CollectionPublic, OverluPackage.CollectionPublic, MetadataViews.ResolverCollection}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )

        // Create a Minter resource and save it to storage
        let minter <- create NFTMinter()
        self.account.save(<-minter, to: self.MinterStoragePath)
        self.account.link<&OverluPackage.NFTMinter{OverluPackage.MinterPublic}>(self.MinterPublicPath, target: self.MinterStoragePath)

        emit ContractInitialized()
    }
}
