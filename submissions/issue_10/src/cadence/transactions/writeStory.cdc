import SinGirlsNFT from "../contracts/SinGirlsNFT.cdc"

/// This transaction is for transferring and NFT from
/// one account to another
transaction(recipient: Address, writeID: UInt64, story: String) {

    prepare(signer: AuthAccount) {
        // borrow a reference to the signer's NFT collection
        let writeRef = getAccount(recipient).getCapability(/public/SinGirlsCollection).borrow<&SinGirlsNFT.Collection>()
                                  ?? panic("The recipient does not have a Collection.")

        let writeNFT = writeRef.borrowAuthNFT(id: writeID)


        writeNFT.WriteStory(Story: story)}

}
