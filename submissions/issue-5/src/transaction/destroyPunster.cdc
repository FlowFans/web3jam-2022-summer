import PunstersNFT from "../contracts/Punsters.cdc"
import NonFungibleToken from "../contracts/NonFungibleToken.cdc"

transaction () {

  prepare(acct: AuthAccount) {

    if let punsterRef = acct.borrow<&PunstersNFT.Collection>(from : PunstersNFT.PunsterStoragePath) {
        punsterRef.preDestroy();
    }

    if let resPunster <- acct.load<@PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
        destroy resPunster;
    }
  }

  execute {
    
  }
}