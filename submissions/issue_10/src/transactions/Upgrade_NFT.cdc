import SinGirlsNFT from 0x01

/// This transaction is for transferring and NFT from
/// one account to another
transaction(recipient: Address, writeID: UInt64, type: String) {

    prepare(signer: AuthAccount) {
        // borrow a reference to the signer's NFT collection
        let writeRef = getAccount(recipient).getCapability(/public/SinGirlsCollection).borrow<&SinGirlsNFT.Collection>()
                                  ?? panic("The recipient does not have a Collection.")

        let writeNFT = writeRef.borrowAuthNFT(id: writeID)


        writeNFT.upgradeNFT(type: type)
        }

}