import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4

transaction(number : UInt64, series : String) {
    let mainNftRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
        // todo: should this be &SoulMadeMain.Collection? Or it can be any Type as this is from StoragePath, not Public or Private path
        self.mainNftRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
        var id : UInt64 = 0
        while id < number {
            self.mainNftRef.deposit(token: <- SoulMadeMain.mintMain(series: series))
            id = id + 1
        }
    }
}


