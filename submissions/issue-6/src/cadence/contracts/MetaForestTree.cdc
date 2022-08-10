import NonFungibleToken from 0x631e88ae7f1d7c20
pub contract MetaForestTree : NonFungibleToken {
    // Events
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    pub event NFTDestroyed(id: UInt64)
    pub event NFTMinted(nftId: UInt64, templateId: UInt64, mintNumber: UInt64)
    pub event TemplateCreated(templateId: UInt64, maxSupply: UInt64)

    // Paths
    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    
    // Latest template-id
    pub var lastIssuedTemplateId: UInt64

    // Total supply of all NFTs that are minted using this contract
    pub var totalSupply: UInt64
    // A dictionary that stores all Templates against it's template-id.
    access(self) var allTemplates: {UInt64: Template}
    // A dictionary that stores all NFTs against it's nft-id.
    access(self) var allNFTs: {UInt64: NFTData}

    // A structure that contain all the data and methods related to Template
    pub struct Template {
        pub let templateId: UInt64
        pub var maxSupply: UInt64
        pub var issuedSupply: UInt64
        access(contract) var baseUri: String
        access(contract) var tokenUri: String

        init(maxSupply: UInt64, baseUri: String, tokenUri: String) {
            pre {
                maxSupply > 0 : "MaxSupply must be greater than zero"
                baseUri.length > 0 : "base Uri should be valid"
                tokenUri.length > 0: "token Uri should be valid"
            }
            
            self.templateId = MetaForestTree.lastIssuedTemplateId
            self.maxSupply = maxSupply
            self.baseUri = baseUri
            self.tokenUri = tokenUri
            self.issuedSupply = 0
        }

        // a method to set new base uri
        access(contract) fun setBaseUri(baseUri: String) {
            pre {
                self.baseUri != baseUri : "please provide new uri"
            }
            self.baseUri = baseUri
        }
        // a method to set new token uri
        access(contract) fun setTokenUri(tokenUri: String) {
            pre {
                self.tokenUri != tokenUri : "plese provide new uri"
            }
            self.tokenUri  = tokenUri
        }
        pub fun getBaseUri(): String {
            return self.baseUri
        }
        pub fun getTokenUri(): String {
            return self.tokenUri
        }
        // a method to increment issued supply for template
        access(contract) fun incrementIssuedSupply(): UInt64 {
            pre {
                self.issuedSupply < self.maxSupply: "Template reached max supply"
            }   
            self.issuedSupply = self.issuedSupply + 1
            return self.issuedSupply
        }

    }
    // A structure that link template and mint-no of NFT
    pub struct NFTData {
        pub let templateId: UInt64
        pub let mintNumber: UInt64

        init(templateId: UInt64, mintNumber: UInt64) {
            self.templateId = templateId
            self.mintNumber = mintNumber
        }
    }
    // The resource that represents the MetaForestTree NFTs
    // 
    pub resource NFT: NonFungibleToken.INFT {
        pub let id: UInt64
        access(contract) var data: NFTData

        init(templateId: UInt64, mintNumber: UInt64) {
            MetaForestTree.totalSupply = MetaForestTree.totalSupply + 1
            self.id = MetaForestTree.totalSupply
            MetaForestTree.allNFTs[self.id] = NFTData(templateId: templateId, mintNumber: mintNumber)
            self.data = MetaForestTree.allNFTs[self.id]!
            emit NFTMinted(nftId: self.id, templateId: templateId, mintNumber: mintNumber)
        }
        destroy(){
            emit NFTDestroyed(id: self.id)
        }
    }
    pub resource interface MetaForestTreeCollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowMetaForestTree(id: UInt64): &MetaForestTree.NFT? {
            // If the result isn't nil, the id of the returned reference
            // should be the same as the argument to the function
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow MetaForestTree reference: The ID of the returned reference is incorrect"
            }
        }
    }

    // Collection is a resource that every user who owns NFTs 
    // will store in their account to manage their NFTS
    //
    pub resource Collection: MetaForestTreeCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic {
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}

        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token  <- self.ownedNFTs.remove(key: withdrawID) 
                ?? panic("Cannot withdraw: template does not exist in the collection")
            emit Withdraw(id: token.id, from: self.owner?.address)
            return <-token
        }

        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @MetaForestTree.NFT
            let id = token.id
            let oldToken <- self.ownedNFTs[id] <- token
            if self.owner?.address != nil {
                emit Deposit(id: id, to: self.owner?.address)
            }
            destroy oldToken
        }

        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }
        
        pub fun borrowMetaForestTree(id: UInt64): &MetaForestTree.NFT? {
            if self.ownedNFTs[id] != nil {
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &MetaForestTree.NFT
            }
            else{
                return nil
            }
        }

        init() {
            self.ownedNFTs <- {}
        }
        
        destroy () {
            destroy self.ownedNFTs
        }
    }
    //method to create new Template, only access by the verified user
    access(account) fun createTemplate(maxSupply: UInt64, baseUri: String, tokenUri: String) {
        let newTemplate = Template(maxSupply: maxSupply, baseUri: baseUri, tokenUri: tokenUri)
        MetaForestTree.allTemplates[MetaForestTree.lastIssuedTemplateId] = newTemplate
        emit TemplateCreated(templateId: MetaForestTree.lastIssuedTemplateId, maxSupply: maxSupply)
        MetaForestTree.lastIssuedTemplateId = MetaForestTree.lastIssuedTemplateId + 1
    }
    //method to mint NFT, only access by the verified user
    access(account) fun mintNFT(templateId: UInt64, account: Address) {
        pre {
            account != nil: "invalid receipt Address"
            MetaForestTree.allTemplates[templateId] != nil: "Template Id must be valid"
        }
        let receiptAccount = getAccount(account)
        let recipientCollection = receiptAccount
            .getCapability(MetaForestTree.CollectionPublicPath)
            .borrow<&{MetaForestTree.MetaForestTreeCollectionPublic}>()
            ?? panic("Could not get receiver reference to the NFT Collection")
        var newNFT: @NFT <- create NFT(templateId: templateId, mintNumber: MetaForestTree.allTemplates[templateId]!.incrementIssuedSupply())
        recipientCollection.deposit(token: <-newNFT)
    }
    //method to create empty Collection
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create MetaForestTree.Collection()
    }
    
    access(account) fun updateBaseUri(templateId: UInt64, baseUri: String){
        pre {
            baseUri.length > 0 : "base Uri should be valid"
        }
        self.allTemplates[templateId]!.setBaseUri(baseUri: baseUri)
    }
    access(account) fun updateTokenUri(templateId: UInt64, tokenUri: String){
        pre {
            tokenUri.length > 0 : "token Uri should be valid"
        }
        self.allTemplates[templateId]!.setTokenUri(tokenUri: tokenUri)
    }


    //method to get all templates
    pub fun getAllTemplates(): {UInt64: Template} {
        return MetaForestTree.allTemplates
    }

    //method to get template by id
    pub fun getTemplateById(templateId: UInt64): Template {
        pre {
            MetaForestTree.allTemplates[templateId] != nil: "Template id does not exist"
        }
        return MetaForestTree.allTemplates[templateId]!
    } 
    //method to get nft-data by id
    pub fun getNFTDataById(nftId: UInt64): NFTData {
        pre {
            MetaForestTree.allNFTs[nftId] != nil: "nft id does not exist"
        }
        return MetaForestTree.allNFTs[nftId]!
    }
    
    init(){
        self.lastIssuedTemplateId = 1
        self.totalSupply = 0
        self.allTemplates = {}
        self.allNFTs = {}
        self.CollectionStoragePath = /storage/MetaForestTreeCollection
        self.CollectionPublicPath = /public/MetaForestTreeCollection
    }
}