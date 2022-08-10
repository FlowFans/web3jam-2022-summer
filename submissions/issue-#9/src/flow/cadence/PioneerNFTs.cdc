 import NonFungibleToken from 0x631e88ae7f1d7c20
 import MetadataViews from 0x631e88ae7f1d7c20
import PioneerLocking from 0x2a9b2f3682e65464


// import NonFungibleToken from "./NonFungibleToken.cdc"
// import MetadataViews from "./MetadataViews.cdc"
// import PioneerLocking from "./PioneerLocking.cdc"

pub contract PioneerNFTs:NonFungibleToken{

    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event MintPioneerNFT(id: UInt64)
     // Event that is emitted when a new NFT is put up for sale
    pub event ForSale(id: UInt64, price: UFix64, owner: Address?)
      // Event that is emitted when a seller withdraws their NFT from the sale
    pub event SaleCanceled(id: UInt64, seller: Address?)


    pub let CollectionStoragePath: StoragePath
    pub let CollectionPrivatePath: PrivatePath
    pub let CollectionPublicPath: PublicPath
    pub let MinterStoragePath: StoragePath
    pub var totalSupply: UInt64
    pub var pricesMapping:{UInt64: PriceMeta}
    pub var PioneerNFTMapping:{UInt64: PriceMeta}

    // pub var prices:{UInt64:UFix64}

    pub struct PioneerNFTMetadataView {
        pub let id: UInt64
        pub let name: String
        pub let description: String
        pub let url: String
        pub let creator: Address
        pub let createTime: UFix64
        pub let externalUrl: String

        pub let properties: {String:String}?
        init( 
        id: UInt64,
        name: String,
        description: String,
        url: String,
        creator: Address,
        createTime: UFix64,
        externalUrl: String,
        properties: {String:String}?){
            self.id=id
            self.name=name
            self.description=description
            self.url=url
            self.creator=creator
            self.createTime=createTime
            self.externalUrl=externalUrl
            self.properties=properties
        }
     }

   pub struct PriceMeta {
        pub let price: UFix64
        pub let url: String
        pub let name: String
        pub let description: String
        pub let officialUrl: String
        pub let creator: Address

        init( 
        price: UFix64,
        url: String,
        name: String,
        description: String,
        officialUrl: String,
        creator: Address,
        )
       {
            self.price = price
            self.url = url
            self.name = name
            self.description = description
            self.officialUrl = officialUrl
            self.creator = creator
        }
     }

     pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
       
        pub let id: UInt64
        pub let name: String
        pub let description: String
        pub let url: String
        pub let creator: Address
        pub let createTime: UFix64
        pub let externalUrl: String
        pub let properties: {String:String}?
        init(
            name: String,
            description: String,
            url: String,
            creator: Address,
            createTime: UFix64,
            externalUrl: String,
            properties: {String:String}?
        ) {
            self.id = PioneerNFTs.totalSupply+1
            self.description=description
            self.name=name
            self.url=url
            self.creator=creator
            self.createTime=createTime
            self.externalUrl=externalUrl
            self.properties=properties
        }
        pub fun getViews(): [Type] {
            return [
                Type<MetadataViews.Display>(),
                Type<PioneerNFTMetadataView>(),
                Type<MetadataViews.ExternalURL>()
            ]
        }

        pub fun getname(): String {
          return self.name
        }
        pub fun geturl(): String{
            return self.url
        }
        pub fun getdescription(): String {
           return self.description
        }

          pub fun resolveView(_ view: Type): AnyStruct? {
            switch view {
                case Type<MetadataViews.Display>():
                       return MetadataViews.Display(
                        name: self.getname(),
                        description: self.getdescription(),
                        thumbnail: MetadataViews.HTTPFile(self.geturl())
                    )
                case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL(self.externalUrl)
                case Type<PioneerNFTMetadataView>():
                    
                    return PioneerNFTMetadataView(
                        id: self.id,
                        name: self.name,
                        description: self.description,
                        url:self.url,
                        creator:self.creator,
                        createTime:self.createTime,
                        externalUrl: self.externalUrl,
                        properties:self.properties,
                    )
            }
            return nil
        }





     }

     pub resource interface PioneerNFTCollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun batchDeposit(tokens: @NonFungibleToken.Collection)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowPioneerNFT(id: UInt64): &PioneerNFTs.NFT? {
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow PioneerNFT reference: the ID of the returned reference is incorrect"
            }
        }
    }

    pub resource Collection: PioneerNFTCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}

        init () {
            self.ownedNFTs <- {}
        }

        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let nft =self.borrowNFT(id: withdrawID)
            if PioneerLocking.isLocked(nftRef: nft){
                panic("Cannot withdraw: PioneerNFT is locked")
            }
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("Cannot withdraw: PioneerNFT does not exist in the collection")
            emit Withdraw(id: token.id, from: self.owner?.address)
            return <-token
        }

         pub fun batchWithdraw(ids: [UInt64]): @NonFungibleToken.Collection {
            var batchCollection <- create Collection()
            
            for id in ids {
                batchCollection.deposit(token: <-self.withdraw(withdrawID: id))
            }
            
            return <-batchCollection
        }
        

        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @PioneerNFTs.NFT

            let id: UInt64 = token.id

            let oldToken <- self.ownedNFTs[id] <- token

             if self.owner?.address != nil {
                emit Deposit(id: id, to: self.owner?.address)
            }

            emit Deposit(id: id, to: self.owner?.address)

            destroy oldToken
        }

       

        pub fun batchDeposit(tokens: @NonFungibleToken.Collection) {

            let keys = tokens.getIDs()

            for key in keys {
                self.deposit(token: <-tokens.withdraw(withdrawID: key))
            }

            destroy tokens
        }


        pub fun lock(id: UInt64, duration: UFix64) {
            let token <- self.ownedNFTs.remove(key: id) 
                ?? panic("Cannot lock: Moment does not exist in the collection")

            // pass the token to the locking contract
            // store it again after it comes back
            let oldToken <- self.ownedNFTs[id] <- PioneerLocking.lockNFT(nft: <- token, duration: duration)

            destroy oldToken
        }

        // batchLock takes an array of token ids and a duration in seconds
        // it iterates through the ids and locks each for the specified duration
        pub fun batchLock(ids: [UInt64], duration: UFix64) {
            // Iterate through the ids and lock them
            for id in ids {
                self.lock(id: id, duration: duration)
            }
        }

        // unlock takes a token id and attempts to unlock it
        // PioneerLocking.unlockNFT contains business logic around unlock eligibility
        pub fun unlock(id: UInt64) {
            // Remove the nft from the Collection
            let token <- self.ownedNFTs.remove(key: id) 
                ?? panic("Cannot lock: Moment does not exist in the collection")

            // Pass the token to the PioneerLocking contract then get it back
            // Store it back to the ownedNFTs dictionary
            let oldToken <- self.ownedNFTs[id] <- PioneerLocking.unlockNFT(nft: <- token)

            destroy oldToken
        }

        // batchUnlock takes an array of token ids
        // it iterates through the ids and unlocks each if they are eligible
        pub fun batchUnlock(ids: [UInt64]) {
            // Iterate through the ids and unlocks them
            for id in ids {
                self.unlock(id: id)
            }
        }

        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            
            return (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
        }
 
        pub fun borrowPioneerNFT(id: UInt64): &PioneerNFTs.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &PioneerNFTs.NFT
            }

            return nil
        }
     
        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let PioneerNFT = nft as! &PioneerNFTs.NFT
            return PioneerNFT as &AnyResource{MetadataViews.Resolver}
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

    // public function that anyone can call to create a new empty collection
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }


    pub resource NFTMinter {
          pub fun mintNFT(
                name: String,
                description: String,
                url: String,
                creator: Address,
                createTime: UFix64,
                externalUrl: String,
                properties: {String:String}?
        ): @NFT {           
            let  newNFT: @NFT <- create NFT(
                        name: name,
                        description: description,
                        url:url,
                        creator:creator,
                        createTime:createTime,
                        externalUrl: externalUrl, 
                        properties:properties,
            )

            emit MintPioneerNFT(id: PioneerNFTs.totalSupply)
             PioneerNFTs.totalSupply = PioneerNFTs.totalSupply + UInt64(1)
            return <-newNFT
         }
         pub fun batchMintPioneerNFT(
                name: String,
                description: String,
                url: String,
                creator: Address,
                createTime: UFix64,
                externalUrl: String,
                properties: {String:String}?,quantity: UInt64): @Collection {
            let newCollection <- create Collection()

            var i: UInt64 = 0
            while i < quantity {
                newCollection.deposit(token: <-self.mintNFT( 
                        name: name,
                        description: description,
                        url:url,
                        creator:creator,
                        createTime:createTime,
                        externalUrl: externalUrl, 
                        properties:properties,))
                i = i + UInt64(1)
            }

            return <-newCollection
        }        
    }

    pub resource interface SalePublic {
        pub fun addSaleList(tokenID:UInt64,price:UFix64)
        pub fun cancelSale(tokenID:UInt64)
        pub fun idPrice(tokenID:UInt64):UFix64?
        pub fun getIDs():[UInt64]


    }

    // pub resource SaleCollection:SalePublic {

    //     //上架
    //     pub fun addSaleList(tokenID:UInt64,price:UFix64){
    //         PioneerNFTs.prices[tokenID] = price
    //         emit ForSale(id: tokenID, price: price, owner: self.owner?.address)
    //     }
    //     //下架
    //     pub fun cancelSale(tokenID:UInt64){
    //         PioneerNFTs.prices.remove(key:tokenID)
    //         PioneerNFTs.prices[tokenID] = nil
    //     }
    //     //通过id 获取价格
    //     pub fun idPrice(tokenID:UInt64):UFix64? {
    //         return PioneerNFTs.prices[tokenID]
    //     }
        
    //     //返回在售nftlist
    //     pub fun getIDs():[UInt64] {
    //         return PioneerNFTs.prices.keys
    //     }

    // }
    pub fun createEmptyNFTMinter(): @NFTMinter{
        return <-create NFTMinter()
    }

    //  pub fun createSaleCollection(): @SaleCollection {
    //     return <-create SaleCollection()
    // }
          //上架
    pub fun addSaleList(id:UInt64,url:String,price:UFix64,name: String,description: String,officialUrl: String,creator: Address){
        let priceMeta = PriceMeta(price:price,url:url,name:name,description:description,officialUrl:officialUrl,creator:creator)
        PioneerNFTs.pricesMapping[id] = priceMeta
    }
    //下架
    pub fun cancelSale(tokenID:UInt64){
        PioneerNFTs.pricesMapping.remove(key:tokenID)
        PioneerNFTs.pricesMapping[tokenID] = nil
     }

    pub fun getIDs():{UInt64: PriceMeta} {
        return PioneerNFTs.pricesMapping
     }


    init() {
        self.totalSupply=0
        self.CollectionStoragePath = /storage/PioneerNFTsCollection
        self.CollectionPrivatePath= /private/PioneerNFTsCollection
        self.CollectionPublicPath = /public/PioneerNFTsCollection
        self.MinterStoragePath = /storage/PioneerNFTsMinter
        // self.SaleCollectionStoragePath = /storage/PioneerSaleCollection
        // self.SaleCollectionPublicPath = /public/PioneerSaleCollection
        self.pricesMapping = {}
        self.PioneerNFTMapping = {}

        // Create a Collection resource and save it to storage
        let collection <- create Collection()
        self.account.save(<-collection, to: self.CollectionStoragePath)

        // create a public capability for the collection
        self.account.link<&PioneerNFTs.Collection{NonFungibleToken.CollectionPublic, PioneerNFTs.PioneerNFTCollectionPublic, MetadataViews.ResolverCollection}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )

        // self.account.save(<- create SaleCollection(),to:self.SaleCollectionStoragePath)
        // self.account.link<&{SalePublic}>(self.SaleCollectionPublicPath,target:self.SaleCollectionStoragePath)

        let minter <- create NFTMinter()
        self.account.save(<-minter, to: self.MinterStoragePath)

        emit ContractInitialized()


    }
}


