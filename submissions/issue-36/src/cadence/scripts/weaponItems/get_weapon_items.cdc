import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import MetadataViews from "../../contracts/MetadataViews.cdc"
import WeaponItems1 from "../../contracts/WeaponItems1.cdc"

pub struct WeaponItem {
    pub let name: String
    pub let description: String
    pub let thumbnail: String

    pub let itemID: UInt64
    pub let resourceID: UInt64
    pub let owner: Address
    pub let externalUrl: String

    init(
        name: String,
        description: String,
        thumbnail: String,
        itemID: UInt64,
        resourceID: UInt64,
        owner: Address,
        externalURL: String
    ) {
        self.name = name
        self.description = description
        self.thumbnail = thumbnail

        self.itemID = itemID
        self.resourceID = resourceID
        self.owner = owner
        self.externalUrl = externalURL
    }
}

pub fun main(address: Address): [WeaponItem?] {
  let items: [WeaponItem?] = []
  if let collection = getAccount(address).getCapability<&WeaponItems1.Collection{NonFungibleToken.CollectionPublic, WeaponItems1.WeaponItemsCollectionPublic}>(WeaponItems1.CollectionPublicPath).borrow() {
    let ids = collection.getIDs()
    for itemID in ids {
      if let item = collection.borrowWeaponItem(id: itemID) {
            if let view = item.resolveView(Type<MetadataViews.Display>()) {
                let display = view as! MetadataViews.Display
                let owner: Address = item.owner!.address!
                let httpThumbnail = display.thumbnail as! MetadataViews.HTTPFile  
                let externalURL = item.url
                let item = WeaponItem(
                    name: display.name,
                    description: display.description,
                    thumbnail: httpThumbnail.url,
                    itemID: itemID,
                    resourceID: item.uuid,
                    owner: address,
                    externalURL: externalURL
                )
                items.append(item)
            }
        }
    }
  }
  return items
}