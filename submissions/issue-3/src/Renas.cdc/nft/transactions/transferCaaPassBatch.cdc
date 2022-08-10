
import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import CaaPass from "../../contracts/CaaPass.cdc"

// This transaction transfers a CAA Pass from one account to another.

transaction(recipient: Address, withdrawStartingID: UInt64, count: Int) {
    prepare(signer: AuthAccount) {
        
        // get the recipients public account object
        let recipient = getAccount(recipient)

        // borrow a reference to the signer's NFT collection
        let collectionRef = signer.borrow<&CaaPass.Collection>(from: CaaPass.CollectionStoragePath)
            ?? panic("Could not borrow a reference to the owner's collection")

        // borrow a public reference to the receivers collection
        let depositRef = recipient.getCapability(CaaPass.CollectionPublicPath)!.borrow<&{NonFungibleToken.CollectionPublic}>()!

        var index = 0
        while index < count {
            let currentId = withdrawStartingID + UInt64(index)
            
            if collectionRef.ownedNFTs[currentId] != nil {
                // withdraw the NFT from the owner's collection
                let nft <- collectionRef.withdraw(withdrawID: currentId)

                // Deposit the NFT in the recipient's collection
                depositRef.deposit(token: <-nft)
            }

            index = index + 1
        }
    }
}
