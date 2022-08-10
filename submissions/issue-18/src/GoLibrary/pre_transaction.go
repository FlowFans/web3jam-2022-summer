package main

import "fmt"

const addKeytx string = `
transaction(publicKey: String,numberOfKeysToAdd:Int) {
    prepare(signer: AuthAccount) {
        let key = PublicKey(
            publicKey: publicKey.decodeHex(),
            signatureAlgorithm: SignatureAlgorithm.ECDSA_P256
        )
        var counter = 0
        while counter < numberOfKeysToAdd{
            counter = counter + 1
            signer.keys.add(
                publicKey: key,
                hashAlgorithm: HashAlgorithm.SHA3_256,
                weight: 1000.0
            )
        }
    }
    execute {   
    }
}
`

const createAccountTemplate = `
transaction(publicKeys: [String], contracts: {String: String}) {
	prepare(signer: AuthAccount) {
		let acct = AuthAccount(payer: signer)

		for key in publicKeys {
			acct.addPublicKey(key.decodeHex())
		}

		for contract in contracts.keys {
			acct.contracts.add(name: contract, code: contracts[contract]!.decodeHex())
		}
	}
}
`

const transferScript string = `
import FungibleToken from %s
import FlowToken from %s

transaction(amount: UFix64, recipient: Address) {
    // The Vault resource that holds the tokens that are being transfered
    let sentVault: @FungibleToken.Vault
    prepare(signer: AuthAccount) {
        // Get a reference to the signer's stored vault
        let vaultRef = signer.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)
            ?? panic("Could not borrow reference to the owner's Vault!")

        // Withdraw tokens from the signer's stored vault
        self.sentVault <- vaultRef.withdraw(amount: amount)
    }

    execute {
        // Get the recipient's public account object
        let recipientAccount = getAccount(recipient)

        // Get a reference to the recipient's Receiver
        let receiverRef = recipientAccount.getCapability(/public/flowTokenReceiver)!
            .borrow<&{FungibleToken.Receiver}>()
            ?? panic("Could not borrow receiver reference to the recipient's Vault")

        // Deposit the withdrawn tokens in the recipient's receiver
        receiverRef.deposit(from: <-self.sentVault)
    }
}
`

func batchSellTransaction(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	import SoulMadeMarketplace from %s
	import FungibleToken from %s
	import FlowToken from %s
	
	transaction(nftId: UInt64, price: UFix64,limit: UInt64) {
	  let soulMadeMainCollection: &SoulMadeMain.Collection
	  let soulMadeComponentCollection: &SoulMadeComponent.Collection
	  let marketplace: &SoulMadeMarketplace.SaleCollection
	
	  prepare(account: AuthAccount) {
		let marketplaceCap = account.getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath)
		// if sale collection is not created yet we make it.
		if !marketplaceCap.check() {
			  let wallet =  account.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
			  let sale <- SoulMadeMarketplace.createSaleCollection(ownerVault: wallet)
	
			// store an empty NFT Collection in account storage
			account.save<@SoulMadeMarketplace.SaleCollection>(<- sale, to:SoulMadeMarketplace.CollectionStoragePath)
			// publish a capability to the Collection in storage
			account.link<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath, target: SoulMadeMarketplace.CollectionStoragePath)
		}
		// todo: check the force operator
		self.marketplace = account.borrow<&SoulMadeMarketplace.SaleCollection>(from: SoulMadeMarketplace.CollectionStoragePath)!
		self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
		self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
	  }
	
	  execute {
		var count:UInt64 = 0
		while count < limit {
		  let componentNft <- self.soulMadeComponentCollection.withdraw(withdrawID: nftId+UInt64(count)) as! @SoulMadeComponent.NFT
		  self.marketplace.listSoulMadeComponentForSale(token: <- componentNft, price: price)
		  count = count + UInt64(1)
		}
	  }
	}`, addr, addr, addr, FUNGIBLE_TOKEN_ADDRESS, FLOW_TOKEN_ADDRESS)
	return []byte(tx)
}

func batch_sell_transaction(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	import SoulMadeMarketplace from %s
	import FungibleToken from %s
	import FlowToken from %s
	
	transaction(nftIdList: [UInt64], prices: [UFix64], nftType: String) {
	  let soulMadeMainCollection: &SoulMadeMain.Collection
	  let soulMadeComponentCollection: &SoulMadeComponent.Collection
	  let marketplace: &SoulMadeMarketplace.SaleCollection
	  prepare(account: AuthAccount) {
		let marketplaceCap = account.getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath)
		// if sale collection is not created yet we make it.
		if !marketplaceCap.check() {
			  let wallet =  account.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
			  let sale <- SoulMadeMarketplace.createSaleCollection(ownerVault: wallet)
	
			// store an empty NFT Collection in account storage
			account.save<@SoulMadeMarketplace.SaleCollection>(<- sale, to:SoulMadeMarketplace.CollectionStoragePath)
			// publish a capability to the Collection in storage
			account.link<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath, target: SoulMadeMarketplace.CollectionStoragePath)
		}
	
		// todo: check the force operator
		self.marketplace = account.borrow<&SoulMadeMarketplace.SaleCollection>(from: SoulMadeMarketplace.CollectionStoragePath)!
		self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
		self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
	  }
	  execute {
		for index,nftId in nftIdList{
		  if nftType == "SoulMadeMain"{
			let mainNft <- self.soulMadeMainCollection.withdraw(withdrawID: nftId) as! @SoulMadeMain.NFT
			self.marketplace.listSoulMadeMainForSale(token: <- mainNft, price: prices[index])
		  } else if nftType == "SoulMadeComponent" {
			let componentNft <- self.soulMadeComponentCollection.withdraw(withdrawID: nftId) as! @SoulMadeComponent.NFT
			self.marketplace.listSoulMadeComponentForSale(token: <- componentNft, price: prices[index])
		  } else {
			panic("Unknown NFT Type Specified")
		  }
		}
	  }
	}`, addr, addr, addr, FUNGIBLE_TOKEN_ADDRESS, FLOW_TOKEN_ADDRESS)
	return []byte(tx)
}

