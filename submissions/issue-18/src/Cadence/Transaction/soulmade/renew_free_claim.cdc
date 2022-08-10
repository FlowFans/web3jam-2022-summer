import SoulMade from "../../contracts/SoulMade.cdc"

// testnet
// import SoulMade from 0x76b2527585e45db4

transaction {
    let adminRef: &SoulMade.Admin

    prepare(admin: AuthAccount) {
        self.adminRef = admin.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath)
            ?? panic("Could not borrow Admin resource")
    }

    execute {
        self.adminRef.renewFreeClaim()
    }
}
