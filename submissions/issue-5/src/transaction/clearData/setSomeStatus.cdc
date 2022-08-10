import PunstersNFT from "../../contracts/Punsters.cdc"

transaction (pid: UInt64) {

    prepare(acct: AuthAccount) {
        
    }

    execute {
        PunstersNFT.setPunsterID(id: 1000000);
    }
}