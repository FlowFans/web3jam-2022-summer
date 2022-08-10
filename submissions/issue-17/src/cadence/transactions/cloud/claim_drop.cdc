import FungibleToken from "../contracts/core/FungibleToken.cdc"
import FUSD from "../contracts/core/FUSD.cdc"
import Cloud from "../contracts/Cloud.cdc"

transaction(dropID: UInt64, host: Address) {
    let drop: &{Cloud.IDropPublic}
    let receiver : &FUSD.Vault{FungibleToken.Receiver}

    prepare(acct: AuthAccount) {
        let dropCollection = getAccount(host)
            .getCapability(Cloud.DropCollectionPublicPath)
            .borrow<&Cloud.DropCollection{Cloud.IDropCollectionPublic}>()
            ?? panic("Could not borrow the public DropCollection from the host")
        
        let drop = dropCollection.borrowPublicDropRef(dropID: dropID)
            ?? panic("Could not borrow the public Drop from the collection")

        if (acct.borrow<&FUSD.Vault>(from: /storage/fusdVault) == nil) {
            acct.save(<-FUSD.createEmptyVault(), to: /storage/fusdVault)

            acct.link<&FUSD.Vault{FungibleToken.Receiver}>(
                /public/fusdReceiver,
                target: /storage/fusdVault
            )

            acct.link<&FUSD.Vault{FungibleToken.Balance}>(
                /public/fusdBalance,
                target: /storage/fusdVault
            )
        }
        
        self.drop = drop 
        self.receiver = acct
            .getCapability(/public/fusdReceiver)
            .borrow<&FUSD.Vault{FungibleToken.Receiver}>()
            ?? panic("Could not borrow Receiver")
    }

    execute {
        self.drop.claim(receiver: self.receiver, params: {})
    }
}