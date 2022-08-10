import StarRealm from "../../contracts/StarRealm.cdc"

transaction () {
    prepare (acct: AuthAccount) {
        if let starPortRef = acct.borrow<&StarRealm.StarPort>(from: StarRealm.PortStoragePath) {
            panic("`StarPort` already exists!");
        }else {
            acct.save(<- StarRealm.createStarPort(), to: StarRealm.PortStoragePath);
            if (acct.getCapability<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath).borrow() == nil) {
                acct.link<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath, target: StarRealm.PortStoragePath);
            }
        }
    }

    execute {

    }
}
