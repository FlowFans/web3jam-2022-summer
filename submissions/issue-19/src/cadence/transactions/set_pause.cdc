import Melody from 0xMelody

transaction(flag: Bool) {

  prepare(signer: AuthAccount) {
    let adminRef = signer.borrow<&Melody.Admin>(from: Melody.AdminStoragePath)!
    adminRef.setPause(flag)
  }
}
