import StarRealm from "../../contracts/StarRealm.cdc"

transaction () {
    prepare (acct: AuthAccount) {
        if let starPortRef = acct.borrow<&StarRealm.StarPort>(from: StarRealm.PortStoragePath) {
            acct.unlink(StarRealm.DockerPublicPath);
            let rst = acct.link<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath, target: StarRealm.PortStoragePath);
            if rst == nil {
                panic("Link failed!");
            }
        }else {
            panic("`StarPort` does not exist!");
        }
    }

    execute {

    }
}
