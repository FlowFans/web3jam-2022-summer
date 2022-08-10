
transaction(name: String, code: String, appVaultAddress: Address, nftName: String, appName: String) {
  let signer: AuthAccount

  prepare(signer: AuthAccount) {
    self.signer = signer
  }

  execute {
    self.signer.contracts.add(
      name: name,
      code: code.decodeHex(),
      [0.01, 3600.0, 100.0, 0.2, 0.4, appVaultAddress, 0xbf69452890a74d8f, nftName, appName]
    )
  }
}