import FungibleToken from "./FungibleToken.cdc"
import NonFungibleToken from "./NonFungibleToken.cdc"
import MetadataViews from "./MetadataViews.cdc"
import WeaponItems1 from "./WeaponItems1.cdc"
import GnftToken from "./GnftToken.cdc"
import FlowToken from "./FlowToken.cdc"

// import FungibleToken from 0x9a0766d93b6608b7
// import NonFungibleToken from 0x631e88ae7f1d7c20
// import MetadataViews from 0x631e88ae7f1d7c20
// import WeaponItems1 from 0xbf69452890a74d8f
// import GnftToken from 0xbf69452890a74d8f
// import FlowToken from 0x7e60df042a9c0868

pub contract NFTRentalRegular {

  // Events
  pub event ListForRent(tokenId: UInt64, lessor: Address, endTime: UFix64, rentFee: UFix64)
  pub event RentFrom(tokenId: UInt64, tenant: Address, fee: UFix64)
  pub event Claim(tokenId: UInt64, lessor: Address, tenant: Address, claimer: Address, rentFee: UFix64)
  pub event FinishRent(tokenId: UInt64, lessor: Address, tenant: Address, fee: UFix64)

  // Named Path
  pub let flowStoragePath: StoragePath
  pub let flowPublicPath: PublicPath
  pub let gnftStoragePath: StoragePath
  pub let gnftPublicPath: PublicPath
  pub let promiseCollectionStoragePath: StoragePath
  pub let promiseCollectionPublicPath: PublicPath

  // Variables
  pub var platformFeeRate: UFix64
  pub var minRentPeriod: UFix64
  pub var guarantee: UFix64
  pub let claimerPercent: UFix64
  pub let appPercent: UFix64
  pub let appVaultRef: Capability<&{FungibleToken.Receiver}>
  pub let platformVaultRef: Capability<&{FungibleToken.Receiver}>
  pub let nftName: String
  pub let appName: String
  pub let viewTypes: {UInt64: [Type]}
  pub let views: {UInt64: {Type: AnyStruct}}

  // Structs interface
  pub struct interface PromisePublic {
    pub let tokenId: UInt64
    pub let initialOwner: Address
    pub var rented: Bool
    pub var kept: Bool
    pub var claimed: Bool
    pub var tenant: Address?
    pub let rentFee: UFix64
    pub let endTime: UFix64
  }

  // Structs
  pub struct Promise: PromisePublic {
    pub let tokenId: UInt64
    pub let initialOwner: Address
    pub var rented: Bool
    pub var kept: Bool
    pub var claimed: Bool
    pub var tenant: Address?
    pub let rentFee: UFix64
    pub let endTime: UFix64
    
    init(acct: AuthAccount, tokenId: UInt64, endTime: UFix64, rentFee: UFix64) {
      pre {
         WeaponItems1.fetch(acct.address, itemID: tokenId)! != nil: "Wrone user"
      }
      self.rented = false
      self.kept = true
      self.claimed = false
      self.initialOwner = acct.address
      self.rentFee = rentFee
      self.tokenId = tokenId
      self.endTime = endTime
      self.tenant = nil
    }

    pub fun fill(tenant: Address) {
      pre {
        self.tenant == nil: "Already rent out 1"
        !self.rented: "Already rent out 2"
      }
      self.tenant = tenant
      self.rented = true
    }

    pub fun whenKept() {
      pre {
        // Check if rented
        self.rented: "Not rented"

        // Check if kept
        self.kept: "Not kept"
        WeaponItems1.fetch(self.initialOwner, itemID: self.tokenId)! != nil: "Can not claim"

        // Check if in rent period
        getCurrentBlock().timestamp >= self.endTime: "Still in rent period"
      }

      self.rented = false
    }

    pub fun whenBroken() {
      pre {
        // Check if rented
        self.rented: "Not in rent"
        // Check if in rent period
        getCurrentBlock().timestamp < self.endTime: "Rent time finished"
        // Check if the promise has been broken
        WeaponItems1.fetch(self.initialOwner, itemID: self.tokenId)! == nil: "Can not claim"
      }
      self.kept = false
      self.claimed = true
    }
  }

  // Resource interface
  pub resource interface PromiseCollectionPublic {
    pub fun getPromise(tokenId: UInt64): Promise?
    pub fun getAllPromises(): [Promise?]
    pub fun getUserRented(user: Address): [Promise?]
    pub fun makePromise(acct: AuthAccount, tokenId: UInt64, endTime: UFix64, rentFee: UFix64, guaranteePayment: @GnftToken.Vault) {
      pre {
        // Check if the acct owns the nft
        WeaponItems1.fetch(acct.address, itemID: tokenId)! != nil: "Wrone user"
      }
    }
    pub fun fillPromise(tokenId: UInt64, tenant: Address, rentFee: @FungibleToken.Vault)
    pub fun endPromise(tokenId: UInt64): Promise?
    pub fun claim(tokenId: UInt64, claimerVault: Capability<&{FungibleToken.Receiver}>)
  }

  // Resources
  pub resource PromiseCollection: PromiseCollectionPublic {
    pub let platformFeeRate: UFix64
    pub let minRentPeriod: UFix64
    pub let claimerPercent: UFix64
    pub let appPercent: UFix64
    pub let originOwner: {UInt64: Address}
    pub let appReciever: Capability<&{FungibleToken.Receiver}>
    pub let platformReciever: Capability<&{FungibleToken.Receiver}>

    access(self) var promises: {UInt64: Promise}
    access(self) var payments: @{UInt64: GnftToken.Vault}
    access(self) var rentFees: @{UInt64: FungibleToken.Vault}
    access(self) var userTokens: {Address: [UInt64]}
    access(self) var userRented: {Address: {UInt64: UFix64}}

    init(platformFeeRate: UFix64, minRentPeriod: UFix64, claimerPercent: UFix64, appPercent: UFix64, appReciever: Capability<&{FungibleToken.Receiver}>, platformReciever: Capability<&{FungibleToken.Receiver}>) {
      self.platformFeeRate = platformFeeRate
      self.minRentPeriod = minRentPeriod
      self.claimerPercent = claimerPercent
      self.appPercent = appPercent
      self.originOwner = {}
      self.promises = {}
      self.payments <- {}
      self.rentFees <- {}
      self.userTokens = {}
      self.userRented = {}
      self.appReciever = appReciever
      self.platformReciever = platformReciever
    }

    // Read Functions
    pub fun getPromise(tokenId: UInt64): Promise? {
      return self.promises[tokenId]
    }

    pub fun getAllPromises(): [Promise?] {
      let promises: [Promise?] = []
      for tokenId in self.promises.keys {
        if let promise = self.getPromise(tokenId: tokenId) {
          promises.append(promise)
        }
      }

      return promises
    }

    pub fun getUserRented(user: Address): [Promise?] {
      let promises: [Promise?] = []
      if self.userRented.containsKey(user) {
        for tokenId in self.userRented[user]!.keys {
          if self.userRented[user]![tokenId]! > getCurrentBlock().timestamp {
            if let promise = self.getPromise(tokenId: tokenId) {
              promises.append(promise)
            }
          }
        }
      }
      return promises
    }

    // Write Functions
    pub fun makePromise(acct: AuthAccount, tokenId: UInt64, endTime: UFix64, rentFee: UFix64, guaranteePayment: @GnftToken.Vault) {
      pre {
        // Check if the promise has already been made
        // TODO: One case could be optimised:
        // former lessor kept promise but not withdraw guarantee, if he still ownes the nft, he can make a new promise
        self.promises[tokenId] == nil: "promise should be empty"
        self.payments[tokenId] == nil: "payment should be empty"
      }

      self.originOwner[tokenId] = acct.address
      self.promises[tokenId] = Promise(acct: acct, tokenId: tokenId, endTime: endTime, rentFee: rentFee)
      self.payments[tokenId] <-! guaranteePayment
      if !self.userTokens.containsKey(acct.address) {
        self.userTokens.insert(key: acct.address, [])
      }
      self.userTokens[acct.address]!.append(tokenId)
    }

    pub fun fillPromise(tokenId: UInt64, tenant: Address, rentFee: @FungibleToken.Vault) {
      pre {
        self.promises[tokenId] != nil: "promise should not be empty"
        self.promises[tokenId]!.initialOwner != tenant: "Wrone lessor"
        !self.promises[tokenId]!.rented! : "Already rented out"
        rentFee.balance >= self.promises[tokenId]!.rentFee * (1.0 + self.platformFeeRate): "Not sufficient balance"
      }
      let requiredFee = self.promises[tokenId]!.rentFee
      self.promises[tokenId]!.fill(tenant: tenant)
      let platformReceiverRef = self.platformReciever.borrow() ?? panic("can not borrow platform receiver")
      platformReceiverRef.deposit(from: <- rentFee.withdraw(amount: rentFee.balance - requiredFee))
      self.rentFees[tokenId] <-! rentFee.withdraw(amount: requiredFee)
      if (!self.userRented.containsKey(tenant)) {
        self.userRented[tenant] = {}
      } else {
        for tId in self.userRented[tenant]!.keys {
          if self.userRented[tenant]![tId]! < getCurrentBlock().timestamp {
            self.userRented[tenant]!.remove(key: tId)
          }
        }
      }
      self.userRented[tenant]!.insert(key: tokenId, self.promises[tokenId]!.endTime)
      destroy rentFee
    }

    pub fun endPromise(tokenId: UInt64): Promise? {
      pre {
        // Check if the promise has been made
        self.promises[tokenId] != nil: "tokenId not promised"
        self.payments[tokenId] != nil: "tokenId not paid"
      }
      self.promises[tokenId]!.whenKept()

      let owner = self.promises[tokenId]!.initialOwner
      // Get back guarantee
      let ownerGnftRef = getAccount(owner).getCapability<&{FungibleToken.Receiver}>(/public/gnftTokenReceiver).borrow()
        ?? panic("Can not get capability")
      ownerGnftRef.deposit(from: <- self.payments.remove(key: tokenId)!)
      // Receive rent
      let ownerFlowRef = getAccount(owner).getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver).borrow()
        ?? panic("Can not get capability")
      ownerFlowRef.deposit(from: <- self.rentFees.remove(key: tokenId)!)

      if let idx = self.userTokens[owner]!.firstIndex(of: tokenId) {
        self.userTokens[owner]!.remove(at: idx)
      }
      let tenant = self.promises[tokenId]!.tenant!
      self.userRented[tenant]!.remove(key: tokenId)

      return self.promises.remove(key: tokenId)
    }

    pub fun claim(tokenId: UInt64, claimerVault: Capability<&{FungibleToken.Receiver}>) {
      pre {
        // Check if the promise has been made
        self.promises[tokenId] != nil: "tokenId not promised"
        self.payments[tokenId] != nil: "tokenId not paid"
      }
      self.promises[tokenId]!.whenBroken()

      let payment <- self.payments.remove(key: tokenId)!
      let claimerAmount = payment.balance * self.claimerPercent
      let appAmount = payment.balance * self.appPercent
      let tenantAmount = payment.balance - claimerAmount - appAmount

      claimerVault.borrow()!.deposit(from: <- payment.withdraw(amount: claimerAmount))
      self.appReciever.borrow()!.deposit(from: <- payment.withdraw(amount: appAmount))

      let tenantGnftRef = getAccount(self.promises[tokenId]!.tenant!).getCapability<&{FungibleToken.Receiver}>(/public/gnftTokenReceiver)
      tenantGnftRef.borrow()!.deposit(from: <- payment.withdraw(amount: tenantAmount))

      let tenantFlowRef = getAccount(self.promises[tokenId]!.tenant!).getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver)
      tenantFlowRef.borrow()!.deposit(from: <- self.rentFees.remove(key: tokenId)!)

      let owner = self.promises[tokenId]!.initialOwner
      if let idx = self.userTokens[owner]!.firstIndex(of: tokenId) {
        self.userTokens[owner]!.remove(at: idx)
      }

      destroy payment
      // Note: we don't destroy the promise here, because the App may still use it
    }

    destroy () {
      destroy self.payments
      destroy self.rentFees
    }
  }

  // Functions
  // Write Functions
  pub fun listForRent(acct: AuthAccount, tokenId: UInt64, endTime: UFix64, rentFee: UFix64, guaranteePayment: @GnftToken.Vault) {
    pre {
      guaranteePayment.balance >= self.guarantee: "Not enough balance"
      endTime >= getCurrentBlock().timestamp + self.minRentPeriod: "Rent period too short"
    }
    let promiseCollection = self.account.getCapability<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath).borrow()
      ?? panic("Can not get capability")
    promiseCollection.makePromise(acct: acct, tokenId: tokenId, endTime: endTime, rentFee: rentFee, guaranteePayment: <- guaranteePayment)

    if let collection = acct.getCapability<&WeaponItems1.Collection{NonFungibleToken.CollectionPublic, WeaponItems1.WeaponItemsCollectionPublic}>(WeaponItems1.CollectionPublicPath).borrow() {
      if let item = collection.borrowWeaponItem(id: tokenId) {
        self.viewTypes[tokenId] = item.getViews()
        let views: {Type: AnyStruct?} = {}
        for t in self.viewTypes[tokenId]! {
          views[t] = item.resolveView(t)
        }
        self.views[tokenId] = views
      }
    }

    emit ListForRent(tokenId: tokenId, lessor: acct.address, endTime: endTime, rentFee: rentFee)
  }

  pub fun rentFrom(tokenId: UInt64, tenant: Address, feePayment: @FlowToken.Vault) {
    let promiseCollection = self.account.getCapability<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath).borrow()
      ?? panic("Can not get capability")
    let promise = promiseCollection.getPromise(tokenId: tokenId) ?? panic("Token not listed")
    if !promise.rented {
      promiseCollection.fillPromise(tokenId: tokenId, tenant: tenant, rentFee: <- feePayment)
      emit RentFrom(tokenId: tokenId, tenant: tenant, fee: promise.rentFee)
    } else {
      panic("Rent failed")
    }
  }

  pub fun finishRent(acct: AuthAccount, tokenId: UInt64) {
    let promiseCollection = self.account.getCapability<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath).borrow()
      ?? panic("Can not get capability")
    let promise = promiseCollection.getPromise(tokenId: tokenId) ?? panic("Token not listed")
    promiseCollection.endPromise(tokenId: tokenId)

    emit FinishRent(tokenId: promise.tokenId, lessor: promise.initialOwner, tenant: promise.tenant!, fee: promise.rentFee)
  }

  pub fun claim(tokenId: UInt64, claimerVault: Capability<&{FungibleToken.Receiver}>) {
    let promiseCollection = self.account.getCapability<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath).borrow()
      ?? panic("Can not get capability")
    promiseCollection.claim(tokenId: tokenId, claimerVault: claimerVault)
    let promise = promiseCollection.getPromise(tokenId: tokenId)!

    emit Claim(tokenId: promise.tokenId, lessor: promise.initialOwner, tenant: promise.tenant!, claimer: claimerVault.address, rentFee: promise.rentFee);
  }

  // Read Functions
  pub fun getOneRentInfo(tokenId: UInt64): Promise? {
    let promiseCollection = self.account.getCapability<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath).borrow()
      ?? panic("Can not get capability")
    return promiseCollection.getPromise(tokenId: tokenId)
  }

  pub fun getAllRentInfo(): [Promise?] {
    let promiseCollection = self.account.getCapability<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath).borrow()
      ?? panic("Can not get capability")
    return promiseCollection.getAllPromises()
  }

  pub fun getUserRented(user: Address): [Promise?] {
    let promiseCollection = self.account.getCapability<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath).borrow()
      ?? panic("Can not get capability")
    return promiseCollection.getUserRented(user: user)
  }

  pub fun getViewTypes(tokenId: UInt64): [Type]? {
    return self.viewTypes[tokenId]
  }

  pub fun getViews(tokenId: UInt64): {Type: AnyStruct}? {
    return self.views[tokenId]
  }

  // pub fun getUrl(type: Type, view: AnyStruct): String? {
  //   switch type {
  //     case Type<MetadataViews.Display>():
  //       return (view as! MetadataViews.Display).url
  //   }
  //   return nil
  // }

  // pub fun getUrls(tokenId: UInt64): [String?] {
  //   let urls: [String?] = []
  //   if let typeViewMap = self.views[tokenId] {
  //     for type in typeViewMap.keys {
  //       let view = typeViewMap[type]!
  //       // let url = switch view {
  //       //   case Type<MetadataViews.Display>():
  //       //     (view as! MetadataViews.Display).url
  //       // }
  //     }
  //   }
  //   return urls
  // }

  init(platformFeeRate: UFix64, minRentPeriod: UFix64, guarantee: UFix64, claimerPercent: UFix64, appPercent: UFix64, appWalletAddress: Address, platformWalletAddress: Address, nftName: String, appName: String) {

    self.platformFeeRate = platformFeeRate
    self.minRentPeriod = minRentPeriod
    self.guarantee = guarantee
    self.claimerPercent = claimerPercent
    self.appPercent = appPercent
    let appVaultRef = getAccount(appWalletAddress).getCapability<&{FungibleToken.Receiver}>(/public/gnftTokenReceiver)
    self.appVaultRef = appVaultRef
    let platformVaultRef = getAccount(platformWalletAddress).getCapability<&{FungibleToken.Receiver}>(/public/flowTokenReceiver)
    self.platformVaultRef = platformVaultRef
    self.nftName = nftName
    self.appName = appName
    self.viewTypes = {}
    self.views = {}

    let key = nftName.concat("For").concat(appName)

    // For rent fee temporarily storage
    self.flowStoragePath = StoragePath(identifier: key.concat("FlowStorage")) ?? panic("storage path: ".concat(key).concat(" failed"))
    self.flowPublicPath = PublicPath(identifier: key.concat("FlowPublic")) ?? panic("public path: ".concat(key).concat(" failed"))
    self.account.save<@FungibleToken.Vault>(<- FlowToken.createEmptyVault(), to: self.flowStoragePath)
    self.account.link<&FlowToken.Vault{FungibleToken.Receiver, FungibleToken.Balance}>(self.flowPublicPath, target: self.flowStoragePath)

    // For guarantee temporarily storage
    self.gnftStoragePath = StoragePath(identifier: key.concat("GnftStorage")) ?? panic("storage path: ".concat(key).concat(" failed"))
    self.gnftPublicPath = PublicPath(identifier: key.concat("GnftPublic")) ?? panic("public path: ".concat(key).concat(" failed"))
    self.account.save<@FungibleToken.Vault>(<- GnftToken.createEmptyVault(), to: self.gnftStoragePath)
    self.account.link<&GnftToken.Vault{FungibleToken.Receiver, FungibleToken.Balance}>(self.gnftPublicPath, target: self.gnftStoragePath)

    // PromiseCollection resource
    self.promiseCollectionStoragePath = StoragePath(identifier: key.concat("PromiseCollectionStorage")) ?? panic("storage path: ".concat(key).concat(" failed"))
    self.promiseCollectionPublicPath = PublicPath(identifier: key.concat("PromiseCollectionPublic")) ?? panic("public path: ".concat(key).concat(" failed"))
    self.account.save(<- create PromiseCollection(platformFeeRate: platformFeeRate, minRentPeriod: minRentPeriod, claimerPercent: claimerPercent, appPercent: appPercent, appReciever: appVaultRef, platformReciever: platformVaultRef), to: self.promiseCollectionStoragePath)
    self.account.link<&PromiseCollection{PromiseCollectionPublic}>(self.promiseCollectionPublicPath, target: self.promiseCollectionStoragePath)
  }
}