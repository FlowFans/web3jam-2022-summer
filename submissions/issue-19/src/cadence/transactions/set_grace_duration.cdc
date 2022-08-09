import Melody from 0xMelody

transaction(duration: UFix64) {

  prepare(signer: AuthAccount) {
    let adminRef = signer.borrow<&Melody.Admin>(from: Melody.AdminStoragePath)!
    adminRef.setGraceDuration(duration)
  }
}
