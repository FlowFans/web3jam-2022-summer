import SoulMade from "../../contracts/SoulMade.cdc"



// testnet
// import SoulMade from 0x76b2527585e45db4

transaction(series: String, name: String, description: String, category: String, layer: UInt64, startEdition: UInt64, endEdition: UInt64, maxEdition: UInt64, ipfsHash: String) {
    let adminRef: &SoulMade.Admin

    prepare(admin: AuthAccount) {
        self.adminRef = admin.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath) ?? panic("Could not borrow Admin resource")
        log(self.adminRef)
        log("Here🚩")
    }

    execute {
        self.adminRef.mintComponents(series: series, name: name, description: description, category: category, layer: layer, startEdition: startEdition, endEdition: endEdition, maxEdition: maxEdition, ipfsHash: ipfsHash)
    }
}

 