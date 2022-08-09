
import NonFungibleToken from "./standard/NonFungibleToken.cdc"
import MetadataViews from "./standard/MetadataViews.cdc"
import MelodyError from "./MelodyError.cdc"



pub contract MelodyTicket: NonFungibleToken {


    /**    ___  ____ ___ _  _ ____
       *   |__] |__|  |  |__| [__
        *  |    |  |  |  |  | ___]
         *************************/


    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let CollectionPrivatePath: PrivatePath
    pub let MinterStoragePath: StoragePath


    /**    ____ _  _ ____ _  _ ___ ____
       *   |___ |  | |___ |\ |  |  [__
        *  |___  \/  |___ | \|  |  ___]
         ******************************/

    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)

    pub event TicketCreated(id: UInt64, creator: Address?)
    pub event TicketDestoryed(id: UInt64, owner: Address?)
    pub event TicketTransfered(paymentId: UInt64, from: Address?, to: Address?)
    pub event MetadataUpdated(id: UInt64, key: String)
    pub event MetadataInited(id: UInt64)
    pub event BaseURIUpdated(before: String, after: String)
   
    /**    ____ ___ ____ ___ ____
       *   [__   |  |__|  |  |___
        *  ___]  |  |  |  |  |___
         ************************/

    pub var totalSupply: UInt64
    pub var baseURI: String

    // metadata 
    access(contract) var predefinedMetadata: {UInt64: {String: AnyStruct}}

    // Reserved parameter fields: {ParamName: Value}
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

        access(self) let royalties: [MetadataViews.Royalty]
        access(self) let metadata: {String: AnyStruct}


    
        init(
            id: UInt64,
            name: String,
            description: String,
            metadata: {String: AnyStruct},
        ) {
            self.id = id
            self.name = name
            self.description = description
            if MelodyTicket.baseURI != "" {
                self.thumbnail = MelodyTicket.baseURI.concat(id.toString())
            } else {
                self.thumbnail = ""
            }
            self.royalties = [] // get from metadata
            self.metadata = metadata
        }

        destroy (){
            let metadata = self.getMetadata()
            let status = (metadata["status"] as? UInt8?)!
            let owner = (metadata["owner"] as? Address?)!
            assert(status! > 1, message: MelodyError.errorEncode(msg: "Cannot destory ticket while it is activing", err: MelodyError.ErrorCode.WRONG_LIFE_CYCLE_STATE))
            emit TicketDestoryed(id: self.id, owner: owner)
        }


        pub fun getMetadata(): {String: AnyStruct} {

            let metadata = MelodyTicket.predefinedMetadata[self.id] ?? {}
            metadata["metadata"] = self.metadata
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
                    let editionInfo = MetadataViews.Edition(name: "Melody ticket NFT", number: self.id, max: nil)
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
                    return MetadataViews.ExternalURL("https://meoldy.im/api/data/ticket".concat(self.id.toString()))
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: MelodyTicket.CollectionStoragePath,
                        publicPath: MelodyTicket.CollectionPublicPath,
                        providerPath: MelodyTicket.CollectionPrivatePath,
                        publicCollection: Type<&MelodyTicket.Collection{MelodyTicket.CollectionPublic}>(),
                        publicLinkedType: Type<&MelodyTicket.Collection{MelodyTicket.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Receiver,MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&MelodyTicket.Collection{MelodyTicket.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Provider,MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <-MelodyTicket.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let media = MetadataViews.Media(
                        file: MetadataViews.HTTPFile( 
                            url: "" // todo
                        ),
                        mediaType: "image/svg+xml"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "Melody ticket NFT",
                        description: "This collection is Melody stream/vesting ticket NFT.",
                        externalURL: MetadataViews.ExternalURL("https://meoldy.im"), 
                        squareImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url:"https://trello.com/1/cards/62dd12a167854020143ccd01/attachments/62f0c3c0ff585878ed50e8e8/previews/62f0c3c1ff585878ed50ecbf/download/melody-squ-logo.png"
                            ),
                            mediaType: "image/png"
                        ),
                        bannerImage: MetadataViews.Media(
                            file: MetadataViews.HTTPFile(
                                url: "https://trello.com/1/cards/62dd12a167854020143ccd01/attachments/62f0c3e7b0401e250f0a5199/previews/62f0c3e7b0401e250f0a51df/download/melody-logo.png" 
                            ),
                            mediaType: "image/png"
                        ),
                        socials: {
                            "twitter": MetadataViews.ExternalURL("") // todo
                        }
                    )
                case Type<MetadataViews.Traits>():

                    let metadata = MelodyTicket.predefinedMetadata[self.id]!

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
        pub fun borrowNFTResolver(id: UInt64): &{MetadataViews.Resolver}?
    }

    pub resource interface CollectionPrivate {
        pub fun borrowMelodyTicket(id: UInt64): &MelodyTicket.NFT? {
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow MelodyTicket reference: the ID of the returned reference is incorrect"
            }
        }
    }

    pub resource Collection: CollectionPublic, CollectionPrivate, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
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

            return <- token
        }

        // deposit takes a NFT and adds it to the collections dictionary
        // and adds the ID to the id array
        pub fun deposit(token: @NonFungibleToken.NFT) {
            pre {
                self.checkTransferable(token.id, address: self.owner?.address) == true : MelodyError.errorEncode(msg: "Ticket is not transferable", err: MelodyError.ErrorCode.NOT_TRANSFERABLE)
            }
            let id: UInt64 = token.id

            let token <- token as! @MelodyTicket.NFT

            // add the new token to the dictionary which removes the old one
            let oldToken <- self.ownedNFTs[id] <- token

            // update owner
            let owner = self.owner?.address
            let metadata = MelodyTicket.getMetadata(id)!
            let currentOwner = (metadata["owner"] as? Address?)!

            emit TicketTransfered(paymentId: id, from: currentOwner, to: owner)

            MelodyTicket.updateMetadata(id: id, key: "owner", value: owner)
            
            emit Deposit(id: id, to: owner)

            destroy oldToken
        }

        pub fun checkTransferable(_ id: UInt64, address: Address?): Bool {
            let metadata = MelodyTicket.getMetadata(id)!
            let receievr = (metadata["receiver"] as? Address?)!
            if address != nil && receievr == address {
                return true
            }
            let transferable = (metadata["transferable"] as? Bool?)! ?? true

            return transferable
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
 
        pub fun borrowNFTResolver(id: UInt64): &{MetadataViews.Resolver}? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &MelodyTicket.NFT
            }
            return nil
        }
        pub fun borrowMelodyTicket(id: UInt64): &MelodyTicket.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &MelodyTicket.NFT
            }
            return nil
        }

        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let MelodyTicket = nft as! &MelodyTicket.NFT
            return MelodyTicket as &AnyResource{MetadataViews.Resolver}
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

        // mintNFT mints a new NFT ticket with a new ID
        access(account) fun mintNFT(
            name: String,
            description: String,
            metadata: {String: AnyStruct}
        ): @MelodyTicket.NFT {
            let currentBlock = getCurrentBlock()
            metadata["mintedBlock"] = currentBlock.height
            metadata["mintedTime"] = currentBlock.timestamp

            let nftId = MelodyTicket.totalSupply + UInt64(1)
            // create a new NFT
            var newNFT <- create NFT(
                id: nftId,
                name: name,
                description: description,
                metadata: metadata,
            )
            // deposit it in the recipient's account using their reference
            // recipient.deposit(token: <- newNFT)
            let creator = (metadata["creator"] as? Address?)!
            emit TicketCreated(id: nftId, creator: creator)
            MelodyTicket.totalSupply = nftId
            return <- newNFT
        }
        

     
        // UpdateMetadata
        // Update metadata for a paymentId
        //
        // pub fun updateMetadata(id: UInt64, metadata: {String: AnyStruct}) {
        //     MelodyTicket.predefinedMetadata[id] = metadata
        // }

        pub fun setBaseURI(_ uri: String) {
            emit BaseURIUpdated(before: MelodyTicket.baseURI, after: uri )
            MelodyTicket.baseURI = uri
        }
    }


    access(account) fun setMetadata(id: UInt64, metadata: {String: AnyStruct}) {
        MelodyTicket.predefinedMetadata[id] = metadata
        // emit
        emit MetadataInited(id: id)
    }

    access(account) fun updateMetadata(id: UInt64, key: String, value: AnyStruct) {
        pre {
            MelodyTicket.predefinedMetadata[id] != nil : MelodyError.errorEncode(msg: "Metadata not found", err: MelodyError.ErrorCode.NOT_EXIST)
        }
        let metadata = MelodyTicket.predefinedMetadata[id]!

        emit MetadataUpdated(id: id, key: key)
        metadata[key] = value
        MelodyTicket.predefinedMetadata[id] = metadata
    }

    // public funcs

    pub fun getTotalSupply(): UInt64 {
        return MelodyTicket.totalSupply
    }

    pub fun getMetadata(_ id: UInt64): {String: AnyStruct}? {
        return MelodyTicket.predefinedMetadata[id]
    }




    init() {
        // Initialize the total supply
        self.totalSupply = 0

        // Set the named paths
        self.CollectionStoragePath = /storage/MelodyTicketCollection
        self.CollectionPublicPath = /public/MelodyTicketCollection
        self.CollectionPrivatePath = /private/MelodyTicketCollection
        self.MinterStoragePath = /storage/MelodyTicketMinter
        self._reservedFields = {}

        self.predefinedMetadata = {}
        self.baseURI = ""

        // Create a Collection resource and save it to storage
        let collection <- create Collection()
        self.account.save(<-collection, to: self.CollectionStoragePath)

        // create a public capability for the collection
        self.account.link<&MelodyTicket.Collection{NonFungibleToken.CollectionPublic, MelodyTicket.CollectionPublic, MetadataViews.ResolverCollection}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )
        // create a public capability for the collection
        self.account.link<&MelodyTicket.Collection{MelodyTicket.CollectionPrivate}>(
            self.CollectionPrivatePath,
            target: self.CollectionStoragePath
        )



        // Create a Minter resource and save it to storage
        let minter <- create NFTMinter()
        self.account.save(<- minter, to: self.MinterStoragePath)

        emit ContractInitialized()
    }
}
 