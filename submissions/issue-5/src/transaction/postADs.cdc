import PunstersNFT from "../contracts/Punsters.cdc"
import NonFungibleToken from "../contracts/NonFungibleToken.cdc"

transaction(description: String, ipfsURL: String) {

  prepare(acct: AuthAccount) {

      if let punsterRef = acct.borrow<&PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
        punsterRef.postADs(description: "Publish an AD. from ".concat(acct.address.toString()).concat(". ").concat(description), 
                                ipfsURL: ipfsURL);
      }
  }

  execute {
    
  }
}