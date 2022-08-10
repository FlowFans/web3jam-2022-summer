import SoulMadePack from "../../contracts/SoulMadePack.cdc"
import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import SoulMadePack from 0x76b2527585e45db4

transaction(packId: UInt64) {
    let packCollectionRef: &SoulMadePack.Collection
    let mainCollectionRef: &SoulMadeMain.Collection
    let componentCollectionRef: &SoulMadeComponent.Collection

    prepare(acct: AuthAccount) {
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
        self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
            ?? panic("Could not borrow component reference")
        self.packCollectionRef = acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath)
            ?? panic("Could not borrow pack reference")            
    }

    execute {
        self.packCollectionRef.openPackFromCollection(id: packId, mainNftCollectionRef: self.mainCollectionRef, componentNftCollectionRef: self.componentCollectionRef)
    }
}