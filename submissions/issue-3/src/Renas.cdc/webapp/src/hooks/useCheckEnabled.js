import { useState, useEffect } from 'react'
import * as fcl from '@onflow/fcl'
import * as t from "@onflow/types"
import useTimer from './useTimer'

const script = `
import NonFungibleToken from 0x1d7e57aa55817448
import CaaArts from 0x98c9c2e548b84d31

pub fun main(address: Address): Bool {
    return getAccount(address).getCapability(CaaArts.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic}>() != nil
}
`

export default function useGetPurchaseInfo(address, isProcessing) {
    const [isEnabled, setIsEnabled] = useState(null)

    // auto refresh every 10 seconds
    const nonce = useTimer(10000)

    useEffect(() => {
        if (!address) {
            setIsEnabled(null)
            return;
        }

        fcl.send([
            fcl.script(script),
            fcl.args([fcl.arg(address, t.Address)]),
        ])
            .then(fcl.decode)
            .then(setIsEnabled);
    }, [nonce, address, isProcessing])

    return isEnabled
}
