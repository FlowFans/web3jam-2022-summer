import * as fcl from '@onflow/fcl'
import { execScript } from './utils'
import {} from './constants'

const check_domain_collection = fcl.cdc`
import Domains from 0xDomains

pub fun main(address: Address) : Bool {
    return getAccount(address).getCapability<&{Domains.CollectionPublic}>(Domains.CollectionPublicPath).check()
}`

const query_ft_balance = (opt: any) => {
  const { tokenConfig } = opt
  const { type, publicBalPath } = tokenConfig
  const typeArr = type.split('.')
  const contractAddr = typeArr[1]
  const contractName = typeArr[2]

  return fcl.cdc`
  import FungibleToken from 0xFungibleToken
  import ${contractName} from 0x${contractAddr}
  
  pub fun main(address: Address): UFix64? {
    let account = getAccount(address)
    var balance :UFix64? = nil
    if let vault = account.getCapability(${publicBalPath}).borrow<&{FungibleToken.Balance}>() {
      balance = vault.balance
    }
    return balance
  }`
}

const check_emerald_id = fcl.cdc`
import EmeraldID from 0xEmeraldID

pub fun main(user: Address): UInt64? {
  let info = getAccount(user).getCapability(EmeraldID.InfoPublicPath)
              .borrow<&EmeraldID.Info{EmeraldID.InfoPublic}>()
  return info?.uuid
}
`

const get_discord_id_by_address = fcl.cdc`
import EmeraldIdentity from 0xEmeraldIdentity

pub fun main(user: Address): String? {
  return EmeraldIdentity.getDiscordFromAccount(account: user)
}
`

const get_address_by_dicord_id = fcl.cdc`
import EmeraldIdentity from 0xEmeraldIdentity

pub fun main(id: String): Address? {
  return EmeraldIdentity.getAccountFromDiscord(discordID: id)
}
`

export const scripts: any = {
  check_domain_collection,
  check_emerald_id,
  get_address_by_dicord_id,
  get_discord_id_by_address,
}

export const query = async (key: string, args = [], opt = {}) => {
  let script = scripts[key]
  if (typeof script == 'function') {
    script = script(opt)
  }
  const result = await execScript(script, args)
  return result
}
