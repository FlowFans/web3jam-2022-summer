import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"
import SoulMadeComponent from "../../contracts/SoulMadeComponent.cdc"

//testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4


transaction {

    let mainNftRef: &SoulMadeMain.Collection
    let componentNftRef: &SoulMadeComponent.Collection


    prepare(acct: AuthAccount) {
        self.mainNftRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")

        self.componentNftRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
            ?? panic("Could not borrow body reference")

    }

    execute {
        self.mainNftRef.deposit(token: <- SoulMadeMain.mintMain())

        var series:String = "Yuri Girl"
        var description:String = "Hat #1 Round"
        var category:String = "Hat"
        var color:String = "Red"
        // todo: computation limit 
        var maxEdition:UInt64 = 5
        var ipfsHash:String = ""

        let components <- SoulMadeComponent.makeEdition(series: series,description: description,category: category,color: color,maxEdition: maxEdition,ipfsHash: ipfsHash)
        //SoulMadeComponent.makeEdition(series: series,description: description,category: category,color: color,maxEdition: maxEdition,ipfsHash: ipfsHash)
        
        while components.length > 0 {
            log(components.length)
            self.componentNftRef.deposit(token: <- components.removeFirst())
        }

        // todo: do we need to have a post check or something?
        destroy components

    }
}