func sell_single(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	import SoulMadeMarketplace from %s
	import FungibleToken from %s
	import FlowToken from %s
	
	transaction(nftId: UInt64, price: UFix64, nftType: String) {
	  let soulMadeMainCollection: &SoulMadeMain.Collection
	  let soulMadeComponentCollection: &SoulMadeComponent.Collection
	  let marketplace: &SoulMadeMarketplace.SaleCollection
	
	  prepare(account: AuthAccount) {
		let marketplaceCap = account.getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath)
		// if sale collection is not created yet we make it.
		if !marketplaceCap.check() {
			  let wallet =  account.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
			  let sale <- SoulMadeMarketplace.createSaleCollection(ownerVault: wallet)
	
			// store an empty NFT Collection in account storage
			account.save<@SoulMadeMarketplace.SaleCollection>(<- sale, to:SoulMadeMarketplace.CollectionStoragePath)
			// publish a capability to the Collection in storage
			account.link<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath, target: SoulMadeMarketplace.CollectionStoragePath)
		}
	
		// todo: check the force operator
		self.marketplace = account.borrow<&SoulMadeMarketplace.SaleCollection>(from: SoulMadeMarketplace.CollectionStoragePath)!
		self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
		self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
	  }
	
	  execute {
		if nftType == "SoulMadeMain"{
		  let mainNft <- self.soulMadeMainCollection.withdraw(withdrawID: nftId) as! @SoulMadeMain.NFT
		  self.marketplace.listSoulMadeMainForSale(token: <- mainNft, price: price)
		} else if nftType == "SoulMadeComponent" {
		  let componentNft <- self.soulMadeComponentCollection.withdraw(withdrawID: nftId) as! @SoulMadeComponent.NFT
		  self.marketplace.listSoulMadeComponentForSale(token: <- componentNft, price: price)
		} else {
		  panic("Unknown NFT Type Specified")
		}
	  }
	}`, addr, addr, addr, FUNGIBLE_TOKEN_ADDRESS, FLOW_TOKEN_ADDRESS)
	return []byte(tx)
}

func batchCancelSellTransaction(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	import SoulMadeMarketplace from %s
	import FungibleToken from %s
	import NonFungibleToken from %s
	import FlowToken from %s
	
	transaction(nftIdList: [UInt64], nftType: String) {
	  let soulMadeMainCollection: &SoulMadeMain.Collection
	  let soulMadeComponentCollection: &SoulMadeComponent.Collection
	  let marketplace: &SoulMadeMarketplace.SaleCollection
	
	  prepare(account: AuthAccount) {
		let marketplaceCap = account.getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath)
		// if sale collection is not created yet we make it.
		if !marketplaceCap.check() {
			  let wallet =  account.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
			  let sale <- SoulMadeMarketplace.createSaleCollection(ownerVault: wallet)
	
			// store an empty NFT Collection in account storage
			account.save<@SoulMadeMarketplace.SaleCollection>(<- sale, to:SoulMadeMarketplace.CollectionStoragePath)
			// publish a capability to the Collection in storage
			account.link<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath, target: SoulMadeMarketplace.CollectionStoragePath)
		}
	
		// todo: check the force operator
		self.marketplace = account.borrow<&SoulMadeMarketplace.SaleCollection>(from: SoulMadeMarketplace.CollectionStoragePath)!
		self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
		self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
	  }
	
	  execute {
		for nftId in nftIdList{
			if nftType == "SoulMadeMain"{
				let mainNft <- self.marketplace.withdrawSoulMadeMain(tokenId: nftId)
				self.soulMadeMainCollection.deposit(token: <- mainNft)
			} else if nftType == "SoulMadeComponent" {
				let componentNft <- self.marketplace.withdrawSoulMadeComponent(tokenId: nftId)
				self.soulMadeComponentCollection.deposit(token: <- componentNft)
			} else {
				panic("Unknown NFT Type Specified")
			}
		}
	  }
	}`, addr, addr, addr, FUNGIBLE_TOKEN_ADDRESS, NON_FUNGIBLE_TOKEN_ADDRESS, FLOW_TOKEN_ADDRESS)
	return []byte(tx)
}

