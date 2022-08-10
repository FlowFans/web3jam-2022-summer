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

import NonFungibleToken from 0x631e88ae7f1d7c20
import MetadataViews from 0x631e88ae7f1d7c20
import FungibleToken from 0x9a0766d93b6608b7

pub contract ExampleNFT: NonFungibleToken {

    pub var totalSupply: UInt64
    pub var mintFee: UFix64
    pub var mintGrade: UFix64
    pub var saleFee: UFix64
    pub var totalIncome: UFix64
    pub var lastSumProfit: UFix64
    pub var todayProfit: UFix64
    access(contract) var updateTime: UFix64

    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event Mint(id: UInt64, name: String, quality: String, addr: Address)
    pub event Upgrade(id: UInt64, level: Int, addr: Address?)
    pub event Focus_succcess(id: UInt64, experience: Int)
    pub event ClaimSharing(id: UInt64)

    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let MinterStoragePath: StoragePath

    access(self) let need_experience: [Int]
    access(self) let temp_experience: {UInt64: Int}
    access(all) let merge_prob: {String: [Int]}
    access(self) let genesis_prob: [Int]
    access(self) let mintAbility: {UInt64: Int}
    access(self) let lastMint: {UInt64: UFix64}
    access(self) let sharingIds: {UInt64: UFix64}

    pub struct Attributes {
        pub let sunshine: Int
        pub let moisture: Int
        pub let soil: Int
        pub let carbon_capacity: Int

        init(
            sunshine: Int,
            moisture: Int,
            soil: Int,
            carbon_capacity: Int
        ) {
            self.sunshine = sunshine
            self.moisture = moisture
            self.soil = soil
            self.carbon_capacity = carbon_capacity
        }
    }

    pub struct Level {
        pub let level: Int
        pub let experience: Int
        pub let life: UFix64
        pub let mintAbility: Int
        pub let lastMint: UFix64

        init(
            level: Int,
            experience: Int,
            life: UFix64,
            mintAbility: Int,
            lastMint: UFix64
        ) {
            self.level = level
            self.experience = experience
            self.life = life
            self.mintAbility = mintAbility
            self.lastMint = lastMint
        }
    }

    access(self) fun create_attribute(num: Int): Attributes {
        let random1 = unsafeRandom() % 10000 + 1
        let random2 = unsafeRandom() % 10000 + 1
        let random3 = unsafeRandom() % 10000 + 1
        let random4 = unsafeRandom() % 10000 + 1
        let sum_random = Int(random1 + random2 + random3 + random4) / num
        let sunshine: Int = Int(random1) / sum_random
        let moisture: Int = Int(random2) / sum_random
        let soil: Int = Int(random3) / sum_random
        let carbon_capacity = num - sunshine - moisture - soil
        let attribute: Attributes = Attributes(sunshine: sunshine, moisture: moisture, soil: soil, carbon_capacity: carbon_capacity)
        return attribute
    }

    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
        pub let id: UInt64

        pub let name: String
        pub let quality: String
        pub let description: String
        pub let thumbnail: String

        access(contract) var life: UFix64

        pub let attribute: Attributes
        pub let agingRate: UFix64
        pub let focusRate: UFix64

        access(contract) var experience: Int
        access(contract) var level: Int
        

        access(self) let metadata: {String: AnyStruct}
    
        init(
            id: UInt64,
            name: String,
            quality: String,
            description: String,
            thumbnail: String,
            attribute: Attributes,
            metadata: {String: AnyStruct},
        ) {
            self.id = id
            self.name = name
            self.quality = quality
            self.description = description
            self.thumbnail = thumbnail
            self.life = 9999999999.0
            self.attribute = attribute
            self.agingRate = (1.0 + UFix64(attribute.soil + attribute.carbon_capacity) * 0.1) * 0.05
            self.focusRate = (1.0 + UFix64(attribute.soil + attribute.moisture) * 0.1) * 0.05
            self.experience = 0
            self.level = 0
            self.metadata = metadata
        }

        pub fun upgrade() {
            pre {
                self.level <= 10 : "Full level"
                self.experience >= ExampleNFT.need_experience[self.level] : "no enough experience"
            }
            self.level = self.level + 1
            if self.level == 10 {
                self.life = getCurrentBlock().timestamp
            }
            emit Upgrade(id: self.id, level: self.level, addr: self.owner?.address)
        }

        //是否需要
        pub fun claim_experience() {
          self.experience = ExampleNFT.temp_experience[self.id]!
        }

        pub fun getViews(): [Type] {
            return [
                Type<MetadataViews.Display>(),
                Type<MetadataViews.ExternalURL>(),
                Type<MetadataViews.Traits>(),
                Type<ExampleNFT.Attributes>(),
                Type<ExampleNFT.Level>()
            ]
        }

        pub fun resolveView(_ view: Type): AnyStruct? {
            switch view {
                case Type<MetadataViews.Display>():
                    return MetadataViews.Display(
                        name: self.name,
                        description: self.description,
                        thumbnail: MetadataViews.HTTPFile(url:self.thumbnail)
                    )
                case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL("https://example-nft.onflow.org/".concat(self.id.toString()))
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
                case Type<ExampleNFT.Attributes>():
                    return self.attribute
                case Type<ExampleNFT.Level>():
                    return ExampleNFT.Level(
                        level: self.level,
                        experience: self.experience,
                        life: self.life,
                        mintAbility: ExampleNFT.mintAbility[self.id]!,
                        lastMint: ExampleNFT.lastMint[self.id]!
                    )
            }
            return nil
        }
    }

    pub fun claim_sharing(nft: &ExampleNFT.NFT) {
        pre {
            !ExampleNFT.sharingIds.keys.contains(nft.id) : "the tree is claimed, don't repeat claim"
            ExampleNFT.temp_experience[nft.id]! >= ExampleNFT.need_experience[9] : "This tree has not reached level ten"
            nft.life + 15552000.0 * nft.agingRate >= getCurrentBlock().timestamp : "NFT has no life to live"
        }
        ExampleNFT.sharingIds[nft.id] = getCurrentBlock().timestamp
    }

    pub fun remove_sharing(nft: &ExampleNFT.NFT) {
        pre {
            ExampleNFT.sharingIds.keys.contains(nft.id) : "the tree is not contained in here"
            nft.life + 15552000.0 * nft.agingRate < getCurrentBlock().timestamp : "NFT has enough life to live"
        }
        ExampleNFT.sharingIds.remove(key: nft.id)
    }

    pub fun updateProfit(saleFee: UFix64) {
        pre {
            self.updateTime + 82800.0 <= getCurrentBlock().timestamp: "need time to update"
        }
        self.updateTime = getCurrentBlock().timestamp
        self.todayProfit = self.totalIncome + saleFee - self.lastSumProfit
        self.lastSumProfit = self.totalIncome + saleFee
    }

    pub fun claimProfit(nft: &ExampleNFT.NFT, recipient: Capability<&AnyResource{FungibleToken.Receiver}>) {
        pre {
            ExampleNFT.sharingIds.keys.contains(nft.id) : "the tree is not contained in here"
            nft.life + 15552000.0 * nft.agingRate >= getCurrentBlock().timestamp : "NFT is old"
            ExampleNFT.sharingIds[nft.id]!  <= self.updateTime : "today is claimed"
        }
        let profitNum = self.todayProfit / UFix64(self.sharingIds.length)
        ExampleNFT.sharingIds[nft.id] = getCurrentBlock().timestamp
        let vaultRef = recipient.borrow()
                ?? panic("Could not borrow reference to owner token vault")
        let accountVaultRef = self.account.borrow<&FungibleToken.Vault>(from: /storage/flowTokenVault)
                            ?? panic("no resource")
        let profit <- accountVaultRef.withdraw(amount: profitNum)
        vaultRef.deposit(from: <-profit)
    }

    pub fun create_tree(nft1: @ExampleNFT.NFT, nft2: @ExampleNFT.NFT, recipient: &{NonFungibleToken.CollectionPublic}, mintFee: @FungibleToken.Vault): @[ExampleNFT.NFT] {
        pre {
            nft1.level == 10 : "NFT1 without enough level to create"
            nft2.level == 10 : "NFT2 without enough level to create"
            nft1.life + 7776000.0 * nft1.agingRate >= getCurrentBlock().timestamp : "NFT1 has no life to mint"
            nft2.life + 7776000.0 * nft1.agingRate >= getCurrentBlock().timestamp : "NFT2 has no life to mint"
            ExampleNFT.mintAbility[nft1.id]! < 10 : "NFT1 has no mint times"
            ExampleNFT.mintAbility[nft2.id]! < 10 : "NFT2 has no mint times"
            ExampleNFT.lastMint[nft1.id]! + 172800.0 < getCurrentBlock().timestamp : "NFT1 need time to mint"
            ExampleNFT.lastMint[nft2.id]! + 172800.0 < getCurrentBlock().timestamp : "NFT2 need time to mint"
            mintFee.balance >= ExampleNFT.mintFee * UFix64(nft1.level + nft2.level) * ExampleNFT.mintGrade
            //test
            // nft1.life + 600.0 >= getCurrentBlock().timestamp : "NFT1 has no life to mint"
            // nft2.life + 600.0 >= getCurrentBlock().timestamp : "NFT2 has no life to mint"
            // ExampleNFT.mintAbility[nft1.id]! < 10 : "NFT1 has no mint times"
            // ExampleNFT.mintAbility[nft2.id]! < 10 : "NFT2 has no mint times"
            // ExampleNFT.lastMint[nft1.id]! + 30.0 < getCurrentBlock().timestamp : "NFT1 need time to mint"
            // ExampleNFT.lastMint[nft2.id]! + 30.0 < getCurrentBlock().timestamp : "NFT2 need time to mint"
        }
        let return_nft: @[ExampleNFT.NFT] <- []
        ExampleNFT.lastMint[nft1.id] = getCurrentBlock().timestamp
        ExampleNFT.lastMint[nft2.id] = getCurrentBlock().timestamp
        ExampleNFT.totalIncome = ExampleNFT.totalIncome + mintFee.balance
        let accountVaultRef = self.account.getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver).borrow() ?? panic("no resource")
        accountVaultRef.deposit(from: <-mintFee)
        ExampleNFT.mintAbility[nft1.id] = ExampleNFT.mintAbility[nft1.id]! + 1
        ExampleNFT.mintAbility[nft2.id] = ExampleNFT.mintAbility[nft2.id]! + 1
        let quality = nft1.quality.concat(",").concat(nft2.quality)
        let prob = self.merge_prob[quality]!
        return_nft.append(<-nft1)
        return_nft.append(<-nft2)
        ExampleNFT.mintNFT(recipient: recipient, description: "", thumbnail: "", prob: prob)
        return <-return_nft
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
            let exampleNFT = nft as! &ExampleNFT.NFT
            return exampleNFT as &AnyResource{MetadataViews.Resolver}
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

    // public function that anyone can call to create a new empty collection
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }

    pub fun checkExperience(id: UInt64): Int {
        return ExampleNFT.temp_experience[id]!
    }

    //todo : give something to encourage burn
    pub fun burn(nft: @ExampleNFT.NFT) {
        pre {
            nft.level == 10 : "nft's level is not reach 10"
            nft.life + 15552000.0 * nft.agingRate < getCurrentBlock().timestamp : "this nft has enough time to live"
        }
        destroy(<-nft)
    }

    access(self) fun mintNFT(
        recipient: &{NonFungibleToken.CollectionPublic},
        description: String,
        thumbnail: String,
        prob: [Int]
    ) {
        let metadata: {String: AnyStruct} = {}
        let currentBlock = getCurrentBlock()
        let num = Int(unsafeRandom() % 1000 + 1)
        var sum_attributes = 10
        var name: String = ""
        var quality: String = ""
        if num <= prob[4] {
            sum_attributes = 100
            name = "gingko"
            quality = "Legend"
        }
        else if num <= prob[3] + prob[4] {
            sum_attributes = 80
            name = "maple"
            quality = "Epic"
        }
        else if num <= prob[2] + prob[3] + prob[4] {
            sum_attributes = 40
            name = "pine tree"
            quality = "Rare"
        }
        else if num <= prob[1] + prob[2] + prob[3] + prob[4] {
            sum_attributes = 20
            name = "Begonia tree"
            quality = "no-Common"
        }
        else {
            name = "white poplar"
            quality = "Common"
        }
        let attribute: Attributes = ExampleNFT.create_attribute(num: sum_attributes)
        metadata["mintedBlock"] = currentBlock.height
        metadata["mintedTime"] = currentBlock.timestamp
        metadata["minter"] = recipient.owner!.address

        // this piece of metadata will be used to show embedding rarity into a trait
        metadata["foo"] = "bar"

        // create a new NFT
        var newNFT <- create NFT(
            id: ExampleNFT.totalSupply,
            name: name,
            quality: quality,
            description: description,
            thumbnail: thumbnail,
            attribute: attribute,
            metadata: metadata,
        )
        self.mintAbility[newNFT.id] = 0
        self.lastMint[newNFT.id] = 0.0
        emit Mint(id: newNFT.id, name: newNFT.name, quality: newNFT.quality, addr: recipient.owner!.address)
        // deposit it in the recipient's account using their reference
        recipient.deposit(token: <-newNFT)

        ExampleNFT.totalSupply = ExampleNFT.totalSupply + UInt64(1)
    }
    
    // Resource that an admin or something similar would own to be
    // able to mint new NFTs
    //
    pub resource NFTMinter {

        pub fun store_experience(id: UInt64, experience: Int) {
            if(ExampleNFT.temp_experience[id] == nil) {
                ExampleNFT.temp_experience[id] = experience
            }
            else {
                ExampleNFT.temp_experience[id] = ExampleNFT.temp_experience[id]! + experience
            }
            emit Focus_succcess(id: id, experience: experience)
        }
        
        pub fun mint(
            recipient: &{NonFungibleToken.CollectionPublic},
            description: String,
            thumbnail: String,
        ) {
            ExampleNFT.mintNFT(recipient: recipient, description: description, thumbnail: thumbnail, prob: ExampleNFT.genesis_prob)
        }
    }

    init() {
        // Initialize the total supply
        self.totalSupply = 0
        self.mintFee = 1.0
        self.mintGrade = 0.02
        self.saleFee = 0.02
        self.totalIncome = 0.0
        self.lastSumProfit = 0.0
        self.todayProfit = 0.0
        self.updateTime = 0.0
        self.sharingIds = {}
        // Set the named paths
        self.CollectionStoragePath = /storage/exampleNFTCollection
        self.CollectionPublicPath = /public/exampleNFTCollection
        self.MinterStoragePath = /storage/exampleNFTMinter

        self.need_experience = [60,120,200,300,420,560,720,900,1100,1320]
        self.temp_experience = {}
        self.merge_prob = {
            "Common,Common": [1000,0,0,0,0],
            "Common,no-Common": [500,490,10,0,0],
            "Common,Rare": [500,0,490,10,0],
            "Common,Epic": [500,0,0,490,10],
            "Common,Legend": [500,0,0,0,500],
            "no-Common,no-Common": [0,980,20,0,0],
            "no-Common,Common": [500,490,10,0,0],
            "no-Common,Rare": [0,490,500,10,0],
            "no-Common,Epic": [0,490,10,490,10],
            "no-Common,Legend": [0,490,10,0,500],
            "Rare,Rare": [0,0,980,20,0],
            "Rare,Common": [500,0,490,10,0],
            "Rare,no-Common": [0,490,500,10,0],
            "Rare,Epic": [0,0,490,500,10],
            "Rare,Legend": [0,0,490,10,500],
            "Epic,Epic": [0,0,0,980,20],
            "Epic,Common": [500,0,0,490,10],
            "Epic,no-Common": [0,490,10,490,10],
            "Epic,Rare,": [0,0,490,500,10],
            "Epic,Legend": [0,0,0,490,510],
            "Legend,Legend": [0,0,0,0,1000],
            "Legend,Common": [500,0,0,0,500],
            "Legend,no-Common": [0,490,10,0,500],
            "Legend,Rare": [0,0,490,10,500],
            "Legend,Epic": [0,0,0,490,510]
        }
        self.genesis_prob = [800,100,80,15,5]
        self.mintAbility = {}
        self.lastMint = {}
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

        emit ContractInitialized()
    }
}