import ExampleNFT from 0xb10db40892311e63

pub fun main(addr: Address): Bool {
    let acct = getAccount(addr)
    if acct.getCapability<&ExampleNFT.Collection{ExampleNFT.ExampleNFTCollectionPublic}>(ExampleNFT.CollectionPublicPath).borrow() == nil {
        return false
    }
    else {
        return true
    }
}