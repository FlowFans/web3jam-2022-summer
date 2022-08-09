import OverluPackage from 0xOverluPackage
import FlowToken  from 0xFlowToken
import FungibleToken from 0xFungibleToken

transaction(amount: UFix64) {
    let minter: &OverluPackage.NFTMinter
    let receiver: &{FungibleToken.Receiver}
    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
        self.receiver = signer.borrow<&{FungibleToken.Receiver}>(from: /storage/flowTokenVault)!
    }
    execute {
        self.receiver.deposit(from: <- self.minter.getVaultRef()!.withdraw(amount: amount))
    }
}
 