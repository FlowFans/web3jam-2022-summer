import SoulMadeMarketplace from 0xa25fe4df1a3d7b77
import SoulMade from 0xa25fe4df1a3d7b77

transaction(cut : UFix64) {
    let admin : &SoulMade.Admin
    prepare(acct: AuthAccount){
        self.admin = acct.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath) ?? panic("Cannot borrow Main NFT collection receiver from account")

    }

    execute{
        self.admin.updataPlatformCut(platformCut: cut)
    }
}
