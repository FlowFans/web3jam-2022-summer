import NonFungibleToken from 0xNonFungibleToken
import MetadataViews from 0xMetadataViews
import OnlyBadges from 0xOnlyBadges

pub struct KittyItem {
  pub let name: String
  pub let creator: Address
  pub let description: String
  pub let badge_image: String

  pub let id: UInt64
  pub let resourceID: UInt64
  pub let owner: Address
  pub let number: UInt64

  pub let max: UInt64?

  //Royalty View
  pub let royalty_cut: UFix64? //0.0 -> 1.0

  pub let royalty_description: String?

  pub let royalty_receiver: Address?

  //NFTCollectionDisplay View
  pub let externalURL: String?

  init(
    name: String,
    creator: Address,
    description: String,
    badge_image: String,
    id: UInt64,
    resourceID: UInt64,
    owner: Address,
    number: UInt64,
    max: UInt64?,
    royalty_cut: UFix64?,
    royalty_description: String?,
    royalty_receiver: Address?,
    externalURL: String?
  ) {
    self.name = name
    self.creator = creator
    self.description = description
    self.badge_image = badge_image

    self.id = id
    self.resourceID = resourceID
    self.owner = owner

    self.number = number
    self.max = max

    self.royalty_cut = royalty_cut
    self.royalty_description = royalty_description
    self.royalty_receiver = royalty_receiver

    self.externalURL = externalURL
  }
}

pub fun fetch(address: Address, id: UInt64): KittyItem? {
  if let collection = getAccount(address).getCapability<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath).borrow() {

    if let item = collection.borrowOnlyBadges(id: id) {

      if let view = item.resolveView(Type<MetadataViews.Display>()) {

        let display = view as! MetadataViews.Display

        let owner: Address = item.owner!.address!

        let ipfsThumbnail = display.thumbnail as! MetadataViews.IPFSFile

        var editionName: String? = nil
        var editionNumber: UInt64 = 0
        var editionMax: UInt64? = nil

        var externalURL: String? = nil

        var royalty_cut: UFix64?  = nil//0.0 -> 1.0
        var royalty_description: String? = nil
        var royalty_receiver: Address? = nil


        if let view = item.resolveView(Type<MetadataViews.Edition>()) {
          let editionView = view as! MetadataViews.Edition
          editionName = editionView.name
          editionNumber = editionView.number
          editionMax = editionView.max
        }

        if let view = item.resolveView(Type<MetadataViews.ExternalURL>()) {
          let externalURLView = view as! MetadataViews.ExternalURL
          externalURL = externalURLView.url
        }

        if let view = item.resolveView(Type<MetadataViews.Royalty>()) {
          let royaltyView = view as! MetadataViews.Royalty
          royalty_cut = royaltyView.cut
          royalty_description = royaltyView.description
          royalty_receiver = royaltyView.receiver.address
        }

        return KittyItem(
          name: display.name,
          creator: item.creator,
          description: display.description,
          badge_image: item.badge_image.cid,
          id: id,
          resourceID: item.uuid,
          owner: address,
          number: editionNumber,
          max: editionMax,
          royalty_cut: royalty_cut,
          royalty_description: royalty_description,
          royalty_receiver: royalty_receiver,
          externalURL: externalURL,
        )
      }
      
    }
  }

  return nil
}

pub fun main(keys: [String], addresses: [Address], ids: [UInt64]): {String: KittyItem?} {
  let r: {String: KittyItem?} = {}
  var i = 0
  while i < keys.length {
    let key = keys[i]
    let address = addresses[i]
    let id = ids[i]
    r[key] = fetch(address: address, id: id)
    i = i + 1
  }
  return r
}