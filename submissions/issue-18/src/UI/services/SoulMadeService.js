import { listItemIconClasses } from '@mui/material';
import * as fcl from '@onflow/fcl';
import * as types from '@onflow/types';
import { config } from '../config';
import { SingletonService } from './SingletonService';

const getBuyScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c
import SoulMadePack from 0x9a57dfe5c8ce609c
import FungibleToken from 0xf233dcee88fe0abe
import FlowToken from 0x1654653399040a61

transaction(tokenId: UInt64, nftType: String, seller: Address) {
    let vaultRef: &{FungibleToken.Provider}
    let soulmadeMainCollectionCap: Capability<&{SoulMadeMain.CollectionPublic}>
    let soulmadeComponentCollectionCap: Capability<&{SoulMadeComponent.CollectionPublic}>
    prepare(account: AuthAccount) {
        // In case the customer does not claim the free drop while buy item directly, hence we need to initialize it here as well 
        if account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
            account.save(<- SoulMadeMain.createEmptyCollection(), to: SoulMadeMain.CollectionStoragePath)
            account.link<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
            account.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
        }
        if account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
            account.save(<- SoulMadeComponent.createEmptyCollection(), to: SoulMadeComponent.CollectionStoragePath)
            account.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
            account.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
        }
        if account.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
            account.save(<- SoulMadePack.createEmptyCollection(), to: SoulMadePack.CollectionStoragePath)
            account.link<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
            account.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
        }        
        self.soulmadeMainCollectionCap = account.getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath)
        self.soulmadeComponentCollectionCap = account.getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath)
        self.vaultRef = account.borrow<&{FungibleToken.Provider}>(from: /storage/flowTokenVault) ?? panic("Could not borrow owner's Vault reference")
    }
    execute {
        let marketplace = getAccount(seller).getCapability(SoulMadeMarketplace.CollectionPublicPath).borrow<&{SoulMadeMarketplace.SalePublic}>() ?? panic("Could not borrow seller's sale reference")
        if nftType == "SoulMadeMain"{
            var price = SoulMadeMarketplace.getSoulMadeMainSale(address: seller, id: tokenId).price
            var temporaryVault <- self.vaultRef.withdraw(amount: price)
            marketplace.purchaseSoulMadeMain(tokenId: tokenId, recipientCap: self.soulmadeMainCollectionCap, buyTokens: <- temporaryVault)
        } else if nftType == "SoulMadeComponent" {
            var price = SoulMadeMarketplace.getSoulMadeComponentSale(address: seller, id: tokenId).price
            var temporaryVault <- self.vaultRef.withdraw(amount: price)
            marketplace.purchaseSoulMadeComponent(tokenId: tokenId, recipientCap: self.soulmadeComponentCollectionCap, buyTokens: <- temporaryVault)
        }
    }
}
`;

const getUserAssetsScript = () => `
import NonFungibleToken from 0x1d7e57aa55817448
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadePack from 0x9a57dfe5c8ce609c
import SoulMade from 0x9a57dfe5c8ce609c

pub struct SoulMadeNftDetail {
    pub let id: UInt64
    pub let nftType: String
    pub let series: String
    pub let mainDetail: SoulMadeMain.MainDetail?
    pub let componentDetail: SoulMadeComponent.ComponentDetail?

    init(id: UInt64,
            nftType: String,
            series: String,
            mainDetail: SoulMadeMain.MainDetail?,
            componentDetail: SoulMadeComponent.ComponentDetail?){
                self.id = id
                self.nftType = nftType
                self.series = series
                self.mainDetail = mainDetail
                self.componentDetail = componentDetail
    }
}

pub fun main(address: Address): [SoulMadeNftDetail] {

  var res: [SoulMadeNftDetail] = []

  if(getAccount(address).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()){
    var mainNftIds = SoulMade.getMainCollectionIds(address: address)
    for mainNftId in mainNftIds{
      
      var mainDetail = SoulMade.getMainDetail(address: address, mainNftId: mainNftId)!
      var detail = SoulMadeNftDetail(id: mainNftId, nftType: "SoulMadeMain", series: mainDetail.series, mainDetail: mainDetail, componentDetail: nil)
      res.append(detail)
    }

    var componentNftIds = SoulMade.getComponentCollectionIds(address: address)
    for componentNftId in componentNftIds{
      var componentDetail = SoulMade.getComponentDetail(address: address, componentNftId: componentNftId)!
      var detail = SoulMadeNftDetail(id: componentNftId, nftType: "SoulMadeComponent", series: componentDetail.series, mainDetail: nil, componentDetail: componentDetail)
      res.append(detail)
    }

  }

  return res
}
`;

const getUpdateComponentsScript = () => `

