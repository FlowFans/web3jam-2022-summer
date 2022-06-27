import NonFungibleToken from 0x01

pub contract Geohash: NonFungibleToken {

    // Events
    //
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event Minted(id: UInt64, metadata: String)
    pub event Burned(id: UInt64)

    // Named Paths
    //
    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath

    // totalSupply
    // The total number of Geohash that have been minted
    //
    pub var totalSupply: UInt64

    // A Geohash as an NFT
    //
    pub resource NFT: NonFungibleToken.INFT {

        pub let id: UInt64

        pub let metadata: String

        init(id: UInt64, metadata: String) {
            self.id = id
            self.metadata = metadata
        }

        pub fun tokenURI(): String {
            return self.metadata
        }
    }

    // This is the interface that users can cast their Geohash Collection as
    // to allow others to deposit Geohash into their Collection. It also allows for reading
    // the details of Geohash in the Collection.
    pub resource interface GeohashCollectionPublic {
        pub fun divide(id: UInt64)
        pub fun borrowGeohash(id: UInt64): &Geohash.NFT? {
            // If the result isn't nil, the id of the returned reference
            // should be the same as the argument to the function
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow Geohash reference: The ID of the returned reference is incorrect"
            }
        }
    }

    // Collection
    // A collection of Geohash NFTs owned by an account
    //
    pub resource Collection: GeohashCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic {
        pub fun divide(id: UInt64) {
            let token = self.borrowGeohash(id: id) ?? panic("Could not borrow a reference to the owner's collection")
            self.burnNFT(id: id)
            self.batchMintNFT(origin: token.metadata)
        }

        pub fun initMint() {
            assert(Geohash.totalSupply == 0, message:"Geohash has inited")
            self.batchMintNFT(origin: "")
        }

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
            let token <- token as! @Geohash.NFT

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

        // borrowNFT
        // Gets a reference to an NFT in the collection
        //
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }

        // borrowGeohash
        // Gets a reference to an NFT in the collection as a Geohash,
        // This is safe as there are no functions that can be called on the Geohash.
        //
        pub fun borrowGeohash(id: UInt64): &Geohash.NFT? {
            if self.ownedNFTs[id] != nil {
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &Geohash.NFT
            } else {
                return nil
            }
        }

        // batchMintNFT
        // Mints a new NFT with a new ID
        //
        priv fun batchMintNFT(
            origin: String,
        ) {
            let alphabet = [ "0","1","2","3","4","5","6","7",
                            "8","9","b","c","d","e","f","g",
                            "h","j","k","m","n","p","q","r",
                            "s","t","u","v","w","x","y","z"]
            for item in alphabet {
                let metadata = origin.concat(item)
                self.deposit(token: <-create Geohash.NFT(id: Geohash.totalSupply, metadata: metadata))
                Geohash.totalSupply = Geohash.totalSupply + (1 as UInt64)
                emit Minted(
                    id: Geohash.totalSupply,
                    metadata: metadata
                )
            }
        }

        priv fun burnNFT(id: UInt64) {
            emit Burned(id: id)

            let token <- self.withdraw(withdrawID: id)

            destroy token
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

    // fetch
    // Get a reference to a Geohash from an account's Collection, if available.
    // If an account does not have a Geohash.Collection, panic.
    // If it has a collection but does not contain the id, return nil.
    // If it has a collection and that collection contains the id, return a reference to that.
    //
    pub fun fetch(_ from: Address, id: UInt64): &Geohash.NFT? {
        let collection = getAccount(from)
            .getCapability(Geohash.CollectionPublicPath)!
            .borrow<&Geohash.Collection{Geohash.GeohashCollectionPublic}>()
            ?? panic("Couldn't get collection")
        // We trust Geohash.Collection.borowGeohash to get the correct id
        // (it checks it before returning it).
        return collection.borrowGeohash(id: id)
    }

    // initializer
    //
    init() {
        // Set our named paths
        self.CollectionStoragePath = /storage/GeohashCollectionV1
        self.CollectionPublicPath = /public/GeohashCollectionV1

        // Initialize the total supply
        self.totalSupply = 0

        emit ContractInitialized()
    }
}