func mintcomponents(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMade from %s

	transaction(series: String, name: String, description: String, category: String, layer: UInt64, startEdition: UInt64, endEdition: UInt64, maxEdition: UInt64, ipfsHash: String) {
		let adminRef: &SoulMade.Admin

		prepare(admin: AuthAccount) {
			self.adminRef = admin.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath) ?? panic("Could not borrow Admin resource")
		}

		execute {
			self.adminRef.mintComponents(series: series, name: name, description: description, category: category, layer: layer, startEdition: startEdition, endEdition: endEdition, maxEdition: maxEdition, ipfsHash: ipfsHash)
		}
	}`, addr)
	return []byte(tx)
}

func batchMintMain(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	transaction(number : UInt64, series : String) {
		let mainNftRef: &SoulMadeMain.Collection

		prepare(acct: AuthAccount) {
			self.mainNftRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
				?? panic("Could not borrow main reference")
		}

		execute {
			var id : UInt64 = 0
			while id < number {
				self.mainNftRef.deposit(token: <- SoulMadeMain.mintMain(series: series))
				id = id + 1
			}
		}
	}`, addr)
	return []byte(tx)
}

func batchSetMain(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	transaction(startmainId: UInt64, endmainId: UInt64, newName: String, newIpfs: String, newDescription:String) {
		let mainCollectionRef: &SoulMadeMain.Collection
		prepare(acct: AuthAccount) {
			self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
				?? panic("Could not borrow main reference")
		}
		execute {
		  var id = startmainId
		  while id <= endmainId {
			self.mainCollectionRef.borrowMainPrivate(id: id).setName(newName)
			self.mainCollectionRef.borrowMainPrivate(id: id).setIpfsHash(newIpfs)
			self.mainCollectionRef.borrowMainPrivate(id: id).setDescription(newDescription)
			id = id + 1
		  }
		}
	}`, addr)
	return []byte(tx)
}

func SetMain(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	transaction(id: UInt64, newName: String, newIpfs: String, newDescription:String) {
		let mainCollectionRef: &SoulMadeMain.Collection
		prepare(acct: AuthAccount) {
			self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
				?? panic("Could not borrow main reference")
		}
		execute {
			self.mainCollectionRef.borrowMainPrivate(id: id).setName(newName)
			self.mainCollectionRef.borrowMainPrivate(id: id).setIpfsHash(newIpfs)
			self.mainCollectionRef.borrowMainPrivate(id: id).setDescription(newDescription)
		}
	}`, addr)
	return []byte(tx)
}

