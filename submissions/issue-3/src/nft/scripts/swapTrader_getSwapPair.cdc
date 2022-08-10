import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import SwapTrader from "../../contracts/SwapTrader.cdc"

pub fun main(address: Address, pairID: UInt64): AnyStruct{SwapTrader.SwapPairAttributes}? {
    let ref = getAccount(address).getCapability(SwapTrader.SwapPairListPublicPath)
        .borrow<&{SwapTrader.SwapPairListPublic}>()
        ?? panic("Could not borrow swap-pair list public reference")
    return ref.getSwapPair(pairID)
}
