import { config } from "@onflow/fcl";
import * as fcl from "@onflow/fcl";
import { useEffect, useState } from 'react'

import CHAIN_CONFIG from "../../../flow/config.json"

if (CHAIN_CONFIG.env== "testnet") {
  config({
    "accessNode.api": "https://rest-testnet.onflow.org",
    "discovery.wallet": "https://fcl-discovery.onflow.org/testnet/authn/",
    "0x01": "0x83f8ed4318375647"
  })
} else {
  config({
    "accessNode.api": "http://localhost:8888",
    "discovery.wallet": "http://localhost:8701/fcl/authn",
    "0x01": "0xf8d6e0586b0a20c7"
  })
}


export function useCurrentUser() {
  const [user, setUser] = useState({ loggedIn: false })

  useEffect(() => {
    fcl.currentUser().subscribe(setUser)
  }, [])
  return [user, user?.addr != null, fcl.authenticate, fcl.unauthenticate]
}

