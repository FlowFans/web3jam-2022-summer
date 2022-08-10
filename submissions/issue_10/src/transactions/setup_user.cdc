import SinGirlsNFT from 0x01
transaction() {
  prepare(signer: AuthAccount) {
    // Store a `CryptoPoops.Collection` in our account storage.
    signer.save(<- SinGirlsNFT.createEmptyCollection(), to: /storage/SinGirlsCollection)
    

    signer.link<&SinGirlsNFT.Collection{SinGirlsNFT.CollectionPublic}>(/public/SinGirlsCollection, target: /storage/SinGirlsCollection)
  }
}