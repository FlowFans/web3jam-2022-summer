export function normalizeApiListing(item) {
  console.log("normalizeApiListing item:" + JSON.stringify(item))
  return {
    id: item.id,
    creator: item.creator,
    owner: item.owner,
    name: item.name,
    badge_image: item.badge_image,
    listingResourceID: item.listing_resource_id,
    price: item.price.toString(),
    txID: item.transaction_id,
  }
}

export function normalizeListing(listing) {
  console.log("normalizeListing item:" + JSON.stringify(listing))
  return {
    id: listing.id,
    owner: listing.owner,
    name: listing.name,
    badge_image: listing.badge_image,
    listingResourceID: listing.listingResourceID,
    price: listing.price,
    txID: "",
  }
}

export function normalizeMerchants(item) {
  console.log("normalizeMerchants item:" + JSON.stringify(item))
  return {
    name: item.name,
    image: item.image_path,
    address: item.address,
    txID: item.transaction_id,
  }
}

export function normalizeBadges(item) {
  console.log("normalizeBadges item:" + JSON.stringify(item))
  return {
    owner: item.owner,
    id: item.id,
    name: item.name,
    creator: item.creator,
    badge_image: item.badge_image,
    description: item.description,
    number: item.number,
    max: item.max,
    royalty_cut: item.royalty_cut,
    royalty_description: item.royalty_description,
    royalty_receiver: item.royalty_receiver,
    externalURL: item.externalURL,
    txID: item.transaction_id,
  }
}

export function normalizeItem(accountItem, apiListing) {
  return {
    itemID: accountItem.itemID,
    kind: Number(accountItem.kind.rawValue),
    rarity: Number(accountItem.rarity.rawValue),
    owner: accountItem.owner,
    name: accountItem.name,
    image: accountItem.image,
    owner: accountItem.owner,
    listingResourceID: apiListing?.listingResourceID,
    price: apiListing?.price,
    txID: apiListing?.txID,
  }
}
