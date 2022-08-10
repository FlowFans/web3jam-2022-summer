import NonFungibleToken from 0xf5c21ffd3438212b

pub contract WakandaPass: NonFungibleToken {

    pub var totalSupply: UInt64

    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event Minted(id: UInt64, metadata: String)

    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath

    pub resource NFT: NonFungibleToken.INFT {
        pub let id: UInt64

        pub let name: String
        pub let description: String
        pub let metadata: String

        init(
            id: UInt64,
            metadata: String
        ) {
            self.id = id
            self.name = "WakandaPass #".concat(metadata)
            self.description = "Welcome to Wakanda Metaverse!"
            self.metadata = metadata
        }
    }

    pub resource interface WakandaPassCollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowWakandaPass(id: UInt64): &WakandaPass.NFT? {
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow WakandaPass reference: the ID of the returned reference is incorrect"
            }
        }
    }

    pub resource Collection: WakandaPassCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic {
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
            let token <- token as! @WakandaPass.NFT

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

        pub fun borrowWakandaPass(id: UInt64): &WakandaPass.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &WakandaPass.NFT
            }

            return nil
        }

        pub fun initWakandaPass() {
            assert(WakandaPass.totalSupply == 0, message: "WakandaPass already initialized")
            self.batchMintNFT(origin: "")
        }

        pub fun divide(id: UInt64) {
            let origin = self.borrowWakandaPass(id: id) ?? panic("Could not borrow a reference to the owner's collection")
            self.batchMintNFT(origin: origin.metadata)
            self.burnNFT(id: id)
        }

        priv fun batchMintNFT(origin: String) {
             let ALPHABET = [ "0","1","2","3","4","5","6","7",
                              "8","9","b","c","d","e","f","g",
                              "h","j","k","m","n","p","q","r",
                              "s","t","u","v","w","x","y","z"]
             for item in ALPHABET {
                 self.deposit(token: <-create WakandaPass.NFT(
                  id: WakandaPass.totalSupply,
                  metadata: origin.concat(item)
                 )
                 )
                 WakandaPass.totalSupply = WakandaPass.totalSupply + (1 as UInt64)
                 emit Minted(
                     id: WakandaPass.totalSupply,
                     metadata: origin.concat(item)
                 )
             }
        }

        priv fun burnNFT(id: UInt64) {
            let token <- self.withdraw(withdrawID: id)

            destroy token
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

    // public function that anyone can call to create a new empty collection
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }

    init() {
        // Initialize the total supply
        self.totalSupply = 0

        // Set the named paths
        self.CollectionStoragePath = /storage/WakandaPassCollection
        self.CollectionPublicPath = /public/WakandaPassCollection

        // Create a Collection resource and save it to storage
        let collection <- create Collection()
        self.account.save(<-collection, to: self.CollectionStoragePath)

        // create a public capability for the collection
        self.account.link<&WakandaPass.Collection{NonFungibleToken.CollectionPublic, WakandaPass.WakandaPassCollectionPublic}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )

        emit ContractInitialized()
    }
}
