import OverluPackage from 0xOverluPackage
import FlowToken  from 0xFlowToken

transaction() {
    let minter: &OverluPackage.NFTMinter

    prepare(signer: AuthAccount) {
        self.minter = signer
        .borrow<&OverluPackage.NFTMinter>(from: OverluPackage.MinterStoragePath)
        ?? panic("Signer is not the nft admin")
    }

    execute {
        self.minter.setVault(<- FlowToken.createEmptyVault())
    }
}
 