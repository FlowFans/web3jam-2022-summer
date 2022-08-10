// emulator
// import NonFungibleToken from "./NonFungibleToken.cdc"
// import NFTStorefront from "./NFTStorefront.cdc"
// import SoulMadeComponent from "./SoulMadeComponent.cdc"
// import SoulMadeMain from "./SoulMadeMain.cdc"
// import SoulMadePack from "./SoulMadePack.cdc"
// import FungibleToken from 0xee82856bf20e2aa6
// import FlowToken from 0x0ae53cb6e3f42a79

// testnet
import NonFungibleToken from 0x631e88ae7f1d7c20
import FungibleToken from 0x9a0766d93b6608b7
import SoulMadeComponent from 0x421c19b7dc122357
import SoulMadeMain from 0x421c19b7dc122357
import SoulMadePack from 0x421c19b7dc122357
import SoulMadeMarketplace from 0x421c19b7dc122357
import NFTStorefront from 0x94b06cfca1d8a476

// mainnet
// import NonFungibleToken from 0x1d7e57aa55817448
// import NFTStorefront from 0x4eb8a10cb9f87357
// import SoulMadeComponent from 0x543606e9393a64a6
// import SoulMadeMain from 0x543606e9393a64a6
// import SoulMadePack from 0x543606e9393a64a6
// import FungibleToken from 0xf233dcee88fe0abe
// import FlowToken from 0x1654653399040a61


