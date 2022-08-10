import PunstersNFT from "../contracts/Punsters.cdc"
import NonFungibleToken from "../contracts/NonFungibleToken.cdc"
import StarRealm from "../contracts/StarRealm.cdc"

transaction (description: String, ipfsURL: String) {

  prepare(acct: AuthAccount) {

      let punster <- PunstersNFT.registerPunster(addr: acct.address, 
                                                description: "Punster: ".concat(acct.address.toString()).concat(". ").concat(description), 
                                                ipfsURL: ipfsURL);

      acct.save(<-punster, to: PunstersNFT.PunsterStoragePath);
      acct.link<&{PunstersNFT.IPunsterPublic}>(PunstersNFT.IPunsterPublicPath, target: PunstersNFT.PunsterStoragePath);
      
      // if a punster is registered, clear the `StarRealm.DockerPublicPath` first.
      acct.unlink(StarRealm.DockerPublicPath);
      acct.link<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath, target: PunstersNFT.PunsterStoragePath);
  }

  execute {
    
  }
}
