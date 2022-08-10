// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"
// import SoulMadePack from "../../contracts/SoulMadePack.cdc"
// import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
// import FungibleToken from 0xee82856bf20e2aa6
// import FlowToken from 0x0ae53cb6e3f42a79
// import NFTStorefront from "../../contracts/NFTStorefront.cdc"

import SoulMadeMain from 0xa25fe4df1a3d7b77
import SoulMadeComponent from 0xa25fe4df1a3d7b77
import SoulMadePack from 0xa25fe4df1a3d7b77
import NonFungibleToken from 0x631e88ae7f1d7c20
import FungibleToken from 0x9a0766d93b6608b7
import FlowToken from 0x7e60df042a9c0868
import NFTStorefront from 0x94b06cfca1d8a476

transaction {

    let mainNftCollection: &{SoulMadeMain.CollectionPublic}
    let componentNftCollection: &{SoulMadeComponent.CollectionPublic}
    let CollectionForClaim: &{SoulMadePack.CollectionFreeClaim}

    prepare(acct: AuthAccount){

        if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
            let collection <- SoulMadeMain.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeMain.CollectionStoragePath)
            acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
            // todo: double check if we need this PrivatePath at all. I remeber we actually have used it somewhere.
            acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
        }

        if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
            let collection <- SoulMadeComponent.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
        }
        
        if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionFreeClaimStoragePath) == nil {
            let collection <- SoulMadePack.createEmptyCollection()
            acct.save(<-collection, to: SoulMadePack.CollectionFreeClaimStoragePath)
            acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionFreeClaim}>(SoulMadePack.CollectionFreeClaimPublicPath, target: SoulMadePack.CollectionFreeClaimStoragePath)
            acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionFreeClaimPrivatePath, target: SoulMadePack.CollectionFreeClaimStoragePath)
        }

        self.mainNftCollection = acct.borrow<&{SoulMadeMain.CollectionPublic}>(from: SoulMadeMain.CollectionStoragePath) ?? panic("Cannot borrow Main NFT collection receiver from account")
        self.componentNftCollection = acct.borrow<&{SoulMadeComponent.CollectionPublic}>(from: SoulMadeComponent.CollectionStoragePath) ?? panic("Cannot borrow Component NFT collection receiver from account")

        // emulator address
        // let platformAddress: Address = 0xf8d6e0586b0a20c7
        // js-testing admin address
        // let platformAddress: Address = 0x01cf0e2f2f715450
        let platformAddress: Address = 0xa25fe4df1a3d7b77
        
        self.CollectionForClaim = getAccount(platformAddress).getCapability<&{SoulMadePack.CollectionFreeClaim}>(SoulMadePack.CollectionFreeClaimPublicPath).borrow() ?? panic("Cannot borrow CollectionForClaim from Platform")

        self.CollectionForClaim.freeClaim(mainNftCollectionRef: self.mainNftCollection, componentNftCollectionRef: self.componentNftCollection)
    }

    execute {
    }
}