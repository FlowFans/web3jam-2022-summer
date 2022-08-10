import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
// import SoulMadeMain from 0x76b2527585e45db4


transaction(mainId: UInt64) {
    let mainCollectionRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
      // todo: actually, do we have to always borrow storage?
      self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) ?? panic("Could not borrow main reference")
    }

    execute {
      let nft <- self.mainCollectionRef.withdraw(withdrawID: mainId)
      destroy nft
    }
}