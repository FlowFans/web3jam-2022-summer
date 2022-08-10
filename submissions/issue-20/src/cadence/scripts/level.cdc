import ExampleNFT from 0xb10db40892311e63
import MetadataViews from 0x631e88ae7f1d7c20

pub fun main(id: UInt64, acct: Address): AnyStruct? {
    let account = getAccount(acct)
    let collectionRef = account.getCapability<&{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath)
                        .borrow()
                        ?? panic("no resource")
    let nftRef = collectionRef.borrowExampleNFT(id: id)
    return nftRef!.resolveView(Type<ExampleNFT.Level>()) 
}