import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c

/*
Data Structure:
[{String: Integer}]
Example:
[{"Body": null}, {"Head": 2}] (edited) 
*/
transaction(mainNftId: UInt64, changes: [{String: UInt64?}], newIpfsHash: String) {

    let mainCollectionRef: &SoulMadeMain.Collection
    let componentCollectionRef: &SoulMadeComponent.Collection

    prepare(acct: AuthAccount) {
      // todo: should it be private path? or storage? Doesn't matter~ 
      self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
          ?? panic("Could not borrow main reference")
      
      self.componentCollectionRef = acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
          ?? panic("Could not borrow component reference")
    }

    execute {
      let mainNftRef = self.mainCollectionRef.borrowMainPrivate(id: mainNftId)

      for individualChange in changes{

        for category in individualChange.keys {
          // todo: why there is a force operator? Is it because it's a dictionary?
          var componentNftId: UInt64? = individualChange[category]!
          if(componentNftId != nil){
            // todo: maybe we can refactor this? self.componentCollectionRef appears in many places. 
            var componentNft <- self.componentCollectionRef.withdraw(withdrawID: componentNftId!) as! @SoulMadeComponent.NFT

            var old <- mainNftRef.depositComponent(componentNft: <- componentNft!)

            if old != nil {
              // todo: double check this, does this work? Old should be SoulMadeComponent.NFT type, not NonfungibleToken.NFT
              self.componentCollectionRef.deposit(token: <- old!)
            } else {
              destroy old
            }

          } else {
            // todo: why this returns optional?
            var component <- mainNftRef.withdrawComponent(category: category)
            // todo: check if this is component okay?
            // todo: why there has to be a "force operator"?
            self.componentCollectionRef.deposit(token: <- component!)
          }

        }
      }

      mainNftRef.setIpfsHash(newIpfsHash)

    }
}
`;

const getMainAssetsScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0x9a57dfe5c8ce609c
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeMainSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)

  for sale in salesData {
    var category = sale.mainDetail!.componentDetails[0].category
    var name = sale.mainDetail!.name
    var categoryAndName = category.concat(name)

    if intermediate[categoryAndName] == nil {
      intermediate[categoryAndName] = sale
    }
  }
  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for mainSale in intermediate.values{
    res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: mainSale.id,
                price: mainSale.price,
                nftType: "SoulMadeMain",
                mainDetail: mainSale.mainDetail,
                componentDetail: nil
                ))
  }
  return res
}
`;

const getMainAssets_By_Series_MarketplaceScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0x9a57dfe5c8ce609c
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeMainSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)
  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for sale in salesData {
    var se = sale.mainDetail!.componentDetails[0].series
    if series == se {
      res.append(SoulMadeMarketplace.SoulMadeSaleData(
                  id: sale.id,
                  price: sale.price,
                  nftType: "SoulMadeMain",
                  mainDetail: sale.mainDetail,
                  componentDetail: nil
                  ))
    }
  }
  return res
}
`;

const getAllMarketPlace_SaleMain = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0x9a57dfe5c8ce609c
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeMainSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)
  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for sale in salesData {
    res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: sale.id,
                price: sale.price,
                nftType: "SoulMadeMain",
                mainDetail: sale.mainDetail,
                componentDetail: nil
                ))
  }
  return res
}
`;

const getComponentAssetsScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0x9a57dfe5c8ce609c
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeComponentSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeComponentSales(address: address)

  for sale in salesData {
    var category = sale.componentDetail.category
    var name = sale.componentDetail.name
    var categoryAndName = category.concat(name)

    if intermediate[categoryAndName] == nil {
      intermediate[categoryAndName] = sale
    }
  }

  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for componentSale in intermediate.values{
    res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: componentSale.id,
                price: componentSale.price,
                nftType: "SoulMadeComponent",
                mainDetail: nil,
                componentDetail: componentSale.componentDetail
                ))
  }
  return res
}
`;

const getAllMarketPlace_SaleComponent = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(series: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // let address: Address = 0xf8d6e0586b0a20c7
  let address: Address = 0x9a57dfe5c8ce609c
  
  // category: {name: saledata}
  var intermediate: {String : SoulMadeMarketplace.SoulMadeComponentSaleData} = {}

  let salesData = SoulMadeMarketplace.getSoulMadeComponentSales(address: address)


  var res: [SoulMadeMarketplace.SoulMadeSaleData] = []
  for sale in salesData {
    res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: sale.id,
                price: sale.price,
                nftType: "SoulMadeComponent",
                mainDetail: nil,
                componentDetail: sale.componentDetail
                ))
  }
  return res
}
`;

const getGroupAssetsScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(series: String, nftType: String, category: String, nftName: String) : [SoulMadeMarketplace.SoulMadeSaleData] {
  // platform address
  let address : Address = 0x9a57dfe5c8ce609c

  var res: [SoulMadeMarketplace.SoulMadeSaleData] = [] 

  if nftType == "SoulMadeMain"{
    let salesData = SoulMadeMarketplace.getSoulMadeMainSales(address: address)
    for mainSale in salesData {
      var saleCategory = mainSale.mainDetail!.componentDetails[0].category
      var saleName = mainSale.mainDetail!.name

      if saleCategory == category &&  saleName == nftName{
        res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: mainSale.id,
                price: mainSale.price,
                nftType: "SoulMadeMain",
                mainDetail: mainSale.mainDetail,
                componentDetail: nil
                )
              )
      }
    }
  } else if nftType == "SoulMadeComponent" {
    let salesData = SoulMadeMarketplace.getSoulMadeComponentSales(address: address)
    for componentSale in salesData {
      var saleCategory = componentSale.componentDetail.category
      var saleName = componentSale.componentDetail.name

      if saleCategory == category &&  saleName == nftName{
        res.append(SoulMadeMarketplace.SoulMadeSaleData(
                id: componentSale.id,
                price: componentSale.price,
                nftType: "SoulMadeComponent",
                mainDetail: nil,
                componentDetail: componentSale.componentDetail
                )
              )
      }
    }

  }
  return res
}
`;

const getAssetByIdAndTypeScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(id: UInt64, nftType: String) : SoulMadeMarketplace.SoulMadeSaleData? {
  // platform address
  let address : Address = 0x9a57dfe5c8ce609c

  if nftType == "SoulMadeMain"{
    let mainSale = SoulMadeMarketplace.getSoulMadeMainSale(address: address, id: id)
    return SoulMadeMarketplace.SoulMadeSaleData(id: mainSale.id, price: mainSale.price, nftType: "SoulMadeMain", mainDetail: mainSale.mainDetail, componentDetail: nil)
  } else if nftType == "SoulMadeComponent" {
    let componentSale = SoulMadeMarketplace.getSoulMadeComponentSale(address: address, id: id)
    return SoulMadeMarketplace.SoulMadeSaleData( id: componentSale.id, price: componentSale.price, nftType: "SoulMadeComponent", mainDetail: nil, componentDetail: componentSale.componentDetail )
  }

  return nil

}
`;

const getCapabilityScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c

pub fun main(address: Address) : Bool {
    return !getAccount(address).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()
}
`;

const getRandomPackListingIdOfSeriesScript = () => `
import SoulMade from 0x9a57dfe5c8ce609c

pub fun main(series: String) : UInt64 {
  let platformAddress: Address = 0x9a57dfe5c8ce609c

  var packListings = SoulMade.getPackListingIdsPerSeries(address: platformAddress)[series]!

  var randomIndex = unsafeRandom() % UInt64(packListings.length)

  return packListings[randomIndex]

}
`;

const getFreeBodyScript = () => `
import NonFungibleToken from 0x1d7e57aa55817448
import FungibleToken from 0xf233dcee88fe0abe
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadePack from 0x9a57dfe5c8ce609c

