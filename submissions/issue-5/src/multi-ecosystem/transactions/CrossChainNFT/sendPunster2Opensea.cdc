import PunstersNFT from 0x1a478a7149935b63
import Locker from 0x1a478a7149935b63
import StarRealm from 0x1a478a7149935b63

transaction(hashValue: String) {

    prepare(acct: AuthAccount) {
        let punster <- acct.load<@PunstersNFT.Collection>(from: PunstersNFT.PunsterStoragePath)!;

        let punsterID = punster.id;

        Locker.sendCrossChainNFT(transferToken: <- punster, 
                                signerAddress: acct.address, 
                                id: punsterID, 
                                owner: acct.address.toString(), 
                                hashValue: hashValue);

        acct.unlink(StarRealm.DockerPublicPath);
        acct.unlink(PunstersNFT.IPunsterPublicPath);
        acct.link<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath, target: StarRealm.PortStoragePath);
    }

    execute {
        
    }
}
