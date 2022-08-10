import Cloud from "../contracts/Cloud.cdc"

transaction() {
    let admin: &Cloud.Admin
    prepare(acct: AuthAccount) {
        self.admin = acct.borrow<&Cloud.Admin>(from: Cloud.CloudAdminStoragePath)
            ?? panic("Cloud not borrow Cloud.Admin from account")
    }

    execute {
        self.admin.toggleContractPause()
    }
}