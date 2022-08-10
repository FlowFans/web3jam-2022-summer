import PunstersNFT from "../../contracts/Punsters.cdc"

transaction () {

  prepare(acct: AuthAccount) {

    if let resPunster <- acct.load<@PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
        resPunster.preDestroy();
        destroy resPunster;
    }
  }

  execute {
    
  }
}