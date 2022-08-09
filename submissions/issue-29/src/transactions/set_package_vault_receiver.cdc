import OverluPackage from 0xOverluPackage
import FungibleToken  from 0xFungibleToken
import FlowToken  from 0xFlowToken

transaction(addr: Address) {
    let minter: &OverluPackage.NFTMinter
    let cap: Capability<&{FungibleToken.Receiver}>
    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)
        ?? panic("Signer is not the nft admin")

        self.cap = getAccount(addr).getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver)
    }

    execute {
        self.minter.setVaultReceiver(self.cap)
    }
}
 