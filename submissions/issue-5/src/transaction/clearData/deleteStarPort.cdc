import StarRealm from "../../contracts/StarRealm.cdc"

transaction () {

  prepare(acct: AuthAccount) {

    if let resStarPort <- acct.load<@StarRealm.StarPort>(from: StarRealm.PortStoragePath) {
        destroy resStarPort;
    }
  }

  execute {
    
  }
}