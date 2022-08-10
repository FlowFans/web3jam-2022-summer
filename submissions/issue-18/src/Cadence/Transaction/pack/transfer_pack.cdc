
import SoulMadePack from 0x421c19b7dc122357
import NonFungibleToken from 0x631e88ae7f1d7c20

transaction(packID: UInt64, receiver: Address){
    let receiveCollection : &{SoulMadePack.CollectionPublic}
    let giveCollection : &SoulMadePack.Collection
    prepare(acct: AuthAccount) {
        self.receiveCollection = getAccount(receiver).getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Error")
        // let packNftCollection = getAccount(receiver).getCapability(/public/SoulMadePackCollection)!.borrow<&SoulMadePack.collection{SoulMadePack.CollectionPublic}>() ?? panic("Error")

        self.giveCollection = acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath)!
    }

    execute {
        let nft <- self.giveCollection.withdraw(withdrawID: packID)
        self.receiveCollection.deposit(token: <- nft)

    }
}
