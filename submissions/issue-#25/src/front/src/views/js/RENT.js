export const RENT = `import ExampleRentMarketplace from 0xb096b656ab049551
import ExampleNFTUser from 0xb096b656ab049551
import FungibleToken from 0x9a0766d93b6608b7

transaction(tokenID: UInt64, 
    price: UFix64, 
    expired: UInt64,
    acct: Address) {
    
    let recipient: Capability<&{ExampleNFTUser.NFTUserCollectionPublic}>
    let buyTokens: @FungibleToken.Vault

    prepare(acct: AuthAccount){
        self.recipient = acct.getCapability<&{ExampleNFTUser.NFTUserCollectionPublic}>(
            ExampleNFTUser.CollectionPublicPath
        )
        
        let vaultRef = acct.borrow<&FungibleToken.Vault>(from: /storage/flowTokenVault)
   ?? panic("Could not borrow reference to the owner's Vault!")

        let needPrice = price * UFix64(expired - getCurrentBlock().height) / 86400.0
        self.buyTokens <- vaultRef.withdraw(amount: needPrice)
    }

    execute{
        let account = getAccount(acct)
        let saleRef = account.getCapability<&{ExampleRentMarketplace.SalePublic}>(
            /public/ExampleRentMarketplace
        ).borrow() ?? panic("no sale collection")
        saleRef.rent(
            tokenID: tokenID,
            recipient: self.recipient,
            buyTokens: <-self.buyTokens,
            expired: expired
            )
    }
}
`
