import Cloud from "../contracts/Cloud.cdc"
import FUSD from "../contracts/core/FUSD.cdc"

transaction(dropID: UInt64, amount: UFix64) {
    let drop: &Cloud.Drop
    let vault: &FUSD.Vault

    prepare(acct: AuthAccount) {
        let dropCollection = acct.borrow<&Cloud.DropCollection>(from: Cloud.DropCollectionStoragePath)
            ?? panic("Could not borrow dropCollection")

        self.vault = acct.borrow<&FUSD.Vault>(from: /storage/fusdVault)
            ?? panic("Could not borrow fusdVault")

        self.drop = dropCollection.borrowDropRef(dropID: dropID)!
    }

    execute {
        let v <- self.vault.withdraw(amount: amount)
        self.drop.deposit(from: <- v)
    }
}