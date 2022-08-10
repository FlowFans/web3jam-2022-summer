import NonFungibleToken from "../NonFungibleToken.cdc"
import ExampleNFT from "../ExampleNFT.cdc"

transaction(){

    let mintref: &ExampleNFT.NFTMinter


    prepare(acct: AuthAccount){
        self.mintref = acct.borrow<&ExampleNFT.NFTMinter>(from: ExampleNFT.MinterStoragePath)
        ?? panic("no minter resource")
    }

    execute{
        let account = Address(0x05)
        let receiver = getAccount(account)
        let recipient = receiver.getCapability<&{NonFungibleToken.CollectionPublic}>(ExampleNFT.CollectionPublicPath)
        .borrow()?? panic("no capability")
        self.mintref.mintNFT(recipient: recipient, name: "vans", description: "vans test3", thumbnail: "")
        log(recipient.getIDs())
    }
}