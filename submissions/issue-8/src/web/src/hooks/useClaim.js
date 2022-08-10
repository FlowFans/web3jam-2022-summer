import * as fcl from "@onflow/fcl"
import * as t from "@onflow/types"
import {useRouter} from "next/router"
import {useEffect, useRef, useState} from "react"
import useTransactionsContext from "src/components/Transactions/useTransactionsContext"
import {paths} from "src/global/constants"
import publicConfig from "src/global/publicConfig"
import useRequest from "src/hooks/useRequest"
import {EVENT_ITEM_MINTED, getKittyItemsEventByType} from "src/util/events"
import {useSWRConfig} from "swr"
import useAppContext from "src/hooks/useAppContext"
import analytics from "src/global/analytics"
import CLAIM_BADGES_SCRIPT from "cadence/transactions/claim_badges.cdc"

// Mints an item and lists it for sale. The item is minted on the service account.
export default function useClaim() {
  const {currentUser} = useAppContext()
  const {addTransaction} = useTransactionsContext()
  const [_mintState, executeMintRequest] = useRequest()
  const txStateSubscribeRef = useRef()
  const txSealedTimeout = useRef()

  const router = useRouter()
  const {mutate} = useSWRConfig()

  const [isMintingLoading, setIsMintingLoading] = useState(false)
  const [transactionStatus, setTransactionStatus] = useState(null)
  const transactionAction = isMintingLoading ? "Minting Item" : "Processing"

  const resetLoading = () => {
    setIsMintingLoading(false)
  }

  const onTransactionSealed = tx => {
    resetLoading()
    setTransactionStatus(tx)
  }

  const mintAndList = async (reqValues) => {

    console.log("reqValues:" + reqValues.recipient)

    setIsMintingLoading(true)

    const newTxId = await fcl.mutate({
      cadence: CLAIM_BADGES_SCRIPT,
      args: (arg, t) => [
        arg(publicConfig.contractKittyItems, t.Address),
        arg(reqValues.recipient, t.Address),
        arg(reqValues.claimCode, t.String),
      ],
      limit: 9999,
    }).catch(()=>{setIsMintingLoading(false)});

    console.log(newTxId);
    if(newTxId) {
      txStateSubscribeRef.current = fcl.tx(newTxId).subscribe(tx => {
            console.log("tx" + JSON.stringify(tx))
            if (fcl.tx.isSealed(tx)) onTransactionSealed(tx)
          })
    }
  }

  useEffect(() => {
    return () => {
      if (!!txStateSubscribeRef.current) txStateSubscribeRef.current()
      clearTimeout(txSealedTimeout.current)
    }
  }, [])

  const isLoading = isMintingLoading
  return [{isLoading, transactionAction, transactionStatus}, mintAndList]
  
}
