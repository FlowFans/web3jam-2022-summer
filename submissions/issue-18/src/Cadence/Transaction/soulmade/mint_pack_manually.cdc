// import SoulMade from "../../contracts/SoulMade.cdc"

// testnet
import SoulMade from 0x421c19b7dc122357

transaction(scarcity: String, series: String, ipfsHash: String, mainNftIds: [UInt64], componentNftIds: [UInt64]) {
    let adminRef: &SoulMade.Admin

    prepare(admin: AuthAccount) {
        self.adminRef = admin.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath)
            ?? panic("Could not borrow Admin resource")
    }

    execute {
        self.adminRef.mintPackManually(scarcity: scarcity, series: series, ipfsHash: ipfsHash, mainNftIds: mainNftIds, componentNftIds: componentNftIds)
    }
}