transaction {

  let platformFreeCollectionCap: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>
  let ownNftCollection: &{SoulMadeMain.CollectionPublic}

  prepare(acct: AuthAccount) {
    
    if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
      acct.save(<- SoulMadeMain.createEmptyCollection(), to: SoulMadeMain.CollectionStoragePath)
      acct.link<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
      acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
    }

    if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
      acct.save(<- SoulMadeComponent.createEmptyCollection(), to: SoulMadeComponent.CollectionStoragePath)
      acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
      acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
    }

    if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
      acct.save(<- SoulMadePack.createEmptyCollection(), to: SoulMadePack.CollectionStoragePath)
      acct.link<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
      //todo: here is a private
      acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
    }

    //let platformAddress : Address = 0xf8d6e0586b0a20c7 
    let platformAddress : Address = 0x9a57dfe5c8ce609c
    self.platformFreeCollectionCap = getAccount(platformAddress).getCapability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(/public/SoulMadeMainCollectionFree)
    self.ownNftCollection = acct.borrow<&{SoulMadeMain.CollectionPublic}>(from: SoulMadeMain.CollectionStoragePath) ?? panic("Cannot borrow NFT collection receiver from account")
  }

  execute{
    var id = self.platformFreeCollectionCap.borrow()!.getIDs()[0]
    self.ownNftCollection.deposit(token: <- self.platformFreeCollectionCap.borrow()!.withdraw(withdrawID: id))
  }
}
`;

const updateInfoScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c

transaction(mainId: UInt64, newName: String, newDescription: String) {
    let mainCollectionRef: &SoulMadeMain.Collection

    prepare(acct: AuthAccount) {
        self.mainCollectionRef = acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
            ?? panic("Could not borrow main reference")
    }

    execute {
      self.mainCollectionRef.borrowMainPrivate(id: mainId).setName(newName)
      self.mainCollectionRef.borrowMainPrivate(id: mainId).setDescription(newDescription)
    }
}
`;

const getBuyPackScript = () => `
import FungibleToken from 0xf233dcee88fe0abe
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadePack from 0x9a57dfe5c8ce609c
import SoulMade from 0x9a57dfe5c8ce609c
import NonFungibleToken from 0x1d7e57aa55817448

import NFTStorefront from 0x4eb8a10cb9f87357
import FlowToken from 0x1654653399040a61

transaction(listingResourceID: UInt64) {
    let paymentVault: @FungibleToken.Vault
    let mainNftCollection: &{NonFungibleToken.Receiver}
    let componentNftCollection: &{NonFungibleToken.Receiver}
    let storefront: &NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}
    let listing: &NFTStorefront.Listing{NFTStorefront.ListingPublic}

    prepare(acct: AuthAccount) {
        // set up account
        if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
            let collection <- SoulMadeMain.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeMain.CollectionStoragePath)
            acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
            // todo: double check if we need this PrivatePath at all. I remeber we actually have used it somewhere.
            acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
        }

        if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
            let collection <- SoulMadeComponent.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
        }

        // todo: actually, the uers do not need pack collection, as the pack will automatically be opened
        if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
            let collection <- SoulMadePack.createEmptyCollection()
            acct.save(<-collection, to: SoulMadePack.CollectionStoragePath)
            acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
            acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
        }
        
        // let platformAddress: Address = 0xf8d6e0586b0a20c7
        let platformAddress: Address = 0x9a57dfe5c8ce609c
        self.storefront = getAccount(platformAddress)
            .getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath).borrow() ?? panic("Could not borrow Storefront from provided address")

        self.listing = self.storefront.borrowListing(listingResourceID: listingResourceID) ?? panic("No Offer with that ID in Storefront")
            
        let price = self.listing.getDetails().salePrice
        let mainFlowVault = acct.borrow<&FlowToken.Vault>(from: /storage/flowTokenVault)
            ?? panic("Cannot borrow FlowToken vault from acct storage")
        self.paymentVault <- mainFlowVault.withdraw(amount: price)

        // todo: remember to update all these panic to make it more explicit
        self.mainNftCollection = acct.borrow<&{NonFungibleToken.Receiver}>(from: SoulMadeMain.CollectionStoragePath) ?? panic("Cannot borrow Main NFT collection receiver from account")
        self.componentNftCollection = acct.borrow<&{NonFungibleToken.Receiver}>(from: SoulMadeComponent.CollectionStoragePath) ?? panic("Cannot borrow Component NFT collection receiver from account")
    }

    execute {
        let pack <- self.listing.purchase(payment: <-self.paymentVault) as! @SoulMadePack.NFT
        
        let mainComponentIds = pack.getMainComponentIds()

        // todo: I explictly initialized the dictionary, is it safe to use the force operator?
        let mainNftIds = mainComponentIds.mainNftIds
        let componentNftIds = mainComponentIds.componentNftIds

        // todo: I recall there is some kind of "assert"?
        if(mainNftIds.length > 0 && self.mainNftCollection != nil){
        for mainNftId in mainNftIds{
            // todo: doulbe check all this kind of "casting actions"
            var nft <- pack.withdrawMain(mainNftId: mainNftId)! as @NonFungibleToken.NFT
            self.mainNftCollection!.deposit(token: <- nft)
        }
        } else if mainNftIds.length > 0 && self.mainNftCollection == nil {
            // todo: do we need this panic? it will just fail, right?
            panic("reference is null")
        }

        if(componentNftIds.length > 0 && self.componentNftCollection != nil){
        for componentNftId in componentNftIds{
            var nft <- pack.withdrawComponent(componentNftId: componentNftId)! as @NonFungibleToken.NFT
            self.componentNftCollection!.deposit(token: <- nft)
        }
        } else if componentNftIds.length > 0 && self.componentNftCollection == nil {
            panic("reference is null")
        }
        destroy pack
    }
}
`;

