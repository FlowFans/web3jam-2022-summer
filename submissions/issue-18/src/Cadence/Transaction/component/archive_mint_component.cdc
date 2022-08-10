import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4

transaction(series: String, description: String, category: String, color: String, currentEdition: UInt64, maxEdition: UInt64, ipfsHash: String) {
    let componentNftRef: &SoulMadeComponent.Collection

    prepare(acct: AuthAccount) {
        self.componentNftRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
            ?? panic("Could not borrow Collection reference")
    }

    execute {
        let components <- SoulMadeComponent.makeEdition(series: series,description: description,category: category,color: color, currentEdition: currentEdition, maxEdition: maxEdition,ipfsHash: ipfsHash)
        
        while components.length > 0 {
            log(components.length)
            self.componentNftRef.deposit(token: <- components.removeFirst())
            
        }

        destroy components
    }
}