import NonFungibleToken from 0x631e88ae7f1d7c20

pub contract ExampleNFTUser:NonFungibleToken {

    // total number of UserNFT
    pub var totalSupply: UInt64

    //uuid2expired, the deadline of the User
    access(contract) var NFTtabel: {UInt64:UInt64} 

    // Event that emitted when the contract is initialized
    pub event ContractInitialized()

    // Event that is emitted when a NFTUser is withdrawn,the id is NFT's uuid
    pub event Withdraw(id: UInt64, from: Address?)
    // Event that emitted when a NFTUser is deposited to a collection.
    pub event Deposit(id: UInt64, to: Address?)

    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath

    pub resource NFT: NonFungibleToken.INFT{
        // the NFTUser's id in this contract, the same as NFT's uuid
        pub let id: UInt64

        // the NFT's id, to sign the unique NFT in the flow mainnet
        pub let token_id: UInt64
        // the deadline of the NFT to lend
        pub let expired:UInt64
        // the Type of the NFT
        pub let type: String
        
        // display is a basic meta data of the NFT

        init(
            id: UInt64,
            token_id: UInt64,
            expired: UInt64,
            type: String
        ) {
            self.id = id
            self.token_id = token_id
            self.expired = expired
            self.type = type
        }
    }


    pub resource interface NFTUserCollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun getTypeIDs(type: String): [UInt64]?
        pub fun getTypes(): [String]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowUserNFT(uuid: UInt64): &ExampleNFTUser.NFT? {
            post {
                (result == nil) || (result?.id == uuid):
                    "Cannot borrow UserNFT reference: the ID of the returned reference is incorrect"
            }
        }
    }

    pub resource Collection: NFTUserCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic{
        // dictionary of NFTUser conforming tokens
        // uuid2user
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT} 
        
        // NFTType2ids
        // it can store the NFT ids in the given Type
        pub var type2ids: {String: [UInt64]} 

        init () {
            self.ownedNFTs <- {}
            self.type2ids = {}
        }

        // withdraw removes an NFTUser from the collection and moves it to the caller,withdrawID is the NFT's uuid
        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing NFT")
            let utoken <- token as! @ExampleNFTUser.NFT 
            var index = 0
            for id in self.type2ids[utoken.type]! {
                if (id == utoken.token_id) {
                    self.type2ids[utoken.type]!.remove(at: index)
                    break
                }
                index = index + 1
            }

            emit Withdraw(id: utoken.token_id, from: self.owner?.address)

            return <-utoken
        }

        // deposit takes a NFTUser and adds it to the collections dictionary
        // and adds the uuid to the id array
        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @ExampleNFTUser.NFT 

            let uuid: UInt64 = token.id

            let token_id: UInt64 = token.token_id

            let type: String = token.type

            if (self.type2ids[type] == nil) {
                self.type2ids[type] = []
            }
            if (!self.type2ids[type]!.contains(token_id)){
                self.type2ids[type]!.append(token_id)
            }

            // add the new token to the dictionary which removes the old one
            let oldToken <- self.ownedNFTs[uuid] <- token

            emit Deposit(id: uuid, to: self.owner?.address)

            destroy oldToken
        }

        //return collection inside uuids
        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        // return collection inside types
        pub fun getTypes(): [String] {
            return self.type2ids.keys
        }
        // getTypeIDs returns an array of the IDs that are in the collection of the special Type
        pub fun getTypeIDs(type: String): [UInt64]? {
            return self.type2ids[type]
        }

        // borrowNFT gets a reference to an NFT in the collection
        // so that the caller can read its metadata and call its methods
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }
 
        // borrowUserNFT gets a reference to an NFTUser in the collection
        // so that the caller can read its message like type and display
        pub fun borrowUserNFT(uuid: UInt64): &ExampleNFTUser.NFT? {
            if self.ownedNFTs[uuid] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[uuid] as auth &NonFungibleToken.NFT?)!
                return ref as! &ExampleNFTUser.NFT
            }
            return nil
        }

        destroy() {
            destroy self.ownedNFTs
            self.type2ids = {}
        }
    }

    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }

    pub fun createUserNFT(token: @NonFungibleToken.NFT, expired:UInt64,recipient: Capability<&AnyResource{NFTUserCollectionPublic}>): @NonFungibleToken.NFT{ 
        //judge if this NFT is lending and the input expired is correct
        pre {
            expired > getCurrentBlock().height : "wrong time for lending"
            ExampleNFTUser.NFTtabel[token.uuid] == nil || ExampleNFTUser.NFTtabel[token.uuid]! < getCurrentBlock().height : "this NFT is lending"
        }
        // get the NFT's meta data's struct Display
        let userToken <- create NFT(id: token.uuid, token_id: token.id, expired: expired, type: token.getType().identifier)
        ExampleNFTUser.NFTtabel[token.uuid] = expired
        let receiverReference = recipient.borrow() ?? panic("receiver has no UserCollection")
        receiverReference.deposit(token: <- userToken) 
        return <- token
    }

    // getExpired returns if the NFT is lending
    pub fun getExpired(uuid: UInt64): Bool{
        if(ExampleNFTUser.NFTtabel.containsKey(uuid)){
            return ExampleNFTUser.NFTtabel[uuid]! > getCurrentBlock().height ? true: false
        }
        else {
            return false
        }
    }

    init(){
      self.totalSupply = 0
      self.NFTtabel = {}
      self.CollectionStoragePath = /storage/NFTUserCollection
      self.CollectionPublicPath = /public/NFTUserCollection

      self.account.save(<-create Collection(), to: self.CollectionStoragePath)

      self.account.link<&ExampleNFTUser.Collection{NonFungibleToken.CollectionPublic, ExampleNFTUser.NFTUserCollectionPublic}>(
            self.CollectionPublicPath,
            target: self.CollectionStoragePath
        )

      emit ContractInitialized()
    }  
}
