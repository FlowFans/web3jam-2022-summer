import { send, decode, script, cdc, args } from "@onflow/fcl"

const CODE = cdc`
import WakandaPass from 0xf5c21ffd3438212b

pub fun main(): UInt64 {
    return WakandaPass.totalSupply
}

`

const scriptTotalSupply = () => {
  return send([script(CODE), args([])]).then(decode)
}

export default scriptTotalSupply