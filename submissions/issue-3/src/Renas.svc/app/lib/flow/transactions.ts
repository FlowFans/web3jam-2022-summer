import * as fcl from '@onflow/fcl'
import { sendTrx } from './utils'
import { getSupportTokenConfig, network } from './constants'
import { authz } from '~/lib/flow/utils/authz'

const withdraw_domain_vault = (opt: any) => {
  const { token } = opt

  const tokenConfig = getSupportTokenConfig()[token]
  const { type, storagePath } = tokenConfig
  const typeArr = type.split('.')
  const contractAddr = typeArr[1]
  const contractName = typeArr[2]

  return fcl.cdc`
  import Flowns from 0xFlowns
  import Domains from 0xDomains
  import FungibleToken from 0xFungibleToken
  import ${contractName} from 0x${contractAddr}

  transaction(nameHash: String, key: String, amount: UFix64) {
    var domain: &{Domains.DomainPrivate}
    var vaultRef: &${contractName}.Vault
    prepare(account: AuthAccount) {
      var domain: &{Domains.DomainPrivate}? = nil
      let collectionPrivate = account.borrow<&{Domains.CollectionPrivate}>(from: Domains.CollectionStoragePath) ?? panic("Could not find your domain collection cap")
      
      let id = Domains.getDomainId(nameHash)
      if id != nil && !Domains.isDeprecated(nameHash: nameHash, domainId: id!) {
        domain = collectionPrivate.borrowDomainPrivate(id!)
      }

      self.domain = domain!
      self.vaultRef = account.borrow<&${contractName}.Vault>(from: ${storagePath})
      ?? panic("Could not borrow reference to the owner's Vault!")
    }
    execute {
      self.vaultRef.deposit(from: <- self.domain.withdrawVault(key: key, amount: amount))
    }
  }
`
}

const init_ft_token = (opt: any) => {
  const { token } = opt

  const tokenConfig = getSupportTokenConfig()[token]
  const { type, storagePath, publicReceiverPath, publicBalPath } = tokenConfig
  const typeArr = type.split('.')
  const contractAddr = typeArr[1]
  const contractName = typeArr[2]
  return fcl.cdc`
  import FungibleToken from 0xFungibleToken
  import ${contractName} from 0x${contractAddr}

  transaction {

    prepare(signer: AuthAccount) {

        // It's OK if the account already has a Vault, but we don't want to replace it
        if(signer.borrow<&${contractName}.Vault>(from: ${storagePath}) != nil) {
            return
        }
        // Create a new Token Vault and put it in storage
        signer.save(<- ${contractName}.createEmptyVault(), to: ${storagePath})

        // Create a public capability to the Vault that only exposes
        // the deposit function through the Receiver interface
        signer.link<&${contractName}.Vault{FungibleToken.Receiver}>(
            ${publicReceiverPath},
            target: ${storagePath}
        )

        // Create a public capability to the Vault that only exposes
        // the balance field through the Balance interface
        signer.link<&${contractName}.Vault{FungibleToken.Balance}>(
            ${publicBalPath},
            target: ${storagePath}
        )
    }
  }
`
}

const transfer_ft = (opt: any) => {
  const { token } = opt

  const tokenConfig = getSupportTokenConfig()[token]
  const { type, storagePath, publicReceiverPath } = tokenConfig
  const typeArr = type.split('.')
  const contractAddr = typeArr[1]
  const contractName = typeArr[2]
  return fcl.cdc`
  import FungibleToken from 0xFungibleToken
  import Domains from 0xDomains
  import ${contractName} from 0x${contractAddr}

  transaction(to: Address, amount: UFix64) {
    let sentVault: @FungibleToken.Vault
    prepare(signer: AuthAccount) {
      let vaultRef = signer.borrow<&${contractName}.Vault>(from: ${storagePath})
      ?? panic("Err owner Vault!")
      self.sentVault <- vaultRef.withdraw(amount: amount)
    }

    execute {
      let recipient = getAccount(to)
      let receiverRef = recipient.getCapability(${publicReceiverPath})!.borrow<&{FungibleToken.Receiver}>()
      ?? panic("Err recipient Vault")
      receiverRef.deposit(from: <-self.sentVault)
    }
  }
`
}

