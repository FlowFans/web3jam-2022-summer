import NonFungibleToken from "./NonFungibleToken.cdc"
import MetadataViews from "./MetadataViews.cdc"

pub contract CaaPass: NonFungibleToken {

    // Events
    //
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event Minted(id: UInt64)

    // Named Paths
    //
    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let AdminStoragePath: StoragePath

    // totalSupply
    // The total number of CaaPass that have been minted
    //
    pub var totalSupply: UInt64

    // pre-defined metadata
    // 
    access(contract) var predefinedMetadata: {UInt64: Metadata}

    // Type Definitions
    // 
    pub struct Metadata {
        pub let name: String
        pub let description: String

        // mediaType: MIME type of the media
        // - image/png
        // - image/jpeg
        // - video/mp4
        // - audio/mpeg
        pub let mediaType: String

        // mediaHash: IPFS storage hash
        pub let mediaHash: String

        init(name: String, description: String, mediaType: String, mediaHash: String) {
            self.name = name
            self.description = description
            self.mediaType = mediaType
            self.mediaHash = mediaHash
        }
    }

    // NFT
    // An CAA art piece NFT
    //
    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
        // The token's ID
        pub let id: UInt64

        // The token's type
        pub let typeID: UInt64

        // Expose metadata
        pub fun getMetadata(): Metadata? {
            return CaaPass.predefinedMetadata[self.typeID]
        }

        // initializer
        //
        init(initID: UInt64, typeID: UInt64) {
            self.id = initID
            self.typeID = typeID
        }

        pub fun getViews(): [Type] {
            return [
                Type<Metadata>(),
                Type<MetadataViews.Serial>(),
                Type<MetadataViews.Display>(),
                Type<MetadataViews.Royalties>(),
                Type<MetadataViews.NFTCollectionData>(),
                Type<MetadataViews.NFTCollectionDisplay>(),
                Type<MetadataViews.ExternalURL>()
            ]
        }

        pub fun resolveView(_ view: Type): AnyStruct? {
            let metadata = self.getMetadata()
            if metadata == nil {
                return nil
            }
            switch view {
                case Type<Metadata>():
                    return metadata
                case Type<MetadataViews.Serial>():
                    return MetadataViews.Serial(
                        self.id
                    )
                case Type<MetadataViews.Royalties>():
                    return MetadataViews.Royalties([])
                case Type<MetadataViews.Display>():
                    return MetadataViews.Display(
                        name: metadata!.name,
                        description: metadata!.description,
                        thumbnail: MetadataViews.IPFSFile(cid: metadata!.mediaHash, path: nil)
                    )
                case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL("https://thing.fund/")
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: CaaPass.CollectionStoragePath,
                        publicPath: CaaPass.CollectionPublicPath,
                        providerPath: /private/caaPassCollection, // not exists for now
                        publicCollection: Type<&CaaPass.Collection{CaaPass.CollectionPublic}>(),
                        publicLinkedType: Type<&CaaPass.Collection{CaaPass.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Receiver,MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&CaaPass.Collection{CaaPass.CollectionPublic,NonFungibleToken.CollectionPublic,NonFungibleToken.Provider,MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <-CaaPass.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let square = MetadataViews.Media(
                        file: MetadataViews.HTTPFile(
                            url: "https://raw.githubusercontent.com/williampucs/THiNG.FUND-Minter/main/assets/squareImage.png"
                        ),
                        mediaType: "image/png"
                    )
                    let banner = MetadataViews.Media(
                        file: MetadataViews.HTTPFile(
                            url: "https://raw.githubusercontent.com/williampucs/THiNG.FUND-Minter/main/assets/bannerImage.png"
                        ),
                        mediaType: "image/png"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "THiNG.FUND Membership Badge",
                        description: "By holding THiNG.FUND’s membership badge NFTs in your blockchain wallet, you will become a member of THiNG.FUND club, grow with the creators and share the joy and beauty of Web3. At the same time, as a member, you will also get more privileges, including but not limited to obtaining airdrop gifts for creators’ works before obtaining THiNG.FUND limited collections or being invited to participate in offline/online events as a VIP.",
                        externalURL: MetadataViews.ExternalURL("https://thing.fund/"),
                        squareImage: square,
                        bannerImage: banner,
                        socials: {
                            "twitter": MetadataViews.ExternalURL("https://twitter.com/thing_fund"),
                            "discord": MetadataViews.ExternalURL("https://discord.gg/thingfund")
                        }
                    )
            }
            return nil
        }
    }

    pub resource interface CollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowCaaPass(id: UInt64): &CaaPass.NFT? {
            // If the result isn't nil, the id of the returned reference
            // should be the same as the argument to the function
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow CaaPass reference: The ID of the returned reference is incorrect"
            }
        }
    }

    // Collection
    // A collection of CaaPass NFTs owned by an account
    //
    pub resource Collection: NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, CollectionPublic, MetadataViews.ResolverCollection {
        // dictionary of NFT conforming tokens
        // NFT is a resource type with an `UInt64` ID field
        //
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}

        // withdraw
        // Removes an NFT from the collection and moves it to the caller
        //
        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing NFT")

            emit Withdraw(id: token.id, from: self.owner?.address)

            return <-token
        }

        // deposit
        // Takes a NFT and adds it to the collections dictionary
        // and adds the ID to the id array
        //
        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @CaaPass.NFT

            let id: UInt64 = token.id

            // add the new token to the dictionary which removes the old one
            let oldToken <- self.ownedNFTs[id] <- token

            emit Deposit(id: id, to: self.owner?.address)

            destroy oldToken
        }

        // getIDs
        // Returns an array of the IDs that are in the collection
        //
        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        // borrowViewResolver
        // 
        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let theNFT = nft as! &CaaPass.NFT
            return theNFT as &AnyResource{MetadataViews.Resolver}
        }

        // borrowNFT
        // Gets a reference to an NFT in the collection
        // so that the caller can read its metadata and call its methods
        //
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }

        // borrowCaaPass
        // Gets a reference to an NFT in the collection as a CaaPass,
        // exposing all of its fields.
        // This is safe as there are no functions that can be called on the CaaPass.
        //
        pub fun borrowCaaPass(id: UInt64): &CaaPass.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &CaaPass.NFT
            } else {
                return nil
            }
        }

        // destructor
        destroy() {
            destroy self.ownedNFTs
        }

        // initializer
        //
        init () {
            self.ownedNFTs <- {}
        }
    }

    // createEmptyCollection
    // public function that anyone can call to create a new empty collection
    //
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }

    // Admin
    // Resource that an admin or something similar would own to be
    // able to mint new NFTs
    //
    pub resource Admin {

        // mintNFT
        // Mints a new NFT with a new ID
        // and deposit it in the recipients collection using their collection reference
        //
        pub fun mintNFT(recipient: &{NonFungibleToken.CollectionPublic}, typeID: UInt64) {
            emit Minted(id: CaaPass.totalSupply)

            // deposit it in the recipient's account using their reference
            recipient.deposit(token: <-create CaaPass.NFT(initID: CaaPass.totalSupply, typeID: typeID))
            CaaPass.totalSupply = CaaPass.totalSupply + (1 as UInt64)
        }

        // batchMintNFT
        // Mints a batch of new NFTs
        // and deposit it in the recipients collection using their collection reference
        //
        pub fun batchMintNFT(recipient: &{NonFungibleToken.CollectionPublic}, typeID: UInt64, count: Int) {
            var index = 0
        
            while index < count {
                self.mintNFT(
                    recipient: recipient,
                    typeID: typeID
                )

                index = index + 1
            }
        }

        // registerMetadata
        // Registers metadata for a typeID
        //
        pub fun registerMetadata(index: UInt64, metadata: Metadata) {
            CaaPass.predefinedMetadata[index] = metadata
        }
    }

    // fetch
    // Get a reference to a CaaPass from an account's Collection, if available.
    // If an account does not have a CaaPass.Collection, panic.
    // If it has a collection but does not contain the itemID, return nil.
    // If it has a collection and that collection contains the itemID, return a reference to that.
    //
    pub fun fetch(_ from: Address, itemID: UInt64): &CaaPass.NFT? {
        let collection = getAccount(from)
            .getCapability(CaaPass.CollectionPublicPath)!
            .borrow<&CaaPass.Collection>()
            ?? panic("Couldn't get collection")
        // We trust CaaPass.Collection.borowCaaPass to get the correct itemID
        // (it checks it before returning it).
        return collection.borrowCaaPass(id: itemID)
    }

    // getMetadata
    // Get the metadata for a specific type of CaaPass
    //
    pub fun getMetadata(typeID: UInt64): Metadata? {
        return CaaPass.predefinedMetadata[typeID]
    }

    // initializer
    //
    init() {
        // Set our named paths
        self.CollectionStoragePath = /storage/caaPassCollection
        self.CollectionPublicPath = /public/caaPassCollection
        self.AdminStoragePath = /storage/caaPassAdmin

        // Initialize the total supply
        self.totalSupply = 0

        // Initialize predefined metadata
        self.predefinedMetadata = {}

        // Create a Admin resource and save it to storage
        let minter <- create Admin()
        self.account.save(<-minter, to: self.AdminStoragePath)

        emit ContractInitialized()
    }
}
 