const getSpecialMainDetail = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMade from 0x9a57dfe5c8ce609c

pub fun main(address: Address, mainNftId: UInt64) : SoulMadeMain.MainDetail {
    return SoulMade.getMainDetail(address: address, mainNftId: mainNftId)
}
`;

const getUserBalanceScript = () => `
import FungibleToken from 0xf233dcee88fe0abe
import FlowToken from 0x1654653399040a61

pub fun main(account: Address): UFix64 {

    let vaultRef = getAccount(account)
        .getCapability(/public/flowTokenBalance)
        .borrow<&FlowToken.Vault{FungibleToken.Balance}>()
        ?? panic("Could not borrow Balance reference to the Vault")

    return vaultRef.balance
}
`;

const getSellAssetScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c
import FungibleToken from 0xf233dcee88fe0abe
import FlowToken from 0x1654653399040a61

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
}
`;

const getUserSalesScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c

pub fun main(address: Address) : [SoulMadeMarketplace.SoulMadeSaleData] {

  let salesData = SoulMadeMarketplace.getSoulMadeSales(address: address)
  return salesData
}
`;

const getWithdrawSellScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c
import SoulMadeMarketplace from 0x9a57dfe5c8ce609c
import NonFungibleToken from 0x1d7e57aa55817448

transaction(nftId: UInt64, nftType: String) {
  let soulMadeMainCollection: &SoulMadeMain.Collection
  let soulMadeComponentCollection: &SoulMadeComponent.Collection
  let marketplace: &SoulMadeMarketplace.SaleCollection

  prepare(account: AuthAccount) {
    let marketplaceCap = account.getCapability<&{SoulMadeMarketplace.SalePublic}>(SoulMadeMarketplace.CollectionPublicPath)

    // todo: check the force operator
    self.marketplace = account.borrow<&SoulMadeMarketplace.SaleCollection>(from: SoulMadeMarketplace.CollectionStoragePath)!
    self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
    self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
  }

  execute {
    if nftType == "SoulMadeMain"{
      // todo: check if this "as" is the right way to do this
      let mainNft <- self.marketplace.withdrawSoulMadeMain(tokenId: nftId) as @NonFungibleToken.NFT
      self.soulMadeMainCollection.deposit(token: <- mainNft)
    } else if nftType == "SoulMadeComponent" {
      let componentNft <- self.marketplace.withdrawSoulMadeComponent(tokenId: nftId) as @NonFungibleToken.NFT
      self.soulMadeComponentCollection.deposit(token: <- componentNft)      
    } else {
      panic("Unknown NFT Type Specified")
    }
  }
}
`;

const checkInitScript = () => `
  import SoulMadeMain from 0x9a57dfe5c8ce609c

  pub fun main(address: Address) : Bool {
      return !getAccount(address).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).check()
  }
`;

