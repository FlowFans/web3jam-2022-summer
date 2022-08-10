import ExampleNFT from 0xb10db40892311e63
import FungibleToken from 0x9a0766d93b6608b7

transaction(id: UInt64, addr: Address) {
    execute {
        let acct = getAccount(addr)
        let collectionRef = acct.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath)
                        .borrow()
                        ?? panic("no resource")
        let nftRef = collectionRef.borrowExampleNFT(id: id)
        let recipient = acct.getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver)
        ExampleNFT.claimProfit(nft: nftRef!, recipient: recipient)
    }
}