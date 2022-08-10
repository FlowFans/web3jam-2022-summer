import FungibleToken from "../../contracts/FungibleToken.cdc"
import ContributionPoint from "../../contracts/ContributionPoint.cdc"

transaction(recipient: Address, amount: UFix64) {
    let tokenAdmin: &ContributionPoint.Administrator
    let tokenReceiver: &{FungibleToken.Receiver}

    prepare(signer: AuthAccount) {
        self.tokenAdmin = signer.borrow<&ContributionPoint.Administrator>(from: ContributionPoint.AdminStoragePath)
            ?? panic("Signer is not the token admin")

        self.tokenReceiver = getAccount(recipient)
            .getCapability(ContributionPoint.ReceiverPublicPath)
            .borrow<&{FungibleToken.Receiver}>()
            ?? panic("Unable to borrow receiver reference")
    }

    execute {
        let minter <- self.tokenAdmin.createNewMinter(allowedAmount: amount)
        let mintedVault <- minter.mintTokens(amount: amount)

        self.tokenReceiver.deposit(from: <-mintedVault)

        destroy minter
    }
}