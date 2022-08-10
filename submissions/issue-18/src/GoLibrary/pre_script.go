package main

import "fmt"

func getcomponentDetail(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeComponent from %s
	pub fun main(address: Address, componentNftId: UInt64) : SoulMadeComponent.ComponentDetail {
		let receiverRef = getAccount(address)
						.getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
		
		return receiverRef.borrowComponent(id : componentNftId)!.componentDetail
	}`, address)
	return []byte(script)
}

func getComponentIDS(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeComponent from %s
	pub fun main(address: Address) : [UInt64] {
		let receiverRef = getAccount(address)
						.getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
			
		return receiverRef.getIDs()
	}`, address)
	return []byte(script)
}

func getNFTStoreFrontListringResourceID(address string) []byte {
	script := fmt.Sprintf(`
	import NFTStorefront from %s
	pub fun main(account: Address): [UInt64] {
		let storefrontRef = getAccount(account)
			.getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
				NFTStorefront.StorefrontPublicPath
			)
			.borrow()
			?? panic("Could not borrow public storefront from address")
		
		return storefrontRef.getListingIDs()
	}`, NFT_STORE_FRONT_ADDRESS)
	return []byte(script)
}

func getPackSellListringIDS(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMade from %s
	import SoulMadePack from %s
	import NFTStorefront from %s
	pub fun main(address: Address): [UInt64] {
		let storefrontRef = getAccount(address)
			.getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath)
			.borrow()
			?? panic("Could not borrow public storefront from address")
		
		var res: [UInt64] = []
		for listingID in storefrontRef.getListingIDs() {
			var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
			if listingDetail.purchased == false && listingDetail.nftType == Type<@SoulMadePack.NFT>() {
			  res.append(listingID)
			}
		}
		return res
	}`, address, address, NFT_STORE_FRONT_ADDRESS)
	return []byte(script)
}

func getListPackDetailOnSale(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMade from %s
	import SoulMadePack from %s
	import NFTStorefront from %s
	pub fun main(listingID: UInt64): SoulMadePack.PackDetail {
		//testnet
		let platformAddress: Address = %s
		let storefrontRef = getAccount(platformAddress)
			.getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath)
			.borrow()
			?? panic("Could not borrow public storefront from address")
			
		var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
	
		return SoulMade.getPackDetail(address: platformAddress, packNftId: listingDetail.nftID)
	}`, address, address, NFT_STORE_FRONT_ADDRESS, address)
	return []byte(script)
}

func getListingDetailByListringID() []byte {
	script := fmt.Sprintf(`
	import NFTStorefront from %s
	pub fun main(account: Address, listingResourceID: UInt64): NFTStorefront.ListingDetails {
		let storefrontRef = getAccount(account)
			.getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(
				NFTStorefront.StorefrontPublicPath
			)
			.borrow()
			?? panic("Could not borrow public storefront from address")
	
		let listing = storefrontRef.borrowListing(listingResourceID: listingResourceID)
			?? panic("No item with that ID")
		
		return listing.getDetails()!
	}`, NFT_STORE_FRONT_ADDRESS)

	return []byte(script)
}

func getSellingComponentIDS(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeMarketplace from %s
	pub fun main(address:Address) : [UInt64] {
		let res = getAccount(address).getCapability(SoulMadeMarketplace.CollectionPublicPath).borrow<&{SoulMadeMarketplace.SalePublic}>()!
		return res.getSoulMadeComponentIDs()
	}`, address)
	return []byte(script)
}

func getSellingMainIDS(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeMarketplace from %s
	pub fun main(address:Address) : [UInt64] {
		let marketRef = getAccount(address)
						  .getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath).borrow() ?? panic("Could not borrow the marketplace reference")
		return marketRef.getSoulMadeMainIDs()
	}`, address)
	return []byte(script)
}

func getALlMainDetails(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMadeComponent from %s
	pub fun main(address: Address) : [{UInt64:  SoulMadeMain.MainDetail}] {
		let receiverRef = getAccount(address)
						.getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")

		var res : [{UInt64: SoulMadeMain.MainDetail}] = []
		for mainId in receiverRef.getIDs(){
			res.append({mainId : receiverRef.borrowMain(id: mainId).mainDetail})
		}
		return res
	}`, address, address)
	return []byte(script)
}

func getSingleMainDetail(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMade from %s
	pub fun main(address: Address, mainNftId: UInt64) : SoulMadeMain.MainDetail {
		return SoulMade.getMainDetail(address: address, mainNftId: mainNftId)
	}`, address, address)
	return []byte(script)
}

func getMainIDS(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeMain from %s
	pub fun main(address: Address) : [UInt64] {
		let receiverRef = getAccount(address)
						  .getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
		
		return receiverRef.getIDs()
	}`, address)
	return []byte(script)
}

func getcomponentDetailBatch(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeComponent from %s
	pub fun main(address: Address, componentNftIds: [UInt64]) : [SoulMadeComponent.ComponentDetail] {
	
		let receiverRef = getAccount(address)
						.getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
		
		var res : [SoulMadeComponent.ComponentDetail] = []
		for componentNftId in componentNftIds{
			res.append(receiverRef.borrowComponent(id : componentNftId)!.componentDetail)
		}
		return res
	}`, address)
	return []byte(script)
}

func getMainDetailBatch(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeMain from %s
	import SoulMade from %s
	
	pub fun main(address: Address, mainNftIds: [UInt64]) : [SoulMadeMain.MainDetail] {
		let res : [SoulMadeMain.MainDetail] = []
		for mainNftId in mainNftIds {
			res.append(SoulMade.getMainDetail(address:address, mainNftId:mainNftId))
		}
		return res
	}`, address, address)
	return []byte(script)
}

func getPackIDS(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadePack from %s
	pub fun main(address: Address) : [UInt64] {

		let receiverRef = getAccount(address)
						.getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
			
		return receiverRef.getIDs()
		
	}`, address)
	return []byte(script)
}

func getSinglePackDetail(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadePack from %s
	pub fun main(address: Address, id : UInt64) : SoulMadePack.PackDetail {
	
		let receiverRef = getAccount(address)
						  .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
			
		return receiverRef.borrowPack(id: id).packDetail
	}`, address)
	return []byte(script)
}

func getBatchPackDetail(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadePack from %s
	pub fun main(address: Address, ids : [UInt64]) : [SoulMadePack.PackDetail] {
	
		let receiverRef = getAccount(address)
						  .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
			
		var res : [SoulMadePack.PackDetail] = []
			for id in ids{
				res.append(receiverRef.borrowPack(id: id).packDetail)
			}
		return res
	}`, address)
	return []byte(script)
}

func getAllSellDataMarketPalce(address string) []byte {
	script := fmt.Sprintf(`
	import SoulMadeMarketplace from %s
	pub fun main(address: Address) : [SoulMadeMarketplace.SoulMadeSaleData] {
	  let salesData = SoulMadeMarketplace.getSoulMadeSales(address: address)
	  return salesData
	}`, address)
	return []byte(script)
}
