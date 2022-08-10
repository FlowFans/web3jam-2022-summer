import PunstersNFT from 0x1a478a7149935b63
import Locker from 0x1a478a7149935b63
import StarRealm from 0x1a478a7149935b63

transaction(id: UInt64, answer: String) {

    prepare(acct: AuthAccount) {
        Locker.claimNFT(id: id, answer: answer);

        let starPortRef = acct.borrow<&StarRealm.StarPort>(from: StarRealm.PortStoragePath)!;

        let punster <- starPortRef.sailing()! as! @PunstersNFT.Collection;

        acct.unlink(StarRealm.DockerPublicPath);
        acct.save(<-punster, to: PunstersNFT.PunsterStoragePath);

        acct.link<&{PunstersNFT.IPunsterPublic}>(PunstersNFT.IPunsterPublicPath, target: PunstersNFT.PunsterStoragePath);
        acct.link<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath, target: PunstersNFT.PunsterStoragePath);
    }

    execute {
        
    }
}
