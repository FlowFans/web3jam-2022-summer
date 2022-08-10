import FUSD from 0xFUSD
import FungibleToken from 0xFungibleToken

transaction() {
    prepare(acct: AuthAccount) {
        if(acct.borrow<&FUSD.Vault>(from: /storage/fusdVault) == nil) {
             acct.save(<-FUSD.createEmptyVault(), to: /storage/fusdVault)
             acct.link<&FUSD.Vault{FungibleToken.Receiver}>(
                /public/fusdReceiver,
                target: /storage/fusdVault
              )
             acct.link<&FUSD.Vault{FungibleToken.Balance}>(
                 /public/fusdBalance,
                target: /storage/fusdVault
              )
         }
    }
}