import NonFungibleToken from "./NonFungibleToken.cdc"
import FlowToken from "./FlowToken.cdc"
import FungibleToken from "./FungibleToken.cdc"

pub contract Racenumber:NonFungibleToken {
    pub var totalSupply:UInt64
    pub let GamesStoragePath:StoragePath
    pub let GamesPublicPath:PublicPath
    pub let NumberNFTCollectionStoragePath: StoragePath
    pub let NumberNFTCollectionPublicPath:PublicPath
    pub let ThemeNFTCollectionStoragePath: StoragePath
    pub let ThemeNFTCollectionPublicPath:PublicPath

    access(contract) var allGames:{UInt64:GameDetail}  //每个主办方办的所有比赛,通过Games的Capability找到每个Game
    pub event GameCreated(hostAddr:Address,uid:UInt64)

    //Game相关Metadata
    pub struct GameDetail{
        pub var gameName:String
        pub var timestamp:UInt32
        pub var issues:UInt64
        pub(set) var mintedNum:Int
        pub var uid:UInt64
        pub var gameId:UInt64
        pub var hostAddr:Address
        pub var price:UFix64
        pub(set) var imageHash:String
        pub(set) var templateType:String
        pub(set) var gameType:String
        pub(set) var slogan:String
        
        init(name:String, timestamp: UInt32,issues:UInt64, mintedNum:Int, uid:UInt64,gameId:UInt64,hostAddr:Address,price:UFix64,imageHash:String,templateType:String,gameType:String,slogan:String) {
            self.hostAddr = hostAddr
            self.gameName = name
            self.issues = issues
            self.timestamp = timestamp
            self.uid = uid
            self.mintedNum = mintedNum
            self.gameId = gameId
            self.price = price
            self.imageHash = imageHash
            self.templateType = templateType
            self.gameType = gameType
            self.slogan = slogan

        }
    }
    //Number NFT相关Metadata
    pub struct NumberNFTMeta{
        pub let id:UInt64
        pub let GameId:UInt64
        pub let name:String
        pub let host:Address

        init(id:UInt64,GameId:UInt64,name:String, host:Address){
            self.id = id
            self.GameId = GameId
            self.name = name
            self.host = host
        }
    }
    //theme相关metadata
    pub struct ThemeMeta{
        pub var gameDetail:GameDetail
        pub var num:UInt64
        pub let background:String

        init(gameDetail:GameDetail,num:UInt64,background:String){
            self.gameDetail = gameDetail
            self.num = num
            self.background = background
        }
    }

    //不用触发事件，接口必须
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)

    //B端创建比赛的模板
    pub resource interface  GamesPublic {
        pub var totalGames:UInt64
        pub fun getAllGames():{UInt64:GameDetail}
        pub fun borrowPublicGameRef(GameUId: UInt64): &Game{GamePublic}
    }

    pub resource Games:GamesPublic {
        pub var totalGames:UInt64
        access(contract) var Games: @{UInt64:Game}
        pub fun createGame(name:String, issues:UInt64, timestamp: UInt32, hostAddr: Address): UInt64 {
            let gameId = self.totalGames;
            let price = 1.0
            let game <- create Game(name:name,issues:issues, timestamp: timestamp, hostAddr: hostAddr,gameId:gameId,price:price);
            let id = game.id
            self.Games[game.id] <-! game
            assert(!Racenumber.allGames.containsKey(id), message: "Game id is not unique")
            let _game = (&self.Games[id] as &Game?)!
            let _GameDetail = GameDetail(name:_game.name, timestamp: _game.timestamp,issues:_game.issues, mintedNum:0, uid:_game.id,gameId:_game.gameId,hostAddr:_game.hostAddr,price:_game.price,imageHash:_game.imageHash,templateType:_game.templateType,gameType:_game.gameType,slogan:_game.slogan)
            Racenumber.allGames.insert(key: id, _GameDetail)
            self.totalGames = self.totalGames + 1;
            emit GameCreated(hostAddr:hostAddr,uid:id)
            return id;
        }
        
        pub fun getAllGames():{UInt64:GameDetail} {
            let res: {UInt64:GameDetail} = {}
            for id in self.Games.keys {
                let _game = (&self.Games[id] as &Game?)!
                let mintedNum = _game.mintedAddrs.length
                let _GameDetail = GameDetail(name:_game.name, timestamp: _game.timestamp,issues:_game.issues, mintedNum:mintedNum, uid:_game.id,gameId:_game.gameId,hostAddr:_game.hostAddr,price:_game.price,imageHash:_game.imageHash,templateType:_game.templateType,gameType:_game.gameType,slogan:_game.slogan)
                res[id] = _GameDetail;
            }
            return res
        }
        
        pub fun borrowGameRef(GameUId: UInt64): &Game{
          pre {
                self.Games[GameUId]!= nil:"Game not exist!"
            }
            return (&self.Games[GameUId] as &Game?)!
        }
        pub fun borrowPublicGameRef(GameUId: UInt64): &Game{GamePublic} {
            pre {
                self.Games[GameUId]!= nil:"Game not exist!"
            }
            return (&self.Games[GameUId] as &Game{GamePublic}?)!
        }

        init() {
            self.totalGames = 0
            self.Games <- {}
        }

        destroy()  {
            destroy self.Games
        }
    }
    pub resource interface GamePublic{
        pub fun mintNumber(num:UInt64, recipient: &Collection{NonFungibleToken.CollectionPublic}, flowVault:@FlowToken.Vault):UInt64
        pub fun mintTheme(collectionCap:Capability<&Collection{CollectionPublic}>,gameRef:&Game{GamePublic},num:UInt64,background:String,recipient: &ThemeCollection{ThemeCollectionPublic})
        pub fun getMintedNftList():[UInt64]
        pub fun canMintTheme(addr:Address) :Bool
        
        pub var price:UFix64
        pub var imageHash:String
        pub var templateType:String
        pub var gameType:String
        pub var slogan:String
    }
    pub resource Game:GamePublic {
        pub var id:UInt64;
        pub var issues:UInt64;
        access(contract) var minted:{UInt64:Address};
        access(contract) var mintedAddrs:[Address];
        access(contract) var themeMintedAddrs:[Address];
        pub var name: String;
        pub var timestamp: UInt32;
        pub let hostAddr: Address
        pub let gameId: UInt64;
        pub var price: UFix64;
        pub var imageHash:String
        pub var templateType:String
        pub var gameType:String
        pub var slogan:String
        
        //用户mint
        pub fun mintNumber(num:UInt64, recipient: &Collection{NonFungibleToken.CollectionPublic}, flowVault:@FlowToken.Vault):UInt64 {
            pre {
                num < self.issues: "This number exceed the issues!"
                !self.minted.containsKey(num) : "This number has been minted!"
                flowVault.balance >= self.price : "Your balance is not enough!"
            }
            let addr:Address = recipient.owner!.address;
            assert(!self.mintedAddrs.contains(addr),message:"Your address has minted!")
            let token <- create NFT(
                host: self.hostAddr,
                gameUId:self.id,
                GameId: self.gameId,
                name: self.name,
                num:num
                )
            let id = (&token as &NFT).id
            let hostVault = getAccount(self.hostAddr).getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(FlowToken.FlowTokenVaultPublic).borrow() ?? panic("Host addr Vault not found")
            hostVault.deposit(from: <-flowVault)
            self.minted.insert(key:num,addr);
            self.mintedAddrs.append(addr)
            recipient.deposit(token: <-token)
            //update Mintednum
            let _game = &Racenumber.allGames[self.id]! as &GameDetail
            _game.mintedNum = self.mintedAddrs.length
            return id
        }
        pub fun mintTheme(collectionCap:Capability<&Collection{CollectionPublic}>,gameRef:&Game{GamePublic},num:UInt64,background:String,recipient: &ThemeCollection{ThemeCollectionPublic}){

            let addr = recipient.owner!.address;
            assert(!self.themeMintedAddrs.contains(addr),message:"Your address has minted theme NFT!")
            let imageHash = gameRef.imageHash
            let templateType = gameRef.templateType
            let gameType = gameRef.gameType
            let slogan = gameRef.slogan
            let nft <- create ThemeNFT(gameUId: self.id, name: self.name,host:self.hostAddr,num:num,imageHash:imageHash,templateType:templateType, gameType:gameType,slogan:slogan,background:background,collectionCap:collectionCap)
            self.themeMintedAddrs.append(addr)
            recipient.deposit(token: <-nft)
        }

        pub fun canMintTheme(addr:Address) :Bool{
            return self.mintedAddrs.contains(addr)
        }

        init(name:String,issues:UInt64, timestamp: UInt32, hostAddr: Address, gameId:UInt64,price:UFix64) {
            self.name = name;
            self.issues = issues;
            self.timestamp = timestamp;
            self.hostAddr = hostAddr;
            self.gameId = gameId;
            self.minted = {};
            self.id = self.uuid
            self.mintedAddrs = []
            self.themeMintedAddrs = []
            self.price = price
            self.imageHash = ""
            self.templateType = ""
            self.gameType = ""
            self.slogan = ""

        }
        
        pub fun setImgAndTypes(imageHash:String,templateType:String, gameType:String,slogan:String) {
            let id = self.uuid
            let ref = &Racenumber.allGames[id]! as &GameDetail
            ref.imageHash = imageHash;
            ref.gameType = gameType;
            ref.slogan = slogan;
            ref.templateType = templateType;
            self.imageHash = imageHash;
            self.gameType = gameType
            self.slogan = slogan
            self.templateType = templateType;
        }

        pub fun getMintedNftList():[UInt64]{
            return self.minted.keys
        }
        
        destroy (){

        }

    }