const initAccountTx = () => `
  //testnet
  import SoulMade from 0x9a57dfe5c8ce609c
  import SoulMadeMain from 0x9a57dfe5c8ce609c
  import SoulMadeComponent from 0x9a57dfe5c8ce609c
  import SoulMadePack from 0x9a57dfe5c8ce609c

  transaction {
    prepare(acct: AuthAccount) {

      // Check if a SoulMadeMain collection already exists
      if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
          // Create a new Rewards collection
          let collection <- SoulMadeMain.createEmptyCollection()
          // Put the new collection in storage
          acct.save(<-collection, to: SoulMadeMain.CollectionStoragePath)
          // Create a public Capability for the collection
          acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)

          //acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPrivate}>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
          acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
      }

      if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
          // Create a new Rewards collection
          let collection <- SoulMadeComponent.createEmptyCollection()
          // Put the new collection in storage
          acct.save(<-collection, to: SoulMadeComponent.CollectionStoragePath)
          // Create a public Capability for the collection
          acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)

          //acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPrivate}>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
          acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
      }

      if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) == nil {
          let collection <- SoulMadePack.createEmptyCollection()
          acct.save(<-collection, to: SoulMadePack.CollectionStoragePath)
          acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)

          acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionPrivatePath, target: SoulMadePack.CollectionStoragePath)
      }
    }
  }
`;

const checkFreeClaimedScript = () => `
    import SoulMadePack from 0x9a57dfe5c8ce609c
    /*
    Already Claimed -> Return true
    Have not claimed -> return false
    */
    pub fun main(address: Address) : Bool {
        return SoulMadePack.checkClaimed(address: address)
    }
`;

const freeClaimTx = () => `
    import SoulMadeMain from 0x9a57dfe5c8ce609c
    import SoulMadeComponent from 0x9a57dfe5c8ce609c
    import SoulMadeMarketplace from 0x9a57dfe5c8ce609c
    import SoulMadePack from 0x9a57dfe5c8ce609c
    import FungibleToken from 0xf233dcee88fe0abe
    import FlowToken from 0x1654653399040a61
    
transaction {

    let mainNftCollection: &{SoulMadeMain.CollectionPublic}
    let componentNftCollection: &{SoulMadeComponent.CollectionPublic}
    let CollectionForClaim: &{SoulMadePack.CollectionFreeClaim}

    prepare(acct: AuthAccount){

        if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) == nil {
            let collection <- SoulMadeMain.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeMain.CollectionStoragePath)
            acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
            // todo: double check if we need this PrivatePath at all. I remeber we actually have used it somewhere.
            acct.link<&SoulMadeMain.Collection>(SoulMadeMain.CollectionPrivatePath, target: SoulMadeMain.CollectionStoragePath)
        }

        if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) == nil {
            let collection <- SoulMadeComponent.createEmptyCollection()
            acct.save(<-collection, to: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
            acct.link<&SoulMadeComponent.Collection>(SoulMadeComponent.CollectionPrivatePath, target: SoulMadeComponent.CollectionStoragePath)
        }
        
        if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionFreeClaimStoragePath) == nil {
            let collection <- SoulMadePack.createEmptyCollection()
            acct.save(<-collection, to: SoulMadePack.CollectionFreeClaimStoragePath)
            acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionFreeClaim}>(SoulMadePack.CollectionFreeClaimPublicPath, target: SoulMadePack.CollectionFreeClaimStoragePath)
            acct.link<&SoulMadePack.Collection>(SoulMadePack.CollectionFreeClaimPrivatePath, target: SoulMadePack.CollectionFreeClaimStoragePath)
        }

        self.mainNftCollection = acct.borrow<&{SoulMadeMain.CollectionPublic}>(from: SoulMadeMain.CollectionStoragePath) ?? panic("Cannot borrow Main NFT collection receiver from account")
        self.componentNftCollection = acct.borrow<&{SoulMadeComponent.CollectionPublic}>(from: SoulMadeComponent.CollectionStoragePath) ?? panic("Cannot borrow Component NFT collection receiver from account")

        let platformAddress: Address = 0x9a57dfe5c8ce609c
        
        self.CollectionForClaim = getAccount(platformAddress).getCapability<&{SoulMadePack.CollectionFreeClaim}>(SoulMadePack.CollectionFreeClaimPublicPath).borrow() ?? panic("Cannot borrow CollectionForClaim from Platform")
    }

    execute {
        self.CollectionForClaim.freeClaim(mainNftCollectionRef: self.mainNftCollection, componentNftCollectionRef: self.componentNftCollection)
    }
}
`;

