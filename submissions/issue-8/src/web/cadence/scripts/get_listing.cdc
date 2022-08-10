import NonFungibleToken from 0xNonFungibleToken
import MetadataViews from 0xMetadataViews
import NFTStorefront from 0xNFTStorefront
import OnlyBadges from 0xOnlyBadges

pub struct ListingItem {
    pub let name: String
    pub let description: String
    pub let badge_image: String

    pub let id: UInt64
    pub let resourceID: UInt64
    pub let owner: Address
    pub let price: UFix64

    init(
        name: String,
        description: String,
        badge_image: String,
        id: UInt64,
        resourceID: UInt64,
        owner: Address,
        price: UFix64
    ) {
        self.name = name
        self.description = description
        self.badge_image = badge_image

        self.id = id
        self.resourceID = resourceID
        self.owner = owner
        self.price = price
    }
}

pub fun main(address: Address, listingResourceID: UInt64): ListingItem? {
    if let storefrontRef = getAccount(address).getCapability<&NFTStorefront.Storefront{NFTStorefront.StorefrontPublic}>(NFTStorefront.StorefrontPublicPath).borrow() {

        if let listing = storefrontRef.borrowListing(listingResourceID: listingResourceID) {

            let details = listing.getDetails()

            let id = details.nftID
            let itemPrice = details.salePrice

            if let collection = getAccount(address).getCapability<&OnlyBadges.Collection{NonFungibleToken.CollectionPublic, OnlyBadges.OnlyBadgesCollectionPublic}>(OnlyBadges.CollectionPublicPath).borrow() {

                if let item = collection.borrowOnlyBadges(id: id) {

                    if let view = item.resolveView(Type<MetadataViews.Display>()) {

                        let display = view as! MetadataViews.Display

                        let owner: Address = item.owner!.address!

                        let ipfsThumbnail = display.thumbnail as! MetadataViews.IPFSFile

                        return ListingItem(
                            name: display.name,
                            description: display.description,
                            badge_image: item.badge_image.cid,
                            id: item.id,
                            resourceID: item.uuid,
                            // kind: item.kind,
                            // rarity: item.rarity,
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