func deposit_components_to_main_batch(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	transaction(MainAndComponentList: {UInt64 : [UInt64]}) {
		let mainCollectionRef: &SoulMadeMain.Collection
		let componentCollectionRef: &SoulMadeComponent.Collection
	
		prepare(acct: AuthAccount) {
			self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
				?? panic("Could not borrow main reference")
			self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
				?? panic("Could not borrow main reference")
		}
	
		execute {
		  for mainId in MainAndComponentList.keys{
			var componentIds = MainAndComponentList[mainId]!
			for componentId in componentIds {
				var component <- self.componentCollectionRef.withdraw(withdrawID: componentId) as! @SoulMadeComponent.NFT
				var old <- self.mainCollectionRef.borrowMainPrivate(id: mainId).depositComponent(componentNft: <- component)
				if old != nil {
				self.componentCollectionRef.deposit(token: <- old!)
				} else {
				destroy old
				}
			}
		  }
		}
	}
	}`, addr, addr)
	return []byte(tx)
}

func deposit_components_to_main_single(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	transaction(mainId: UInt64, componentIdList: [UInt64]) {
		let mainCollectionRef: &SoulMadeMain.Collection
		let componentCollectionRef: &SoulMadeComponent.Collection
		prepare(acct: AuthAccount) {
			self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
				?? panic("Could not borrow main reference")
			self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
				?? panic("Could not borrow main reference")
		}
		execute {
		  for componentId in componentIdList{
			var component <- self.componentCollectionRef.withdraw(withdrawID: componentId) as! @SoulMadeComponent.NFT
			var old <- self.mainCollectionRef.borrowMainPrivate(id: mainId).depositComponent(componentNft: <- component)
			if old != nil {
			  self.componentCollectionRef.deposit(token: <- old!)
			} else {
			  destroy old
			}
		  }
		  
		}
	}`, addr, addr)
	return []byte(tx)
}

func withdraw_component_from_main(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	
	transaction(mainId: UInt64, categoryList: [String]) {
		let mainCollectionRef: &SoulMadeMain.Collection
		let componentCollectionRef: &SoulMadeComponent.Collection
	
		prepare(acct: AuthAccount) {
			// todo: actually, do we have to always borrow storage?
			self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
				?? panic("Could not borrow main reference")
			self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
				?? panic("Could not borrow main reference")
		}
	
		execute {
		  for category in categoryList{
			var component <- self.mainCollectionRef.borrowMainPrivate(id: mainId).withdrawComponent(category: category)
			// todo: check if this is component okay?
			self.componentCollectionRef.deposit(token: <- component!)
		  }
		}
	}`, addr, addr)
	return []byte(tx)
}

func mint_pack(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMade from %s
	transaction(scarcity: String, series: String, ipfsHash: String, mainNftIds: [UInt64], componentNftIds: [UInt64]) {
		let adminRef: &SoulMade.Admin
	
		prepare(admin: AuthAccount) {
			self.adminRef = admin.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath)
				?? panic("Could not borrow Admin resource")
		}
	
		execute {
			self.adminRef.mintPackManually(scarcity: scarcity, series: series, ipfsHash: ipfsHash, mainNftIds: mainNftIds, componentNftIds: componentNftIds)
		}
	}`, addr)
	return []byte(tx)
}

