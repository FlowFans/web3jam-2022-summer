import FungibleToken from 0xFungibleToken

pub fun main(address: Address): UFix64? {
  let account = getAccount(address)

  if let vaultRef = account.getCapability(/public/kibbleBalance002).borrow<&{FungibleToken.Balance}>() {
    return vaultRef.balance
  } 
  return nil
  
}