import PunstersNFT from "../../contracts/Punsters.cdc"
import StarRealm from "../../contracts/StarRealm.cdc"

transaction (to: Address) {
    prepare (acct: AuthAccount) {
        // Check StarPort exesting
        if let starPortRef = acct.borrow<&StarRealm.StarPort>(from: StarRealm.PortStoragePath) {
            // Check saved `Punster`
            if let punster <- acct.load<@PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath) {
                // Check target docker
                if let targetDocker = StarRealm.getStarDockerFromAddress(addr: to) {
                    // Check docking acceptable
                    let rst <- targetDocker.docking(nft: <- punster);
                    if rst != nil {
                        panic("Transfer failed, the target `docker` does not accept!");
                    } else {
                        destroy rst;
                    }
                } else {
                    panic("Target docker does not exists!")
                }

                acct.unlink(PunstersNFT.IPunsterPublicPath);
                acct.unlink(StarRealm.DockerPublicPath);
            }else {
                if let punsterFromPort <- starPortRef.sailing() {
                    // Check if ported thing is `Punster`
                    if let portedThing <- punsterFromPort as? @PunstersNFT.Collection{
                        // Check target docker
                         if let targetDocker = StarRealm.getStarDockerFromAddress(addr: to) {
                            // Check docking acceptable
                            let rst <- targetDocker.docking(nft: <- portedThing);
                            if rst != nil {
                                panic("Transfer failed, the target `docker` does not accept!");
                            } else {
                                destroy rst;
                            }
                        } else {
                            panic("Target docker does not exists!")
                        }
                    }else {
                        panic("No `Punster` exists!");
                    }
                }else {
                    panic("No `Punster` exists!");
                }
            }

            // Link public docker to `StarPort` for receiving punster
            acct.link<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath, target: StarRealm.PortStoragePath);
        }else {
            panic("Create `StarPort` first!");
        }
    }

    execute {

    }
}