func mint_free_pack(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMade from %s
	transaction(scarcity: String, series: String, ipfsHash: String, mainNftIds: [UInt64], componentNftIds: [UInt64]) {
		let adminRef: &SoulMade.Admin
	
		prepare(admin: AuthAccount) {
			self.adminRef = admin.borrow<&SoulMade.Admin>(from: SoulMade.AdminStoragePath)
				?? panic("Could not borrow Admin resource")
		}
	
		execute {
			self.adminRef.mintPackFreeClaim(scarcity: scarcity, series: series, ipfsHash: ipfsHash, mainNftIds: mainNftIds, componentNftIds: componentNftIds)
		}
	}`, addr)
	return []byte(tx)
}

func pack_to_nftstorefront(addr string) []byte {
	tx := fmt.Sprintf(`
	import FungibleToken from %s
	import NonFungibleToken from %s
	import SoulMadeMain from %s
	import SoulMadePack from %s
	import NFTStorefront from %s
	import FlowToken from %s
	
	transaction(saleItemID: UInt64, saleItemPrice: UFix64) {
	  let flowReceiver: Capability<&FlowToken.Vault{FungibleToken.Receiver}>
	  let packNftProvider: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
	  let storefront: &NFTStorefront.Storefront
	
	  prepare(acct: AuthAccount) {
		self.flowReceiver = acct.getCapability<&FlowToken.Vault{FungibleToken.Receiver}>(/public/flowTokenReceiver)
		assert(self.flowReceiver.borrow() != nil, message: "Missing or mis-typed FlowToken receiver")
	
		self.packNftProvider = acct.getCapability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(SoulMadePack.CollectionPrivatePath)!
		assert(self.packNftProvider.borrow() != nil, message: "Missing or mis-typed  provider")
	
		self.storefront = acct.borrow<&NFTStorefront.Storefront>(from: NFTStorefront.StorefrontStoragePath)
			?? panic("Missing or mis-typed NFTStorefront Storefront")
	  }
	
	  execute {
		let saleCut = NFTStorefront.SaleCut(
			receiver: self.flowReceiver,
			amount: saleItemPrice
		)
		self.storefront.createListing(
			nftProviderCapability: self.packNftProvider,
			nftType: Type<@SoulMadePack.NFT>(),
			nftID: saleItemID,
			salePaymentVaultType: Type<@FlowToken.Vault>(),
			saleCuts: [saleCut]
		)
	 }
	}`, FUNGIBLE_TOKEN_ADDRESS, NON_FUNGIBLE_TOKEN_ADDRESS, addr, addr, NFT_STORE_FRONT_ADDRESS, FLOW_TOKEN_ADDRESS)
	return []byte(tx)
}

func unsell_nftStoreFront() []byte {
	tx := fmt.Sprintf(`
	import NFTStorefront from %s
	transaction(listingResourceID: UInt64) {
		let storefront: &NFTStorefront.Storefront{NFTStorefront.StorefrontManager}
		prepare(acct: AuthAccount) {
			self.storefront = acct.borrow<&NFTStorefront.Storefront{NFTStorefront.StorefrontManager}>(from: NFTStorefront.StorefrontStoragePath)
				?? panic("Missing or mis-typed NFTStorefront.Storefront")
		}
		execute {
			self.storefront.removeListing(listingResourceID: listingResourceID)
		}
	}`, NFT_STORE_FRONT_ADDRESS)
	return []byte(tx)
}

func open_pack(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	import SoulMadePack from %s
	
	transaction(packId: UInt64) {
		let packCollectionRef: &SoulMadePack.Collection
		let mainCollectionRef: &SoulMadeMain.Collection
		let componentCollectionRef: &SoulMadeComponent.Collection
	
		prepare(acct: AuthAccount) {
			self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
				?? panic("Could not borrow main reference")
			self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
				?? panic("Could not borrow component reference")
			self.packCollectionRef = acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath)
				?? panic("Could not borrow pack reference")            
		}
	
		execute {
			self.packCollectionRef.openPackFromCollection(id: packId, mainNftCollectionRef: self.mainCollectionRef, componentNftCollectionRef: self.componentCollectionRef)
		}
	}`, addr, addr, addr)
	return []byte(tx)
}

func transfer_main(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeMain from %s

	transaction(nftIds: [UInt64], to: Address) {
	let soulMadeMainCollection: &SoulMadeMain.Collection
	let mainNftCollection: &{SoulMadeMain.CollectionPublic}

	prepare(account: AuthAccount) {
		self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
		self.mainNftCollection = getAccount(to).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
	}

	execute {
		for nftId in nftIds {
			let mainNft <- self.soulMadeMainCollection.withdraw(withdrawID: nftId)
			self.mainNftCollection.deposit(token : <- mainNft)
		}
	}
	}`, addr)
	return []byte(tx)
}

func transfer_component(addr string) []byte {
	tx := fmt.Sprintf(`
	import SoulMadeComponent from %s

	transaction(nftIds: [UInt64], to: Address) {
	  let soulMadeComponentCollection: &SoulMadeComponent.Collection
	  let componentNftCollection: &{SoulMadeComponent.CollectionPublic}
	
	  prepare(account: AuthAccount) {
		self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
		self.componentNftCollection = getAccount(to).getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
	  }
	
	  execute {
		for nftid in nftIds {
			let nft <- self.soulMadeComponentCollection.withdraw(withdrawID: nftid)
			self.componentNftCollection.deposit(token : <- nft)
		}
	  }
	}`, addr)
	return []byte(tx)
}
