import SoulMadeMain from "../../contracts/SoulMadeMain.cdc"

// testnet
// import SoulMadeMain from 0x76b2527585e45db4
// import SoulMadeComponent from 0x76b2527585e45db4
// import NonFungibleToken from 0x631e88ae7f1d7c20


pub fun main(address: Address, mainNftId: UInt64) : String {

    let receiverRef = getAccount(address)
                      // todo: confirm this Interface
                      .getCapability<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    
    return receiverRef.borrowMain(id: mainNftId).mainDetail.name
}