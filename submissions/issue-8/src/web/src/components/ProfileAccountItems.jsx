import PropTypes from "prop-types"
import ListItems from "src/components/ListItems"
import useAccountItems from "src/hooks/useAccountItems"
import useApiListBadges from "src/hooks/useApiListBadges"

export default function ProfileAccountItems({address}) {
  // The Storefront only returns listing IDs, so we need to fetch
  // the listing objects from the API.
  const {badges, isLoading: isBadgesLoading} = useApiListBadges({
    owner: address,
  })
  const {data: itemIds, isAccountItemsLoading} = useAccountItems(address)
  const isLoading =
    isBadgesLoading || isAccountItemsLoading || !badges || !itemIds
  if (isLoading) return null
  const listingItemIds = badges.map(listing => listing.id)
  const itemIdsNotForSale = itemIds?.filter(id => !listingItemIds.includes(id))

  console.log("listingItemIds:" + JSON.stringify(listingItemIds))
  console.log("itemIds:" + JSON.stringify(itemIds))
  console.log("itemIdsNotForSale:" + JSON.stringify(itemIdsNotForSale))
  return (
    <div>
      <ListItems
        accountItemIds={itemIdsNotForSale.map(id => ({
          id: id,
          owner: address,
        }))}
      />
    </div>
  )
}

ProfileAccountItems.propTypes = {
  address: PropTypes.string,
}
