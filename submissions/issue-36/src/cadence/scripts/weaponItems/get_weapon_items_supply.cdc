import WeaponItems1 from "../../contracts/WeaponItems1.cdc"

// This scripts returns the number of WeaponItems1 currently in existence.

pub fun main(): UInt64 {    
    return WeaponItems1.totalSupply
}
