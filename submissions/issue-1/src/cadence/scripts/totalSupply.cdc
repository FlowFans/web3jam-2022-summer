import WakandaPass from 0x02
// This script reads the current number of cogito that have been minted
// from the Cogito contract and returns that number to the caller
// Returns: UInt64
// Number of cogito minted from Cogito contract
pub fun main(): UInt64 {
    return WakandaPass.totalSupply
}
