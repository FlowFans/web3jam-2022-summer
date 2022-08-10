// import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

// testnet
import SoulMadeMain from 0x421c19b7dc122357

transaction(series: String) {
    let mainNftRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
        // todo: should this be &SoulMadeMain.Collection? Or it can be any Type as this is from StoragePath, not Public or Private path
        self.mainNftRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
        self.mainNftRef.deposit(token: <- SoulMadeMain.mintMain(series: series))
    }
}