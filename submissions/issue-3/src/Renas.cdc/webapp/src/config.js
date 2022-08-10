import * as fcl from "@onflow/fcl"

fcl.config()
  .put("accessNode.api", "https://access-mainnet-beta.onflow.org")
  // .put("accessNode.api", "https://access-testnet.onflow.org")
  .put("challenge.handshake", "https://flow-wallet.blocto.app/authn")
  // .put("challenge.handshake", "http://localhost:8702/authn")