const getAssetTransferScript = () => `
import SoulMadeMain from 0x9a57dfe5c8ce609c
import SoulMadeComponent from 0x9a57dfe5c8ce609c

transaction(id: UInt64, to: Address, nftType: String) {
	let soulMadeMainCollection: &SoulMadeMain.Collection
	let mainNftCollection: &{SoulMadeMain.CollectionPublic}
  let soulMadeComponentCollection: &SoulMadeComponent.Collection
  let componentNftCollection: &{SoulMadeComponent.CollectionPublic}

	prepare(account: AuthAccount) {
		self.soulMadeMainCollection = account.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)!
		self.mainNftCollection = getAccount(to).getCapability<&{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
    self.soulMadeComponentCollection = account.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)!
		self.componentNftCollection = getAccount(to).getCapability<&{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath).borrow() ?? panic("Cannot borrow Main NFT collection receiver from account")
	}

	execute {
    if nftType == "SoulMadeMain" {
			let mainNft <- self.soulMadeMainCollection.withdraw(withdrawID: id)
			self.mainNftCollection.deposit(token : <- mainNft)
    }

    if nftType == "SoulMadeComponent" {
			let nft <- self.soulMadeComponentCollection.withdraw(withdrawID: id)
			self.componentNftCollection.deposit(token : <- nft)
	  }
  }
}

`;

export class SoulMadeService extends SingletonService {
  async login() {
    try {
      return await fcl.authenticate();
    } catch (e) {
      console.log(e);
    }
  }

  async logout() {
    try {
      fcl.unauthenticate();
    } catch (e) {
      console.log(e);
    }
  }

  async getMainAssets({ series }) {
    const res = await fcl.send([fcl.script(getMainAssetsScript()), fcl.args([fcl.arg(series, types.String)])]);
    const decodedData = await fcl.decode(res);
    return decodedData;
  }

  async getMainAssets_by_series_marketplace({ series }) {
    const res = await fcl.send([
      fcl.script(getMainAssets_By_Series_MarketplaceScript()),
      fcl.args([fcl.arg(series, types.String)]),
    ]);
    const decodedData = await fcl.decode(res);
    return decodedData;
  }

  async getMain_AllMarketPalce({ series }) {
    const res = await fcl.send([fcl.script(getAllMarketPlace_SaleMain()), fcl.args([fcl.arg(series, types.String)])]);
    const decodedData = await fcl.decode(res);
    return decodedData;
  }

  async getComponentAssets({ series }) {
    const res = await fcl.send([fcl.script(getComponentAssetsScript()), fcl.args([fcl.arg(series, types.String)])]);
    const decodedData = await fcl.decode(res);
    return decodedData;
  }

  async get_All_Components({ series }) {
    const res = await fcl.send([
      fcl.script(getAllMarketPlace_SaleComponent()),
      fcl.args([fcl.arg(series, types.String)]),
    ]);
    const decodedData = await fcl.decode(res);
    return decodedData;
  }

  async getGroupAssets({ series, nftType, category, nftName }) {
    const res = await fcl.send([
      fcl.script(getGroupAssetsScript()),
      fcl.args([
        fcl.arg(series, types.String),
        fcl.arg(nftType, types.String),
        fcl.arg(category, types.String),
        fcl.arg(nftName, types.String),
      ]),
    ]);
    const decodedData = await fcl.decode(res);
    return decodedData;
  }

  async getAssetByIdAndType({ id, nftType }) {
    const res = await fcl.send([
      fcl.script(getAssetByIdAndTypeScript()),
      fcl.args([fcl.arg(id, types.UInt64), fcl.arg(nftType, types.String)]),
    ]);
    const decodedData = await fcl.decode(res);
    return decodedData;
  }

