import NonFungibleToken from "../NonFungibleToken.cdc"
import ExampleNFT from "../ExampleNFT.cdc" 
import MetadataViews from "../MetadataViews.cdc"
import ExampleNFTUser from "../ExampleNFTUser.cdc"

transaction{
    let recipient: Capability<&{ExampleNFTUser.NFTUserCollectionPublic}>
    let collection: &NonFungibleToken.Collection
    prepare(acct: AuthAccount, acct1: AuthAccount){
        self.recipient = acct1.getCapability<&{ExampleNFTUser.NFTUserCollectionPublic}>(ExampleNFTUser.CollectionPublicPath)
        self.collection = acct.borrow<&NonFungibleToken.Collection>(from: ExampleNFT.CollectionStoragePath)
        ?? panic("no resource")
    }

    execute{
        let token <- self.collection.withdraw(withdrawID: 2)
        let metaToken <- token as! @ExampleNFT.NFT
        let newtoken <- ExampleNFTUser.createUserNFT(token: <-metaToken, expired: 10000, recipient: self.recipient)
        let ref = self.recipient.borrow()?? panic("no UserCollection")
        log(newtoken.getType().identifier)
        log(ref.getTypeIDs(type: newtoken.getType().identifier))
        log(ref.borrowNFT(id: newtoken.uuid).uuid)
        log(ref.borrowUserNFT(uuid: newtoken.uuid)!.uuid)
        log(ref.borrowUserNFT(uuid: newtoken.uuid)!.token_id)
        log(ref.getIDs())
        log(newtoken.uuid)
        let receivenft <- newtoken as! @ExampleNFT.NFT
        self.collection.deposit(token: <-receivenft)
    }
}