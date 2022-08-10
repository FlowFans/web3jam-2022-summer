// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadeMarketplace from "../../contracts/SoulMadeMarketplace.cdc"
// import SoulMadePack from "../../contracts/SoulMadePack.cdc"
// import FungibleToken from 0xee82856bf20e2aa6
// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import FlowToken from 0x0ae53cb6e3f42a79

// testnet
import SoulMadeMain from 0xb4187e54e0ed55a8
import SoulMadePack from 0xb4187e54e0ed55a8
import SoulMadeComponent from 0xb4187e54e0ed55a8
import SoulMadeMarketplace from 0xb4187e54e0ed55a8
import FungibleToken from 0x9a0766d93b6608b7
import FlowToken from 0x7e60df042a9c0868


transaction(tokenId: UInt64, nftType: String) {

    let vaultRef: &{FungibleToken.Provider}
    let soulmadeMainCollectionCap: Capability<&{SoulMadeMain.CollectionPublic}>
    let soulmadeComponentCollectionCap: Capability<&{SoulMadeComponent.CollectionPublic}>

    prepare(account: AuthAccount) {
        // In case the customer does not claim the free drop while buy item directly, hence we need to initialize it here as well 
        if account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
            account.save(<- SoulMadeMain.createEmptyCollection(), to: SoulMadeMain.CollectionStoragePath)
            account.link<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
            account.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
        }

        if account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
            account.save(<- SoulMadeComponent.createEmptyCollection(), to: SoulMadeComponent.CollectionStoragePath)
            account.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
            account.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
        }

        if account.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
            account.save(<- SoulMadePack.createEmptyCollection(), to: SoulMadePack.CollectionStoragePath)
            account.link<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
            account.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
        }        

        self.soulmadeMainCollectionCap = account.getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath)
        self.soulmadeComponentCollectionCap = account.getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath)
        self.vaultRef = account.borrow<&{FungibleToken.Provider}>(from: /storage/flowTokenVault) ?? panic("Could not borrow owner's Vault reference")

    }

    execute {
        let platformAddress : Address = 0xb4187e54e0ed55a8
        // let platformAddress : Address = 0x56aa8181daafcff8
        // let platformAddress : Address = 0xa445c4ad9711aaae
        // let platformAddress : Address = 0xf8d6e0586b0a20c7
        let seller = getAccount(platformAddress)
        let marketplace = seller.getCapability(SoulMadeMarketplace.CollectionPublicPath).borrow<&{SoulMadeMarketplace.SalePublic}>() ?? panic("Could not borrow seller's sale reference")

        if nftType == "SoulMadeMain"{
            var price = SoulMadeMarketplace.getSoulMadeMainSale(address: platformAddress, id: tokenId).price
            var temporaryVault <- self.vaultRef.withdraw(amount: price)
            marketplace.purchaseSoulMadeMain(tokenId: tokenId, recipientCap: self.soulmadeMainCollectionCap, buyTokens: <- temporaryVault)
        } else if nftType == "SoulMadeComponent" {
            var price = SoulMadeMarketplace.getSoulMadeComponentSale(address: platformAddress, id: tokenId).price
            var temporaryVault <- self.vaultRef.withdraw(amount: price)
            marketplace.purchaseSoulMadeComponent(tokenId: tokenId, recipientCap: self.soulmadeComponentCollectionCap, buyTokens: <- temporaryVault)
        }
    }
}
 