//////////用户存储部分//////////////////
    pub resource NFT:NonFungibleToken.INFT {
        pub let id:UInt64
        pub let GameId:UInt64
        pub let gameUId:UInt64
        pub let name:String
        pub let host:Address
        pub let num:UInt64
        pub let gamesCap: Capability<&Games{GamesPublic}>
        init(host:Address, gameUId:UInt64,GameId:UInt64, name:String,num:UInt64){
            //校验
            self.id = self.uuid
            self.num = num
            self.GameId = GameId
            self.gameUId = gameUId
            self.name = name
            self.host = host
            let gamesRef =  getAccount(host).getCapability<&Games{GamesPublic}>(Racenumber.GamesPublicPath)
            self.gamesCap = gamesRef
        }     
    }

    pub resource interface CollectionPublic{
        pub fun deposit(token:@NonFungibleToken.NFT)
        pub fun getIDs():[UInt64]
        pub fun borrowNFT(id:UInt64): &NonFungibleToken.NFT
        pub fun borrowNumberNFT(id:UInt64):&NFT
    }

    pub resource Collection: NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, CollectionPublic {
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}  //ID实际上对应的是GameId

        pub fun withdraw(withdrawID:UInt64):@NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("You donnot own this NFT")
            let nft <- token as! @NFT
            return <- nft
        }

        pub fun deposit(token:@NonFungibleToken.NFT) {
            let nft <- token as! @NFT;
            let id = nft.gameUId;
            self.ownedNFTs[id]<-! nft;
        }

        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys;
        }

        pub fun borrowNFT(id:UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }

        pub fun borrowNumberNFT(id:UInt64): &NFT{
            pre{
                self.ownedNFTs[id]!=nil: "Number NFT doesn't exist!"
            }
            let ref = (&self.ownedNFTs[id] as auth&NonFungibleToken.NFT?)!
            return ref as! &NFT
        }

        init(){
            self.ownedNFTs <- {}
        }
        destroy (){
            destroy self.ownedNFTs
        }
    }

     pub resource ThemeNFT:NonFungibleToken.INFT {
        pub let id:UInt64
        pub let gameUId:UInt64
        pub let name:String
        pub let host:Address
        pub let num: UInt64
        pub let imageHash:String
        pub let templateType:String
        pub let gameType:String
        pub let slogan:String
        pub let background:String
        pub let collectionCap:Capability<&Collection{CollectionPublic}>
        init(gameUId:UInt64,name:String,host:Address,num:UInt64,imageHash:String,templateType:String, gameType:String,slogan:String,background:String,collectionCap:Capability<&Collection{CollectionPublic}>){ //num:UInt64,imageHash:String,templateType:String, gameType:String,slogan:String
            self.id = self.uuid
            self.gameUId = gameUId
            self.name = name
            self.host = host
            self.num = num
            self.imageHash = imageHash
            self.templateType = templateType
            self.gameType = gameType
            self.slogan = slogan
            self.background = background
            self.collectionCap = collectionCap
        }
    }

    pub resource interface ThemeCollectionPublic {
        pub fun deposit(token:@ThemeNFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id:UInt64): &ThemeNFT
    }

    pub resource ThemeCollection:ThemeCollectionPublic {
        pub var ownedNFTs: @{UInt64: ThemeNFT}

        pub fun withdraw(withdrawID:UInt64):@ThemeNFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("You donnot own this NFT")
            return <- token
        }

        pub fun deposit(token:@ThemeNFT) {
            let nft <- token as! @ThemeNFT;
            let id = nft.id;
            self.ownedNFTs[id]<-! nft;
        }

        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys;
        }

        pub fun borrowNFT(id:UInt64): &ThemeNFT {
            pre {
                self.ownedNFTs[id]!=nil: "Theme NFT doesn't exist!"
            }
            return (&self.ownedNFTs[id] as &ThemeNFT?)!
        }

        init(){
            self.ownedNFTs <- {}
        }
        destroy (){
            destroy self.ownedNFTs
        }
    }

    pub fun createEmptyCollection(): @Collection {
        return <- create Collection()
    }

    pub fun createEmptyThemeCollection(): @ThemeCollection {
        return <- create ThemeCollection()
    }

    pub fun createEmptyGames():@Games{
        return <- create Games();
    }

    //一些查询功能
    pub fun getAllGames():{UInt64:GameDetail}{
        return self.allGames
    }

    pub fun getGameById(id:UInt64):GameDetail{
        pre {
            self.allGames[id] != nil:"Game not exist!"
        }
        return self.allGames[id]!
    }

    init() {
        self.GamesStoragePath = /storage/GamesStoragePath
        self.GamesPublicPath = /public/GamesStoragePath
        self.NumberNFTCollectionStoragePath = /storage/NumberNFTCollectionStoragePath
        self.NumberNFTCollectionPublicPath = /public/NumberNFTCollectionPublicPath
        self.ThemeNFTCollectionStoragePath = /storage/ThemeNFTCollectionStoragePath
        self.ThemeNFTCollectionPublicPath = /public/ThemeNFTCollectionPublicPath
        self.totalSupply = 0
        self.allGames = {}
    }

}