  async buyAsset(tokenId, nftType, sellerAddress) {
    return fcl
      .send([
        fcl.transaction(getBuyScript()),
        fcl.args([
          fcl.arg(tokenId, types.UInt64),
          fcl.arg(nftType, types.String),
          fcl.arg(sellerAddress, types.Address),
        ]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.authorizations([fcl.currentUser().authorization]),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async buySeries(series) {
    return fcl.send([]).then(res => fcl.tx(res.transactionId));
  }

  async getUserAssets(address) {
    const res = await fcl.send([fcl.script(getUserAssetsScript()), fcl.args([fcl.arg(address, types.Address)])]);
    const decodedRes = await fcl.decode(res);
    return decodedRes;
  }

  async getRandomPackListingIdOfSeries(series) {
    const res = await fcl.send([
      fcl.script(getRandomPackListingIdOfSeriesScript()),
      fcl.args([fcl.arg(series, types.String)]),
    ]);
    const decodedRes = await fcl.decode(res);
    return decodedRes;
  }

  async getCapability(address) {
    const res = await fcl.send([fcl.script(getCapabilityScript()), fcl.args([fcl.arg(address, types.Address)])]);
    return await fcl.decode(res);
  }

  async getFreeBody() {
    return fcl
      .send([
        fcl.transaction(getFreeBodyScript()),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async updateInfo(mainNftId, name, description) {
    return fcl
      .send([
        fcl.transaction(updateInfoScript()),
        fcl.args([fcl.arg(mainNftId, types.UInt64), fcl.arg(name, types.String), fcl.arg(description, types.String)]),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async initAccount() {
    return fcl
      .send([
        fcl.transaction(initAccountTx()),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async freeClaim() {
    return fcl
      .send([
        fcl.transaction(freeClaimTx()),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async updateComponents(updatedComponents, mainNftId, ipfsHash) {
    return fcl
      .send([
        fcl.transaction(getUpdateComponentsScript()),
        fcl.args([
          //fcl.arg(Number(mainNftId), types.UInt64),
          fcl.arg(mainNftId, types.UInt64),
          fcl.arg(
            updatedComponents,
            types.Array(types.Dictionary({ key: types.String, value: types.Optional(types.UInt64) })),
          ),
          fcl.arg(ipfsHash, types.String),
        ]),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async buyPack(series) {
    const res = await fcl.send([
      fcl.script(getRandomPackListingIdOfSeriesScript()),
      fcl.args([fcl.arg(series, types.String)]),
    ]);
    const listingId = await fcl.decode(res);

    return fcl
      .send([
        fcl.transaction(getBuyPackScript()),
        //fcl.args([fcl.arg(series, types.String)]),
        fcl.args([fcl.arg(listingId, types.UInt64)]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.authorizations([fcl.currentUser().authorization]),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async getMainDetail(address, id) {
    const res = await fcl.send([
      fcl.script(getSpecialMainDetail()),
      fcl.args([fcl.arg(address, types.Address), fcl.arg(id, types.UInt64)]),
    ]);
    const decodeData = await fcl.decode(res);
    return decodeData;
  }

  async getUserBalance(address) {
    const res = await fcl.send([fcl.script(getUserBalanceScript()), fcl.args([fcl.arg(address, types.Address)])]);
    const decodeData = await fcl.decode(res);
    return decodeData;
  }

  async sellAsset(assetId, price, nftType) {
    return fcl
      .send([
        fcl.transaction(getSellAssetScript()),
        fcl.args([fcl.arg(assetId, types.UInt64), fcl.arg(price, types.UFix64), fcl.arg(nftType, types.String)]),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async getUserSales(address) {
    const res = await fcl.send([fcl.script(getUserSalesScript()), fcl.args([fcl.arg(address, types.Address)])]);
    const decodeData = await fcl.decode(res);
    return decodeData;
  }

  async withdrawSell(assetId, nftType) {
    return fcl
      .send([
        fcl.transaction(getWithdrawSellScript()),
        fcl.args([fcl.arg(assetId, types.UInt64), fcl.arg(nftType, types.String)]),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }

  async getInitStatus(addr) {
    const res = await fcl.send([fcl.script(checkInitScript()), fcl.args([fcl.arg(addr, types.Address)])]);
    const decodeData = await fcl.decode(res);
    return decodeData;
  }

  async checkFreeClaimed(addr) {
    const res = await fcl.send([fcl.script(checkFreeClaimedScript()), fcl.args([fcl.arg(addr, types.Address)])]);
    const decodeData = await fcl.decode(res);
    return decodeData;
  }

  async assetTransfer(assetId, address, nftType) {
    return fcl
      .send([
        fcl.transaction(getAssetTransferScript()),
        fcl.args([fcl.arg(assetId, types.UInt64), fcl.arg(address, types.Address), fcl.arg(nftType, types.String)]),
        fcl.authorizations([fcl.authz]),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.payer(fcl.currentUser().authorization),
        fcl.limit(9999),
      ])
      .then(res => fcl.tx(res.transactionId));
  }
}
