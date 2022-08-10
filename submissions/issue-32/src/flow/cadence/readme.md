# 接口说明

## 项目方承办的比赛Games相关接口

### 1.Games资源对普通用户开放接口
```
pub resource interface  GamesPublic {
    pub var totalGames:UInt64
    pub fun getAllGames():{UInt64:GameDetail}  //获取主办方办的所有比赛
    pub fun borrowPublicGameRef(GameUId: UInt64): &Game{GamePublic} //根据GameUId获取主办方的某个比赛
    ...
}
```

### 2.Games资源对项目方额外开放的接口
```
pub resource Games:GamesPublic {
    access(contract) var Games: @{UInt64:Game}  //存储所有比赛
    pub fun createGame(name:String, issues:UInt64, timestamp: UInt32, hostAddr: Address): UInt64 //创建比赛
    pub fun borrowGameRef(GameUId: UInt64): &Game //根据GameUId获取自己承办的单个比赛的引用
    ...
}
```

### 3.单个比赛(Game)对普通用户开放的接口
```
pub resource interface GamePublic{
    access(contract) var minted:{UInt64:Address};  //存储mint过的号牌号码
    access(contract) var mintedAddrs:[Address];  //存储mint过号牌NFT的用户地址
    access(contract) var themeMintedAddrs:[Address];  //存储mint过主题NFT的用户地址
    ...
    pub fun mintNumber(num:UInt64, recipient: &Collection{NonFungibleToken.CollectionPublic}, flowVault:@FlowToken.Vault):UInt64   //根据号牌价格mint单个号牌NFT
    pub fun mintTheme(collectionCap:Capability<&Collection{CollectionPublic}>,gameRef:&Game{GamePublic},num:UInt64,background:String,recipient: &ThemeCollection{ThemeCollectionPublic})  //有号牌的用户有权限mint主题NFT
    pub fun getMintedNftList():[UInt64] //获取已mint的号牌
    pub fun canMintTheme(addr:Address) :Bool //查询是否可以mint主题NFT的权限
    ...
}
```

### 4.单个比赛(Game)对项目方额外开放的接口
```
pub resource Game:GamePublic {
    ...
    pub fun setImgAndTypes(imageHash:String,templateType:String, gameType:String,slogan:String) //设置主题NFT的元数据信息
    ...
}
```

## 用户存储号牌NFT和主题NFT相关接口
### 1.号牌NFT资源、Collection Capbility访问控制和Collection实现
```
    pub resource NFT:NonFungibleToken.INFT   //号牌NFT
    pub resource interface CollectionPublic{ //Capability访问控制
        pub fun deposit(token:@NonFungibleToken.NFT)  //存入NFT
        pub fun getIDs():[UInt64]
        pub fun borrowNFT(id:UInt64): &NonFungibleToken.NFT  //引用该NFT
        pub fun borrowNumberNFT(id:UInt64):&NFT //引用该NFT
    }
    pub resource Collection: NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, CollectionPublic { //号牌NFT Collection
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}  //存储所有的mint的号牌NFT
```

### 3.主题NFT资源、Collection Capbility接口和Collection实现
```
    pub resource ThemeNFT:NonFungibleToken.INFT  //主题NFT
    pub resource interface ThemeCollectionPublic {  //Capability访问控制
        pub fun deposit(token:@ThemeNFT)   //存入NFT
        pub fun getIDs(): [UInt64]  
        pub fun borrowNFT(id:UInt64): &ThemeNFT  //引用该NFT
    }
    pub resource ThemeCollection:ThemeCollectionPublic {
        pub var ownedNFTs: @{UInt64: ThemeNFT}  //存储所有的mint的主题NFT
        ...
    }
```

