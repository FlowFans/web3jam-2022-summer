import MetaForestTree from 0x25d45165c1b69b6e
import MetaForestCarbonEmission from 0x25d45165c1b69b6e
import MetaForestCarbonEnergy from 0x25d45165c1b69b6e
import FungibleToken from 0x9a0766d93b6608b7

pub contract MetaForestCore {
    
    // A dictionary that shows a user freeList nft, a user can only get one free nft.
    access(self) var freeList : {Address:Bool}
    // A dictionary that shows the growth of a token id.
    access(self) var growth : {UInt64:UFix64}
    // A dictionary that shows the healthiness of a token id.
    access(self) var unHealthy : {UInt64:UFix64}
    // A dictionary that shows the last attack against a user on a specific nft.
    access(self) var lastAttack : {Address:UFix64}
    // A dictionary that shows a token id against it's URI
    access(contract) var tokenURI: {UInt64: String}
    // A variable that stores the URI count.
    access(contract) var tokenURICount: UInt64

    //----------------------------------------------------
    //          MetaForestCore contract level definitions
    //----------------------------------------------------

    pub event Attack(account: Address, tokenId: UInt64, amount:UFix64)
    pub event Watering(account: Address, tokenId: UInt64, wateringAmount:UFix64)
    pub event FreeNFTPurchased(account: Address, templateId: UInt64)
    pub event NFTPurchasedWithFlow(account: Address, templateId: UInt64)


    pub resource interface CorePublic{ 
        pub fun watering(tokenId: UInt64, wateringAmount: UFix64, userAddress: Address, MetaForestCarbonEnergy: @FungibleToken.Vault)
        pub fun attack(nftAccount: Address, tokenId:UInt64, attackAmount: UFix64, MetaForestCarbonEnergy: @FungibleToken.Vault)
        pub fun purchaseNFTWithFlow(templateId: UInt64, recipientAddress: Address, price: UFix64, flowPayment: @FungibleToken.Vault)
        pub fun purchaseNFTFree(templateId: UInt64, recipientAddress: Address)
    }

    pub resource Core : CorePublic {

        pub fun watering(tokenId: UInt64, wateringAmount: UFix64, userAddress: Address, MetaForestCarbonEnergy: @FungibleToken.Vault){
            pre {
                tokenId != 0: "token id should be valid"
                wateringAmount > UFix64(0.0): "wateringAmount should be valid"
                userAddress != nil: "userAddress should be valid"
                wateringAmount >= MetaForestCarbonEnergy.balance :  "watering amount should be equal or greater than MetaForestCarbonEnergy balance"
            }
            let account = getAccount(userAddress)
            let acct1Capability = account.getCapability(MetaForestTree.CollectionPublicPath)
                                                .borrow<&{MetaForestTree.MetaForestTreeCollectionPublic}>()
                                                ?? panic("Could not get receiver reference to the NFT Collection")
            var nftIds =   acct1Capability.getIDs()
            assert(nftIds.contains(tokenId), message: "you don't have nft with this id")

            var nftData = MetaForestTree.getNFTDataById(nftId: tokenId)
            let templateTd = nftData.templateId
            
            if(wateringAmount <= 10.0) {
                MetaForestTree.updateTokenUri(templateId: templateTd, tokenUri: MetaForestCore.tokenURI[1]!)
            }else if(wateringAmount <= 20.0) {
                MetaForestTree.updateTokenUri(templateId: templateTd, tokenUri: MetaForestCore.tokenURI[2]!)
            }else if(wateringAmount <= 30.0) {
                MetaForestTree.updateTokenUri(templateId: templateTd, tokenUri: MetaForestCore.tokenURI[3]!)
            }else {
                MetaForestTree.updateTokenUri(templateId: templateTd, tokenUri: MetaForestCore.tokenURI[4]!)
            }
            
            // check the healthy and unhealthy amount 
            if MetaForestCore.unHealthy[tokenId]! >= wateringAmount {
                MetaForestCore.unHealthy[tokenId] =  MetaForestCore.unHealthy[tokenId]! - wateringAmount
            }else {
                MetaForestCore.growth[tokenId] = MetaForestCore.growth[tokenId]! + wateringAmount - MetaForestCore.unHealthy[tokenId]!
                MetaForestCore.unHealthy[tokenId] = 0.0
            }
            // withdraw and burn MetaForestCarbonEnergy Token
            destroy  MetaForestCarbonEnergy
            emit Watering(account:userAddress, tokenId: tokenId, wateringAmount: wateringAmount)
        }

        pub fun attack(nftAccount: Address, tokenId:UInt64, attackAmount: UFix64, MetaForestCarbonEnergy: @FungibleToken.Vault){
            pre {
                tokenId != 0: "token id should be valid"
                attackAmount >= 20.0 : "attackAmount should be greater than or equal to 20"
                nftAccount != nil: "userAddress should be valid"
                attackAmount >= MetaForestCarbonEnergy.balance : "attack amount should be equal or greater than MetaForestCarbonEnergy balance"
                }
            
            let account = getAccount(nftAccount)
            let acct1Capability = account.getCapability(MetaForestTree.CollectionPublicPath)
                                    .borrow<&{MetaForestTree.MetaForestTreeCollectionPublic}>()
                                    ?? panic("Could not get receiver reference to the NFT Collection")
            var nftIds =  acct1Capability.getIDs()
            assert(nftIds.contains(tokenId), message: "user doesn't have nft with this id which you want to attack")

            var nftData = MetaForestTree.getNFTDataById(nftId: tokenId)
            let templateTd = nftData.templateId
            //token greate than 20
            if(attackAmount == 20.0) {
                MetaForestTree.updateTokenUri(templateId: templateTd, tokenUri: MetaForestCore.tokenURI[2-1]!)
            }else if(attackAmount == 30.0) {
                MetaForestTree.updateTokenUri(templateId: templateTd, tokenUri: MetaForestCore.tokenURI[3-1]!)
            }else {
                MetaForestTree.updateTokenUri(templateId: templateTd, tokenUri: MetaForestCore.tokenURI[4-1]!)
            }

            MetaForestCore.unHealthy[tokenId] = MetaForestCore.unHealthy[tokenId]! + attackAmount
            MetaForestCore.lastAttack[nftAccount] = getCurrentBlock().timestamp

            destroy  MetaForestCarbonEnergy

            emit Attack(account: self.owner!.address, tokenId: tokenId, amount: attackAmount)
        }

        // Method to Purchase an NFT with Flow Tokens
        pub fun purchaseNFTWithFlow(templateId: UInt64, recipientAddress: Address, price: UFix64, flowPayment: @FungibleToken.Vault) {
            pre {
                templateId != 0: "template it must not be zero"
                recipientAddress != nil: "recript address must not be null"
                price > 0.0: "Price should be greater than zero"
                flowPayment.balance == price: "Your vault does not have balance to buy NF"
            }
            
            let adminVaultReceiverRef = getAccount(self.owner!.address)
                                                    .getCapability(/public/MetaForestCarbonEnergyReceiver)
                                                    .borrow<&MetaForestCarbonEnergy.Vault{FungibleToken.Receiver}>()
                                                    ?? panic("Could not borrow reference to owner token vault!")
            adminVaultReceiverRef.deposit(from: <- flowPayment)
            
            MetaForestTree.mintNFT(templateId: templateId, account: recipientAddress)

            emit NFTPurchasedWithFlow(account: recipientAddress, templateId: templateId)
        }
        
        pub fun purchaseNFTFree(templateId: UInt64, recipientAddress: Address) {
            pre {
                templateId != 0: "template it must not be zero"
                recipientAddress != nil : "recript address must not be null"
                MetaForestCore.freeList[recipientAddress] != true: "you have already get free nft"
            }
            MetaForestTree.mintNFT(templateId: templateId, account: recipientAddress)
            MetaForestCore.freeList[recipientAddress] = true

            emit FreeNFTPurchased(account: recipientAddress, templateId: templateId)
        }
        // only admin can call this function
        pub fun setTokenUriData(tokenUri: String){
            pre {
                tokenUri.length > 0: "token uri should be valid"
            }

            MetaForestCore.tokenURI[MetaForestCore.tokenURICount] = tokenUri
            MetaForestCore.tokenURICount = MetaForestCore.tokenURICount + 1
        }
    }

    pub fun getGrowthAmount(tokenId:UInt64): UFix64{
        pre {
            tokenId != 0:"token id must not be null"
        }
        return MetaForestCore.growth[tokenId]!
    }
    pub fun getAttackAmount(account:Address): UFix64{
        pre {
            account != nil : "account must not be null"
        }
        return MetaForestCarbonEmission.getlastBalanceOf(user: account)
    }

    pub fun getUnhealthyAmount(tokenId: UInt64): UFix64{
        return  MetaForestCore.unHealthy[tokenId]!
    }

    
    

    init(){
        self.freeList = {}
        self.growth = {}
        self.unHealthy = {}
        self.lastAttack = {}
        self.tokenURI = {}
        self.tokenURICount = 1

        self.account.save(<- create Core(), to: /storage/MetaForestCore)
        self.account.link<&{CorePublic}>(/public/MetaForestCore, target: /storage/MetaForestCore)
    }
    
}