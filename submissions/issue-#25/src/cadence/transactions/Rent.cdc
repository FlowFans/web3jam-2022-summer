import ExampleMarketplace
import ExampleNFTUser
import ExampleToken

transaction(tokenID: UInt64, 
    price: UFix64, 
    expired: UInt64){
    
    let recipient: Capability<&{ExampleNFTUser.NFTUserCollectionPublic}
    let buyTokens: @ExampleToken.Vault

    prepare(acct: AuthAccount){
        self.recipient = acct.getCapability<&{ExampleNFTUser.NFTUserCollectionPublic>(
            ExampleNFTUser.CollectionPublicPath
        )
        
        let vaultRef = acct.getCapability<&ExampleToken.Vault>(
            /storage/CadenceFungibleTokenTutorialVault
        ).borrow() ?? panic("no resource")

        self.buyTokens <- vaultRef.withdraw(amount: pirce)
    }

    execute{
        let account = getAccount()
        let saleRef = account.getCapability<&{SalePublic}>(
            /public/ExampleMarketplace
        )
        saleRef.rent(
            tokenID: tokenID,
            recipient: self.recipient,
            buyTokens: <-self.buyTokens,
            expired: expired
            )
    }
}