## 全局查询所有比赛接口
```
access(contract) var allGames:{UInt64:GameDetail} //全局存储所有主办方的比赛信息，便于查找
pub fun getAllGames():{UInt64:GameDetail}{  //查找所有项目方的创建的所有比赛
    return self.allGames
}

pub fun getGameById(id:UInt64):GameDetail{  //根据比赛id查找单个比赛相关信息
    pre {
        self.allGames[id] != nil:"Game not exist!"
    }
    return self.allGames[id]!
}
```

# 使用 

> https://github.dev/emerald-dao/beginner-dapp-course/tree/main/chapter2.0/day1

1. generate testnet keys

```
❯ flow keys generate --network=testnet
🙏 If you want to create an account on testnet with the generated keys use this link:
https://testnet-faucet.onflow.org/?key=5f5373a17e38af85d0489f2a7a8ef6ab880a8b8d35afcef375d1a9479f291e5e848266dbb29ba2b27e4458d5fb158e86b58cf6d4a9524d90d80e9a199203ab65 


🔴️ Store private key safely and don't share with anyone! 
Private Key      d09f61d016e0fa767a7af5fb35f9a198d7ee37cf36db2fd02a0d596ffde81d0c 
Public Key       5f5373a17e38af85d0489f2a7a8ef6ab880a8b8d35afcef375d1a9479f291e5e848266dbb29ba2b27e4458d5fb158e86b58cf6d4a9524d90d80e9a199203ab65 
```

2. copy address

> https://testnet-faucet.onflow.org/

```
0x83f8ed4318375647
```

3. Deploy it

```
❯ flow project deploy --network=testnet

Deploying 1 contracts for accounts: testnet-account

HelloWorld -> 0x83f8ed4318375647 (1ce1d8a5ed4d422b1d0d90e8fedf524d4cbb7498368e676696a7f34bb90bce33) 


✨ All contracts deployed successfully
```

4. Read and Write

```
❯ flow scripts execute ./flow/cadence/scripts/readGreeting.cdc --network=testnet

Result: "Goodbye, Loser"

❯ flow transactions send ./flow/cadence/transactions/changeGreeting.cdc "Goodbye, Loser" --network=testnet --signer=testnet-account
Transaction ID: 903e12cb7e955de255bfc3011a4ed55812b54ab1e012741fc26f67dd260e48e6

Status          ✅ SEALED
ID              903e12cb7e955de255bfc3011a4ed55812b54ab1e012741fc26f67dd260e48e6
Payer           16fa33cab0a7b7c2
Authorizers     [16fa33cab0a7b7c2]

Proposal Key:
    Address     16fa33cab0a7b7c2
    Index       0
    Sequence    1

No Payload Signatures

Envelope Signature 0: 16fa33cab0a7b7c2
Signatures (minimized, use --include signatures)

Events:          
    Index       0
    Type        A.7e60df042a9c0868.FlowToken.TokensWithdrawn
    Tx ID       903e12cb7e955de255bfc3011a4ed55812b54ab1e012741fc26f67dd260e48e6
    Values
                - amount (UFix64): 0.00000204 
                - from (Address?): 0x83f8ed4318375647 

    Index       1
    Type        A.7e60df042a9c0868.FlowToken.TokensDeposited
    Tx ID       903e12cb7e955de255bfc3011a4ed55812b54ab1e012741fc26f67dd260e48e6
    Values
                - amount (UFix64): 0.00000204 
                - to (Address?): 0x912d5440f7e3769e 

    Index       2
    Type        A.912d5440f7e3769e.FlowFees.FeesDeducted
    Tx ID       903e12cb7e955de255bfc3011a4ed55812b54ab1e012741fc26f67dd260e48e6
    Values
                - amount (UFix64): 0.00000204 
                - inclusionEffort (UFix64): 1.00000000 
                - executionEffort (UFix64): 0.00000021 



Code (hidden, use --include code)

Payload (hidden, use --include payload)
```

# View it on testnet

> https://flow-view-source.com/testnet/account/0x83f8ed4318375647/contract/Racenumber