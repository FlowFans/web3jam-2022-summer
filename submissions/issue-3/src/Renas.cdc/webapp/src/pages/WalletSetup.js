import React, { useState, useEffect, useMemo } from "react"
import * as fcl from "@onflow/fcl"

import Layout from '../components/Layout'
import useCheckEnabled from '../hooks/useCheckEnabled'

import LoginScreen from './LoginScreen'
import EnableScreen from './EnableScreen'
import EnabledScreen from './EnabledScreen'

const setupTransacion = `\
import NonFungibleToken from 0x1d7e57aa55817448
import CaaArts from 0x98c9c2e548b84d31

transaction {
  prepare(signer: AuthAccount) {
    if signer.borrow<&CaaArts.Collection>(from: CaaArts.CollectionStoragePath) == nil {

      let collection <- CaaArts.createEmptyCollection() as! @CaaArts.Collection

      signer.save(<-collection, to: CaaArts.CollectionStoragePath)

      signer.link<&{NonFungibleToken.CollectionPublic, CaaArts.CollectionPublic}>(
          CaaArts.CollectionPublicPath,
          target: CaaArts.CollectionStoragePath)
    }
  }
}
`

const WalletSetup = () => {
  const [user, setUser] = useState(null)
  const [isProcessing, setProcessing] = useState(false);

  const isEnabled = useCheckEnabled(user && user.addr, isProcessing)

  const isLoggedIn = useMemo(
    () => Boolean(user && user.addr),
    [user]
  )

  const handleSetup = async () => {
    setProcessing(true)

    const blockResponse = await fcl.send([
      fcl.getLatestBlock(),
    ])

    const block = await fcl.decode(blockResponse)

    try {
      const { transactionId } = await fcl.send([
        fcl.transaction(setupTransacion),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.authorizations([
          fcl.currentUser().authorization,
        ]),
        fcl.payer(fcl.currentUser().authorization),
        fcl.ref(block.id),
        fcl.limit(1000),
      ])

      const unsub = fcl
        .tx({ transactionId })
        .subscribe(transaction => {
          if (fcl.tx.isSealed(transaction)) {
            setProcessing(false)
            unsub()
          }
        })
    } catch (error) {
      console.error(error);
      setProcessing(false)
    }
  }

  useEffect(() => {
    fcl
      .currentUser()
      .subscribe(user => {
        setUser({ ...user });
      })
  }, [])

  useEffect(() => {
    if (isLoggedIn && isEnabled === false) {
      handleSetup()
    }
  }, [isLoggedIn, isEnabled])

  if (!isLoggedIn) {
    return (
      <Layout withTitle>
        <LoginScreen />
      </Layout>
    )
  }

  if (isEnabled) {
    return (
      <Layout>
        <EnabledScreen
          address={user.addr}
        />
      </Layout>
    )
  }

  return (
    <Layout>
      <EnableScreen />
    </Layout>
  )
}

export default WalletSetup
