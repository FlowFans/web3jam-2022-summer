import FungibleToken from "../../contracts/core/FungibleToken.cdc"
import FUSD from "../../contracts/core/FUSD.cdc"

transaction(amount: UFix64, recipient: Address) {

    let mintedVault: @FUSD.Vault

    prepare(signer: AuthAccount) {
        let admin = signer.borrow<&FUSD.Administrator>(from: FUSD.AdminStoragePath)
            ?? panic("Could not borrow Administrator reference")

        let minter <- admin.createNewMinter()
        self.mintedVault <- minter.mintTokens(amount: amount)
        destroy minter
    }

    execute {
        let receiver = getAccount(recipient).getCapability<&{FungibleToken.Receiver}>(/public/fusdReceiver).borrow()
            ?? panic("Could not borrow Receiver capability")

        receiver.deposit(from: <- self.mintedVault)
    }
}