pub contract SoulMade {

  pub let AdminStoragePath: StoragePath

  pub resource Admin {

    pub fun mintComponent(series: String,
                                    name: String,
                                    description: String,
                                    category: String,
                                    layer: UInt64,
                                    edition: UInt64,
                                    maxEdition: UInt64,
                                    ipfsHash: String) {

      let adminComponentsCollection = SoulMade.account.borrow<&SoulMadeComponent.Collection{NonFungibleToken.CollectionPublic}>(from: SoulMadeComponent.CollectionStoragePath)!
      
      var newNFT <- SoulMadeComponent.makeEdition(
          series: series,
          name: name,
          description: description,
          category: category,
          layer: layer,
          currentEdition: edition,
          maxEdition: maxEdition,
          ipfsHash: ipfsHash
      )
      adminComponentsCollection.deposit(token: <- newNFT)
    }

    pub fun mintComponents(series: String,
                                    name: String,
                                    description: String,
                                    category: String,
                                    layer: UInt64,
                                    startEdition: UInt64,
                                    endEdition: UInt64,
                                    maxEdition: UInt64,
                                    ipfsHash: String) {

      let adminComponentsCollection = SoulMade.account.borrow<&SoulMadeComponent.Collection{NonFungibleToken.CollectionPublic}>(from: SoulMadeComponent.CollectionStoragePath)!
      
      var edition = startEdition

      while edition <= endEdition {
        var newNFT <- SoulMadeComponent.makeEdition(
            series: series,
            name: name,
            description: description,
            category: category,
            layer: layer,
            currentEdition: edition,
            maxEdition: maxEdition,
            ipfsHash: ipfsHash
        )
        edition = edition + UInt64(1)
        adminComponentsCollection.deposit(token: <- newNFT)
      }
    }

    pub fun moveMainComponentToPack(scarcity: String, series: String, ipfsHash: String, mainNftIds: [UInt64], componentNftIds: [UInt64], adminStoragePath: StoragePath) {
      let adminMainCollection = SoulMade.account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
      let adminComponentCollection = SoulMade.account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
      let adminPackCollection = SoulMade.account.borrow<&SoulMadePack.Collection{NonFungibleToken.CollectionPublic}>(from: adminStoragePath)!

      var mainNftList: @[SoulMadeMain.NFT] <- []
      var componentNftList: @[SoulMadeComponent.NFT] <- []

      for mainNftId in mainNftIds{
        var nft <- adminMainCollection.withdraw(withdrawID: mainNftId) as! @SoulMadeMain.NFT
        mainNftList.append(<- nft)
      }

      for componentNftId in componentNftIds{
        var nft <- adminComponentCollection.withdraw(withdrawID: componentNftId) as! @SoulMadeComponent.NFT
        componentNftList.append(<- nft)
      }

      var packNft <- SoulMadePack.mintPack(scarcity: scarcity, series: series, ipfsHash: ipfsHash, mainNfts: <- mainNftList, componentNfts: <- componentNftList)
      adminPackCollection.deposit(token: <- packNft)      
    }
    
    pub fun mintPackManually(scarcity: String, series: String, ipfsHash: String, mainNftIds: [UInt64], componentNftIds: [UInt64]) {
      self.moveMainComponentToPack(scarcity: scarcity, series: series, ipfsHash: ipfsHash, mainNftIds: mainNftIds, componentNftIds: componentNftIds, adminStoragePath: SoulMadePack.CollectionStoragePath)
    }

    pub fun mintPackFreeClaim(scarcity: String, series: String, ipfsHash: String, mainNftIds: [UInt64], componentNftIds: [UInt64]) {
      self.moveMainComponentToPack(scarcity: scarcity, series: series, ipfsHash: ipfsHash, mainNftIds: mainNftIds, componentNftIds: componentNftIds, adminStoragePath: SoulMadePack.CollectionFreeClaimStoragePath)
    }

    pub fun renewFreeClaim() {
      SoulMadePack.renewClaimDictionary()
    }

    pub fun updataAccountFreeClaim(address: Address, series: String) {
      SoulMadePack.updateClaimDictionary(address: address, series: series)
    }

    pub fun updataPlatformCut(platformCut: UFix64) {
      SoulMadeMarketplace.updatePlatformCut(platformCut: platformCut)
    }

  }

  pub fun getMainCollectionIds(address: Address) : [UInt64] {
      let receiverRef = getAccount(address)
                        .getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
      return receiverRef.getIDs()
  }

  pub fun getMainDetail(address: Address, mainNftId: UInt64) : SoulMadeMain.MainDetail {
    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    return receiverRef.borrowMain(id: mainNftId).mainDetail
  }

  pub fun getComponentCollectionIds(address: Address) : [UInt64] {
    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
    return receiverRef.getIDs()
  }

  pub fun getComponentDetail(address: Address, componentNftId: UInt64) : SoulMadeComponent.ComponentDetail {
      let receiverRef = getAccount(address)
                        .getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
      return receiverRef.borrowComponent(id : componentNftId).componentDetail
  }

  pub fun getPackCollectionIds(address: Address) : [UInt64] {
      let receiverRef = getAccount(address)
                        .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
      return receiverRef.getIDs()
  }

  pub fun getPackDetail(address: Address, packNftId: UInt64) : SoulMadePack.PackDetail {
      let receiverRef = getAccount(address)
                        .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
      return receiverRef.borrowPack(id : packNftId).packDetail
  }

  pub fun getPackListingIdsPerSeries(address: Address): {String: [UInt64]} {
      let storefrontRef = getAccount(address)
          .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath)
          .borrow()
          ?? panic("Could not borrow public storefront from address")
      
      var res: {String: [UInt64]} = {}

      for listingID in storefrontRef.getListingIDs() {
          var listingDetail : NFTStorefront.ListingDetails = storefrontRef.borrowListing(listingResourceID: listingID)!.getDetails()
          if listingDetail.purchased == false && listingDetail.nftType == Type<@SoulMadePack.NFT>() {
            var packNftId = listingDetail.nftID
            var packDetail: SoulMadePack.PackDetail = SoulMade.getPackDetail(address: address, packNftId: packNftId)!
            var packSeries = packDetail.series
            if res[packSeries] == nil{
              res[packSeries] = [listingID]
            } else {
              res[packSeries]!.append(listingID)
            }
          }
      }

      return res
  }


  init() {
    self.AdminStoragePath = /storage/SoulMadeAdmin
    self.account.save(<- create Admin(), to: self.AdminStoragePath)
  }

}