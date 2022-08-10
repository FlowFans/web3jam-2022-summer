import { useState, useEffect } from 'react'
import * as fcl from '@onflow/fcl'
import * as t from "@onflow/types"
import useTimer from './useTimer'

const scriptArts = `
import NonFungibleToken from 0x1d7e57aa55817448
import CaaArts from 0x98c9c2e548b84d31

pub fun main(address: Address): [CaaArts.Metadata] {
    let collectionRef = getAccount(address).getCapability(CaaArts.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic, CaaArts.CollectionPublic}>()
        ?? panic("Could not borrow collection public reference")

    let ids = collectionRef.getIDs()
    let array: [CaaArts.Metadata] = []

    for id in ids {
        let caaArt = collectionRef.borrowCaaArt(id: id)!
        array.append(caaArt.getMetadata()!)
    }

    return array
}
`

const scriptPass = `
import NonFungibleToken from 0x1d7e57aa55817448
import CaaPass from 0x98c9c2e548b84d31

pub fun main(address: Address): [CaaPass.Metadata] {
    let collectionRef = getAccount(address).getCapability(CaaPass.CollectionPublicPath)
        .borrow<&{NonFungibleToken.CollectionPublic, CaaPass.CollectionPublic}>()
        ?? panic("Could not borrow collection public reference")

    let ids = collectionRef.getIDs()
    let array: [CaaPass.Metadata] = []

    for id in ids {
        let caaPass = collectionRef.borrowCaaPass(id: id)!
        array.append(caaPass.getMetadata()!)
    }

    return array
}
`

export default function useGetPurchaseInfo(address, isProcessing) {
    const [arts, setArts] = useState([])
    const [pass, setPass] = useState([])

    // auto refresh every 10 seconds
    const nonce = useTimer(10000)

    useEffect(() => {
        if (!address) {
            setArts([])
            setPass([])
            return;
        }

        fcl.send([
            fcl.script(scriptArts),
            fcl.args([fcl.arg(address, t.Address)]),
        ])
            .then(fcl.decode)
            .then(setArts);

        fcl.send([
            fcl.script(scriptPass),
            fcl.args([fcl.arg(address, t.Address)]),
        ])
            .then(fcl.decode)
            .then(setPass);
    }, [nonce, address, isProcessing])

    return [arts, pass]
}
