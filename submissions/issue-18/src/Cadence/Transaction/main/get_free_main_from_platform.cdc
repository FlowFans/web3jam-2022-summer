import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

//testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4

transaction {

    let mainNftRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
        self.mainNftRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")

    }

    execute {
        self.mainNftRef.deposit(token: <- SoulMadeMain.mintMain())

    }
}

