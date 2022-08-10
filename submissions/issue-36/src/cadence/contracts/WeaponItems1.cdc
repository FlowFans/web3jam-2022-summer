import NonFungibleToken from "./NonFungibleToken.cdc"
import MetadataViews from "./MetadataViews.cdc"

// import NonFungibleToken from 0x631e88ae7f1d7c20
// import MetadataViews from 0x631e88ae7f1d7c20

pub contract WeaponItems1: NonFungibleToken {

    // Events
    //
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event Minted(id: UInt64, name: String, attack: UInt8, defence: UInt8, url: String)

    // Named Paths
    //
    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let MinterStoragePath: StoragePath
    pub let MinterPublicPath: PublicPath

    // totalSupply
    // The total number of WeaponItems that have been minted
    //
    pub var totalSupply: UInt64

    // A Weapon Item as an NFT
    //
    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {

        pub let id: UInt64

        pub let itemName: String
        pub let attack: UInt8
        pub let defence: UInt8
        pub let url: String

        init(id: UInt64, name: String, attack: UInt8, defence: UInt8, url: String) {
          self.id = id
          self.itemName = name
          self.attack = attack
          self.defence = defence
          self.url = url
        }

        pub fun name(): String {
          return self.itemName
        }

        pub fun description(): String {
          return "weapon ".concat(self.itemName)
        }

        pub fun getViews(): [Type] {
            return [
                Type<MetadataViews.Display>()
            ]
        }

        pub fun resolveView(_ view: Type): AnyStruct? {
            switch view {
                case Type<MetadataViews.Display>():
                    return MetadataViews.Display(
                        name: self.name(),
                        description: self.description(),
                        thumbnail: MetadataViews.HTTPFile(
                            url: self.url, 
                        )
                    )
            }

            return nil
        }
    }

    // This is the interface that users can cast their WeaponItems Collection as
    // to allow others to deposit WeaponItems into their Collection. It also allows for reading
    // the details of WeaponItems in the Collection.
    pub resource interface WeaponItemsCollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowWeaponItem(id: UInt64): &WeaponItems1.NFT? {
            // If the result isn't nil, the id of the returned reference
            // should be the same as the argument to the function
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow WeaponItem reference: The ID of the returned reference is incorrect"
            }
        }
    }

    // Collection
    // A collection of WeaponItem NFTs owned by an account
    //
    pub resource Collection: WeaponItemsCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
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
            let token <- token as! @WeaponItems1.NFT

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
        // so that the caller can read its metadata and call its methods
        //
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }

        // borrowWeaponItem
        // Gets a reference to an NFT in the collection as a WeaponItem,
        // This is safe as there are no functions that can be called on the WeaponItem.
        //
        pub fun borrowWeaponItem(id: UInt64): &WeaponItems1.NFT? {
            if self.ownedNFTs[id] != nil {
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &WeaponItems1.NFT
            } else {
                return nil
            }
        }

        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let weaponItem = nft as! &WeaponItems1.NFT
            return weaponItem as &AnyResource{MetadataViews.Resolver}
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

    pub resource interface NFTMinterPublic {
        pub fun mintNFT(
            recipient: &{NonFungibleToken.CollectionPublic}, 
            name: String,
            attack: UInt8,
            defence: UInt8,
            url: String
        )
    }

    // NFTMinter
    // Resource that an admin or something similar would own to be
    // able to mint new NFTs
    //
    pub resource NFTMinter: NFTMinterPublic {

        // mintNFT
        // Mints a new NFT with a new ID
        // and deposit it in the recipients collection using their collection reference
        //
        pub fun mintNFT(
            recipient: &{NonFungibleToken.CollectionPublic}, 
            name: String,
            attack: UInt8,
            defence: UInt8,
            url: String
        ) {
            // deposit it in the recipient's account using their reference
            recipient.deposit(token: <-create WeaponItems1.NFT(id: WeaponItems1.totalSupply, name: name, attack: attack, defence: defence, url: url))

            emit Minted(
                id: WeaponItems1.totalSupply,
                name: name,
                attack: attack,
                defence: defence,
                url: url
            )

            WeaponItems1.totalSupply = WeaponItems1.totalSupply + (1 as UInt64)
        }
    }

    // fetch
    // Get a reference to a WeaponItem from an account's Collection, if available.
    // If an account does not have a WeaponItems.Collection, panic.
    // If it has a collection but does not contain the itemID, return nil.
    // If it has a collection and that collection contains the itemID, return a reference to that.
    //
    pub fun fetch(_ from: Address, itemID: UInt64): &WeaponItems1.NFT? {
        let collection = getAccount(from)
            .getCapability(WeaponItems1.CollectionPublicPath)!
            .borrow<&WeaponItems1.Collection{WeaponItems1.WeaponItemsCollectionPublic}>()
            ?? panic("Couldn't get collection")
        // We trust WeaponItems.Collection.borowWeaponItem to get the correct itemID
        // (it checks it before returning it).
        return collection.borrowWeaponItem(id: itemID)
    }

    // FREE MINT FOR TEST
    pub fun getMinter(): @NFTMinter {
        return <-create NFTMinter()
    }

    // initializer
    //
    init() {
        // Set our named paths
        self.CollectionStoragePath = /storage/weaponItemsFreeCollection1
        self.CollectionPublicPath = /public/weaponItemsFreeCollection1
        self.MinterStoragePath = /storage/weaponItemsFreeMinter1
        self.MinterPublicPath = /public/weaponItemsFreeMinter1

        // Initialize the total supply
        self.totalSupply = 0

        // Create a Minter resource and save it to storage
        let minter <- create NFTMinter()
        self.account.save(<-minter, to: self.MinterStoragePath)
        // FOR FREE MINT
        self.account.link<&NFTMinter{NFTMinterPublic}>(self.MinterPublicPath, target: self.MinterStoragePath)

        emit ContractInitialized()
    }
}
