import FungibleToken from "../../contracts/FungibleToken.cdc"
import ContributionPoint from "../../contracts/ContributionPoint.cdc"

transaction {
    let tokenAdmin: &ContributionPoint.Administrator

    prepare(signer: AuthAccount) {
        self.tokenAdmin = signer.borrow<&ContributionPoint.Administrator>(from: ContributionPoint.AdminStoragePath)
            ?? panic("Signer is not the token admin")
    }

    execute {
        let pauser <- self.tokenAdmin.createNewPauser()
        pauser.unpause()
        destroy pauser
    }
}