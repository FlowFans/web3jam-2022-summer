import ExampleNFT from 0xb10db40892311e63
import NonFungibleToken from 0x631e88ae7f1d7c20

transaction(addr: Address) {
    let mintRef: &ExampleNFT.NFTMinter
    prepare(admin: AuthAccount) {
        self.mintRef = admin.borrow<&ExampleNFT.NFTMinter>(from: ExampleNFT.MinterStoragePath)
                        ?? panic("no resource")
    }

    execute {
        let account = getAccount(addr)
        let recipient = account.getCapability<&{NonFungibleToken.CollectionPublic}>(ExampleNFT.CollectionPublicPath)
                        .borrow() 
                        ?? panic("no resource")
        self.mintRef.mint(recipient: recipient, description: "", thumbnail: "")
    }
}