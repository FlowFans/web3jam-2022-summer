// import SoulMade from "../../contracts/SoulMade.cdc"

// testnet
import SoulMade from 0x421c19b7dc122357

transaction(series: String, name: String, description: String, category: String, layer: UInt64, currentEdition: UInt64, maxEdition: UInt64, ipfsHash: String) {
    let adminRef: &SoulMade.Admin

    prepare(admin: AuthAccount) {
        self.adminRef = admin.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath) ?? panic("Could not borrow Admin resource")
    }

    execute {
        self.adminRef.mintComponent(series: series, name: name, description: description, category: category, layer: layer, edition: currentEdition, maxEdition: maxEdition, ipfsHash: ipfsHash)
    }
}