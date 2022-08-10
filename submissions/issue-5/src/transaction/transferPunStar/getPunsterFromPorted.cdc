import StarRealm from "../../contracts/StarRealm.cdc"
import PunstersNFT from "../../contracts/Punsters.cdc"

transaction () {
    prepare (acct: AuthAccount) {
        if let starPortRef = acct.borrow<&StarRealm.StarPort>(from: StarRealm.PortStoragePath) {
             if let punsterFromPort <- starPortRef.sailing() {
                    // Check if ported thing is `Punster`
                    if let portedThing <- punsterFromPort as? @PunstersNFT.Collection{
                        acct.save(<- portedThing, to: PunstersNFT.PunsterStoragePath);
                        acct.unlink(StarRealm.DockerPublicPath);
                        acct.link<&{PunstersNFT.IPunsterPublic}>(PunstersNFT.IPunsterPublicPath, target: PunstersNFT.PunsterStoragePath);
                        acct.link<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath, target: PunstersNFT.PunsterStoragePath);

                        let punsterRef = acct.borrow<&PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath)!;
                        PunstersNFT.updateRegisteredPunster(punster: punsterRef);

                    } else {
                        panic("Ported thing is not `Punster`");
                    }
             } else {
                panic("Nothing ported")
             }
        }else {
            panic("`StarPort` does not exist!");
        }
    }

    execute {

    }
}