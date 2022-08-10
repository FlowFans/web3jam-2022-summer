import MetaForestTree from 0x25d45165c1b69b6e
import MetaForestCarbonEmission from 0x25d45165c1b69b6e
pub contract MetaForestAccessControl {
    
    pub let AdminStoragePath: StoragePath

    pub resource Admin {

        pub fun createTemplate(maxSupply: UInt64, baseUri: String, tokenUri: String){
            MetaForestTree.createTemplate(maxSupply:maxSupply,baseUri:baseUri, tokenUri: tokenUri)
        }
        pub fun increaseMetaForestCarbonEmissions(user: Address, amount: UFix64){
            MetaForestCarbonEmission.increaseMetaForestCarbonEmissions(user:user, amount: amount)
        }
        pub fun mintNFT(templateId:UInt64, receiptAccount:Address){
            MetaForestTree.mintNFT(templateId:templateId, account:receiptAccount)
        }
        pub fun updateBaseUri(templateId: UInt64, baseUri: String){
            MetaForestTree.updateBaseUri(templateId: templateId, baseUri: baseUri)
        }
        pub fun updateTokenUri(templateId: UInt64, tokenUri: String){
            MetaForestTree.updateTokenUri(templateId: templateId, tokenUri: tokenUri)
        }
        
    }

    init(){  
        self.AdminStoragePath = /storage/MetaForestAdmin
        self.account.save(<- create Admin(), to:  self.AdminStoragePath)
    }
    
}