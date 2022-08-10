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
import MINT_BADGES_SCRIPT from "cadence/transactions/mint_badges.cdc"

// Mints an item and lists it for sale. The item is minted on the service account.
export default function useMintAndList() {
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

    //recipient, name, description, badge_image, max, royalty_cut, royalty_cut, royalty_description, royalty_receiver, externalURL
    let image = reqValues.badge_image.file.response

    setIsMintingLoading(true)

    console.log("reqValues:" + reqValues.recipient)

    const newTxId = await fcl.mutate({
      cadence: MINT_BADGES_SCRIPT,
      args: (arg, t) => [
        arg(publicConfig.contractKittyItems, t.Address),
        arg(reqValues.recipient, t.Address),
        arg(reqValues.name, t.String),
        arg(reqValues.description, t.String),
        arg(image, t.String),
        arg("image/nft_img.png", t.String),
        arg(reqValues.max, t.UInt64),
        arg(reqValues.claim_code, t.Optional(t.String)),
        arg((reqValues.royalty_cut/100).toFixed(4), t.Optional(t.UFix64)),
        arg(reqValues.royalty_description, t.Optional(t.String)),
        arg(reqValues.royalty_receiver, t.Optional(t.Address)),
        arg(reqValues.externalURL, t.Optional(t.String)),
      ],
      limit: 9999,
    }).catch(()=>{setIsMintingLoading(false)});

    console.log(newTxId);
    if(newTxId) {
      txStateSubscribeRef.current = fcl.tx(newTxId).subscribe(tx => {
            console.log("tx.status:" + tx.status)
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
