import NonFungibleToken from "../../contracts/NonFungibleToken.cdc"
import MetadataViews from "../../contracts/MetadataViews.cdc"
import NFTStorefront from "../../contracts/NFTStorefront.cdc"
import OnlyBadges from "../../contracts/OnlyBadges.cdc"

pub struct ListingItem {
    pub let creator: Address
    pub let name: String
    pub let badge_image: String
    pub let thumbnail: String
    pub let description: String
    pub let id: UInt64
    pub let resourceID: UInt64
    pub let owner: Address
    pub let price: UFix64

    init(
        creator: Address,
        name: String,
        badge_image: String,
        thumbnail: String,
        description: String,
        id: UInt64,
        resourceID: UInt64,
        owner: Address,
        price: UFix64
    ) {
        self.creator = creator
        self.name = name
        self.badge_image = badge_image
        self.thumbnail = thumbnail
        self.description = description

        self.id = id
        self.resourceID = resourceID
        self.owner = owner
        self.price = price
    }
}

pub fun dwebURL(_ file: MetadataViews.IPFSFile): String {
    var url = "https://"
        .concat(file.cid)
        .concat(".ipfs.dweb.link/")

    if let path = file.path {
        return url.concat(path)
    }

    return url
}

pub fun main(address: Address, listingResourceID: UInt64): ListingItem? {
    let account = getAccount(address)

    if let storefrontRef = account.getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath).borrow() {

        if let listing = storefrontRef.borrowListing(listingResourceID: listingResourceID) {

            let details = listing.getDetails()

            let id = details.nftID
            let itemPrice = details.salePrice

            if let collection = getAccount(address).getCapability<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath).borrow() {

                if let item = collection.borrowOnlyBadges(id: id) {

                    let creator: Address = item.creator
                    
                    if let view = item.resolveView(Type<MetadataViews.Display>()) {

                        let display = view as! MetadataViews.Display

                        let owner: Address = item.owner!.address!

                        let ipfsThumbnail = display.thumbnail as! MetadataViews.IPFSFile

                        return ListingItem(
                            creator: creator,
                            name: display.name,
                            badge_image: item.badge_image.cid,
                            thumbnail: dwebURL(ipfsThumbnail),
                            description: display.description,
                            id: id,
                            resourceID: item.uuid,
                            owner: address,
                            price: itemPrice
                        )
                    }
                }
            }
        }
    }

    return nil
}