const template_trx = fcl.cdc`
import Flowns from 0xFlowns
import Domains from 0xDomains
import FungibleToken from 0xFungibleToken
import NonFungibleToken from 0xNonFungibleToken

transaction(nameHashs: [String], duration: UFix64, refer: Address) {
  let vaultRef: &FungibleToken.Vault
  let prices: {String:{Int: UFix64}}
  let collectionCap: &{Domains.CollectionPublic}
  prepare(account: AuthAccount) {
    self.collectionCap = account.getCapability<&{Domains.CollectionPublic}>(Domains.CollectionPublicPath).borrow()!

    self.vaultRef = account.borrow<&FungibleToken.Vault>(from: /storage/flowTokenVault)
          ?? panic("Could not borrow owner's Vault reference")
    
    // self.vault <- vaultRef.withdraw(amount: amount)

    self.prices = {}
    let roots: {UInt64: Flowns.RootDomainInfo}? = Flowns.getAllRootDomains()
    let keys = roots!.keys
    for key in keys {
      let root = roots![key]!
      self.prices[root.name] = root.prices
    }
  }

  execute {
    var idx = 1
    for nameHash in nameHashs {
      idx = idx + 1
      let id = Domains.getDomainId(nameHash)
      let address = Domains.getRecords(nameHash)!
      let collectionCap = getAccount(address).getCapability<&{Domains.CollectionPublic}>(Domains.CollectionPublicPath).borrow()!
      let domain = collectionCap.borrowDomain(id: id!)
      var len = domain.name.length
      if len > 10 {
        len = 10
      }
      let price = self.prices[domain.parent]![len]!
      if idx != nameHashs.length {
        Flowns.renewDomainWithNameHash(nameHash: nameHash, duration: duration, feeTokens: <- self.vaultRef.withdraw(amount: price * duration), refer: refer)
      } else {
        Flowns.renewDomainWithNameHash(nameHash: nameHash, duration: duration, feeTokens: <- self.vaultRef.withdraw(amount: price * duration), refer: refer)
      }
    }
  }
}
 `

const transfer_ft_with_path = (opt: any = {}) => {
  const { vaultId, receiverId } = opt
  return fcl.cdc`
  import FungibleToken from 0xFungibleToken

  transaction(to: Address, amount: UFix64) {
    let sentVault: @FungibleToken.Vault
    prepare(signer: AuthAccount) {
      let vaultPath = StoragePath(identifier: "${vaultId}")!
      let vault = signer.borrow<&{FungibleToken.Provider}>(from: vaultPath)!
      self.sentVault <- vault.withdraw(amount: amount)
    }

    execute {
      let recipient = getAccount(to)
      let receiverRef = recipient.getCapability(PublicPath(identifier: "${receiverId}")!)!.borrow<&{FungibleToken.Receiver}>()
      ?? panic("Err recipient Vault")
      receiverRef.deposit(from: <-self.sentVault)
    }
  }
`
}

export const transactions: any = {
  template_trx,
  transfer_ft,
  init_ft_token,
  transfer_ft_with_path,
}

export const send = async (key: string, args: any = [], opt = {}, onlyTrxId = false) => {
  try {
    let trxScript = transactions[key]
    if (typeof trxScript == 'function') {
      trxScript = trxScript(opt)
    }
    const trxId = await sendTrx(trxScript, args)
    if (onlyTrxId) {
      return trxId
    }
    const txStatus = await fcl.tx(trxId).onceSealed()
    return txStatus
  } catch (error) {
    console.log(error)
    return null
  }
}

export const serverSend = async (key: string, args: any = [], opt = {}, onlyTrxId = false) => {
  try {
    let trxScript = transactions[key]
    if (typeof trxScript == 'function') {
      trxScript = trxScript(opt)
    }
    const trxId = await sendTrx(trxScript, args, { limit: 9999, authz: authz })
    if (onlyTrxId) {
      return trxId
    }
    const txStatus = await fcl.tx(trxId).onceSealed()
    return txStatus
  } catch (error) {
    console.log(error)
    return null
  }
}
