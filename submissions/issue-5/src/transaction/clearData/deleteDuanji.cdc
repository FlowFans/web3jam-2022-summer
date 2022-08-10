import PunstersNFT from "../../contracts/Punsters.cdc"

transaction (duanjiID: UInt64) {

  prepare(acct: AuthAccount) {

    if let punsterRef = acct.borrow<&PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
        let duanjiRes <- punsterRef.withdraw(withdrawID: duanjiID);
        destroy duanjiRes;
    }
  }

  execute {
    
  }
}