import ExampleMarketplace from 0xb10db40892311e63
import ExampleNFT from 0xb10db40892311e63
import FungibleToken from 0x9a0766d93b6608b7
import FlowToken from 0x7e60df042a9c0868

transaction(tokenID: UInt64, price: UFix64, addr: Address) {
    let recipient: Capability<&AnyResource{ExampleNFT.ExampleNFTCollectionPublic}>
    let buyTokens: @FungibleToken.Vault
    prepare(acct: AuthAccount) {
        self.recipient = acct.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath)
        let vaultRef = acct.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)
			?? panic("Could not borrow reference to the owner's Vault!")
        self.buyTokens <- vaultRef.withdraw(amount: price)
    }
    execute {
        let acct = getAccount(addr)
        let salepub = acct.getCapability<&{ExampleMarketplace.SalePublic}>(ExampleMarketplace.SalePublicPath)
                        .borrow() ?? panic("no SaleCollection resource")
        salepub.purchase(tokenID: tokenID, recipient: self.recipient, buyTokens: <- self.buyTokens)
    }
}