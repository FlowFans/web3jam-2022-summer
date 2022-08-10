import NFTCrossChain from 0x1a478a7149935b63;

transaction(){
  let signer: AuthAccount;
  prepare(signer: AuthAccount){
    self.signer = signer;
  }

  execute {
    NFTCrossChain.resetSentMessageVault();
  }
}